<?php

namespace App\Http\Controllers;

use App\Models\Kutuphane;
use App\Models\Uye;
use App\Models\ZiyaretKaydi;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ZiyaretController extends Controller
{
    private function canView(): bool
    {
        $u = auth()->user();

        return $u && ($u->hasYetki(27) || $u->hasYetki(31));
    }

    private function canManage(): bool
    {
        $u = auth()->user();

        return $u && ($u->hasYetki(28) || $u->hasYetki(29) || $u->hasYetki(32) || $u->hasYetki(33));
    }

    private function canViewAllLibraries(): bool
    {
        $u = auth()->user();

        return $u && ($u->hasYetki(31));
    }

    private function canCreateAllLibraries(): bool
    {
        $u = auth()->user();

        return $u && ($u->hasYetki(32));
    }

    private function baseQuery()
    {
        $q = ZiyaretKaydi::query()->with(['uye', 'kutuphane', 'kaydeden']);

        if (!$this->canViewAllLibraries()) {
            $ids = auth()->user()->yetkiliKutuphaneIds();
            $q->whereIn('kutuphane_id', $ids ?: [-1]);
        }

        return $q;
    }

    private function stats(): array
    {
        $today = now()->toDateString();
        $base = $this->baseQuery();

        return [
            'bugun'    => (clone $base)->whereDate('giris_saati', $today)->count(),
            'icinde'   => (clone $base)->whereNull('cikis_saati')->count(),
            'cikisli'  => (clone $base)->whereNotNull('cikis_saati')->count(),
            'toplam'   => (clone $base)->count(),
        ];
    }

    private function kutuphanelerForUser()
    {
        $query = Kutuphane::query()
            ->whereNull('deleted_at')
            ->where('statu', 'aktif')
            ->orderBy('title');

        if (!$this->canViewAllLibraries()) {
            $ids = auth()->user()->yetkiliKutuphaneIds();
            $query->whereIn('id', $ids ?: [-1]);
        }

        return $query->get(['id', 'title']);
    }

    private function assertKutuphaneYetkisi(int $kutuphaneId): void
    {
        if ($this->canViewAllLibraries()) {
            return;
        }

        $ids = auth()->user()->yetkiliKutuphaneIds() ?: [];
        abort_unless(in_array($kutuphaneId, $ids, true), 403);
    }

    private function canCreateLibrary(int $kutuphaneId): bool
    {
        return $this->canCreateAllLibraries() || in_array($kutuphaneId, auth()->user()->yetkiliKutuphaneIds() ?: [], true);
    }

    private function parseDateTimeInput(string $value): Carbon
    {
        $value = trim($value);
        if (preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/', $value) === 1) {
            return Carbon::createFromFormat('Y-m-d\TH:i', $value);
        }

        return Carbon::parse($value);
    }

    private function filteredQuery(Request $request)
    {
        $filtre      = $request->input('filtre', 'hepsi');
        $search      = trim($request->input('search', ''));
        $kutuphaneId = (int) $request->input('kutuphaneId', 0);

        $query = $this->baseQuery();

        if ($filtre === 'icinde') {
            $query->whereNull('cikis_saati');
        } elseif ($filtre === 'bugun') {
            $query->whereDate('giris_saati', now()->toDateString());
        } elseif ($filtre === 'cikisli') {
            $query->whereNotNull('cikis_saati');
        }

        if ($kutuphaneId > 0) {
            $query->where('kutuphane_id', $kutuphaneId);
        }

        if ($search !== '') {
            $query->whereHas('uye', function ($q) use ($search) {
                $normalized = preg_replace('/\s+/', ' ', $search);
                $q->where('ad', 'LIKE', "%{$search}%")
                    ->orWhere('soyad', 'LIKE', "%{$search}%")
                    ->orWhereRaw("CONCAT(ad, ' ', soyad) LIKE ?", ["%{$normalized}%"])
                    ->orWhere('tc_kimlik', 'LIKE', "%{$search}%");
            });
        }

        return $query->orderByDesc('giris_saati');
    }

    public function index(Request $request)
    {
        abort_unless($this->canView(), 403);

        return view('ziyaret.list', [
            'stats'        => $this->stats(),
            'filtre'       => $request->input('filtre', 'hepsi'),
            'canManage'    => $this->canManage(),
            'kutuphaneler' => $this->kutuphanelerForUser(),
        ]);
    }

    public function tableData(Request $request)
    {
        abort_unless($this->canView(), 403);

        $perPage = in_array((int) $request->input('per_page'), [10, 20, 50, 100], true)
            ? (int) $request->input('per_page')
            : 20;

        $rows = $this->filteredQuery($request)->paginate($perPage);

        $canManage = $this->canManage();
        $items = collect($rows->items())->map(function (ZiyaretKaydi $z) use ($canManage) {
            $uye = $z->uye;

            return [
                'id'            => $z->id,
                'uye_id'        => $z->uye_id,
                'uye_ad'        => $uye ? trim($uye->ad . ' ' . $uye->soyad) : '—',
                'uye_initials'  => $uye
                    ? mb_strtoupper(mb_substr($uye->ad, 0, 1) . mb_substr($uye->soyad, 0, 1), 'UTF-8')
                    : '?',
                'uye_tc'        => $uye?->tc_kimlik ?? '—',
                'profile_url'   => $uye ? route('uyeler.show', $uye->id) : null,
                'kutuphane'     => $z->kutuphane?->title ?? '—',
                'giris_saati'   => $z->giris_saati?->format('d.m.Y H:i') ?? '—',
                'cikis_saati'   => $z->cikis_saati?->format('d.m.Y H:i'),
                'icinde_mi'     => $z->icinde_mi,
                'sure_label'    => $z->sure_label,
                'notlar'        => $z->notlar,
                'kaydeden'      => $z->kaydeden?->name,
                'cikis_kaydedilebilir' => $canManage && $z->icinde_mi,
            ];
        })->values()->all();

        return response()->json([
            'success' => true,
            'data'    => $items,
            'meta'    => [
                'current_page' => $rows->currentPage(),
                'last_page'    => $rows->lastPage(),
                'per_page'     => $rows->perPage(),
                'total'        => $rows->total(),
                'from'         => $rows->firstItem() ?? 0,
                'to'           => $rows->lastItem() ?? 0,
            ],
        ]);
    }

    public function export(Request $request)
    {
        abort_unless($this->canView(), 403);

        $kayitlar = $this->filteredQuery($request)->get();

        $filename = 'ziyaretci_' . now()->format('Ymd_His') . '.csv';
        $headers  = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use ($kayitlar) {
            $out = fopen('php://output', 'w');
            fputs($out, "\xEF\xBB\xBF");
            fputcsv($out, [
                '#', 'Üye Adı Soyadı', 'TC Kimlik No',
                'Kütüphane', 'Giriş Saati', 'Çıkış Saati',
                'Süre', 'Durum', 'Not', 'Kaydeden',
            ], ';');

            foreach ($kayitlar as $z) {
                $uye = $z->uye;
                fputcsv($out, [
                    $z->id,
                    $uye ? trim($uye->ad . ' ' . $uye->soyad) : '—',
                    $uye?->tc_kimlik ?? '—',
                    $z->kutuphane?->title ?? '—',
                    $z->giris_saati?->format('d.m.Y H:i') ?? '—',
                    $z->cikis_saati?->format('d.m.Y H:i') ?? '—',
                    $z->sure_label,
                    $z->icinde_mi ? 'İçeride' : 'Çıktı',
                    $z->notlar ?? '—',
                    $z->kaydeden?->name ?? '—',
                ], ';');
            }
            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function store(Request $request)
    {
        abort_unless($this->canManage(), 403);

        $validated = $request->validate([
            'uye_id'       => ['required', 'integer', 'exists:uyeler,id'],
            'kutuphane_id' => ['required', 'integer', 'exists:kutuphane,id'],
            'giris_saati'  => ['required', 'string'],
            'cikis_saati'  => ['nullable', 'string'],
            'notlar'       => ['nullable', 'string', 'max:2000'],
        ], [
            'uye_id.required'       => 'Üye seçimi zorunludur.',
            'kutuphane_id.required' => 'Kütüphane seçimi zorunludur.',
            'giris_saati.required'  => 'Giriş saati zorunludur.',
        ]);

        $kutuphaneId = (int) $validated['kutuphane_id'];

        if (!$this->canCreateAllLibraries()) {
            abort_unless($this->canCreateLibrary($kutuphaneId), 403, 'Bu kütüphaneye ziyaret kaydı oluşturma yetkiniz yok. Sistem yöneticinize başvurunuz.');
        }

        $giris = $this->parseDateTimeInput($validated['giris_saati']);
        $cikis = null;
        if (!empty($validated['cikis_saati'])) {
            $cikis = $this->parseDateTimeInput($validated['cikis_saati']);
            if ($cikis->lt($giris)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Çıkış saati giriş saatinden önce olamaz.',
                ], 422);
            }
        }

        $kayit = ZiyaretKaydi::create([
            'uye_id'       => (int) $validated['uye_id'],
            'kutuphane_id' => $kutuphaneId,
            'giris_saati'  => $giris,
            'cikis_saati'  => $cikis,
            'notlar'       => $validated['notlar'] ?: null,
            'created_user' => auth()->id(),
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Ziyaretçi girişi kaydedildi.',
                'id'      => $kayit->id,
            ]);
        }

        return redirect()->route('ziyaret.index')->with('success', 'Ziyaretçi girişi kaydedildi.');
    }

    public function update(Request $request, ZiyaretKaydi $ziyaretKaydi)
    {
        abort_unless($this->canManage(), 403);
        $this->assertKutuphaneYetkisi((int) $ziyaretKaydi->kutuphane_id);

        $validated = $request->validate([
            'cikis_saati' => ['nullable', 'string'],
            'notlar'      => ['nullable', 'string', 'max:2000'],
        ]);

        $data = ['updated_user' => auth()->id()];

        if (array_key_exists('notlar', $validated)) {
            $data['notlar'] = $validated['notlar'] ?: null;
        }

        if (!empty($validated['cikis_saati'])) {
            $cikis = $this->parseDateTimeInput($validated['cikis_saati']);
            if ($cikis->lt($ziyaretKaydi->giris_saati)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Çıkış saati giriş saatinden önce olamaz.',
                ], 422);
            }
            $data['cikis_saati'] = $cikis;
        }

        $ziyaretKaydi->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Ziyaret kaydı güncellendi.',
        ]);
    }
}
