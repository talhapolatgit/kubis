<?php

namespace App\Http\Controllers;

use App\Models\Katalog;
use App\Models\Uye;
use App\Models\UyeBekleme;
use App\Models\UyeRezerve;
use App\Services\BeyogluWebhookService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RezerveController extends Controller
{
    public function __construct(
        private readonly BeyogluWebhookService $webhookService
    ) {}

    private function canView(): bool
    {
        $u = auth()->user();

        return $u && ($u->hasYetki(7) || $u->hasYetki(8) || $u->hasYetki(9) || $u->hasYetki(10));
    }

    private function canManage(): bool
    {
        $u = auth()->user();

        return $u && ($u->hasYetki(8) || $u->hasYetki(10));
    }

    private function canViewAllLibraries(): bool
    {
        $u = auth()->user();

        return $u && ($u->hasYetki(9) || $u->hasYetki(10));
    }

    private function rezerveBaseQuery()
    {
        $q = UyeRezerve::query()->with(['uye', 'katalog.kutuphane', 'kutuphane']);

        if (! $this->canViewAllLibraries()) {
            $ids = auth()->user()->yetkiliKutuphaneIds();
            $q->whereHas('katalog', function ($k) use ($ids) {
                $k->whereIn('kutuphaneId', $ids ?: [-1]);
            });
        }

        return $q;
    }

    private function stats(): array
    {
        $aktif = (clone $this->rezerveBaseQuery())
            ->where('iptalMi', 'false')
            ->where('oduncAldiMi', 'false')
            ->where('rezerve_bitis', '>', now())
            ->count();

        $tamamlanan = (clone $this->rezerveBaseQuery())
            ->where('oduncAldiMi', 'true')
            ->count();

        $suresiDoldu = (clone $this->rezerveBaseQuery())
            ->where('iptalMi', 'false')
            ->where('oduncAldiMi', 'false')
            ->where('rezerve_bitis', '<=', now())
            ->count();

        $toplam = $this->rezerveBaseQuery()->count();

        return [
            'aktif'         => $aktif,
            'tamamlanan'    => $tamamlanan,
            'suresi_doldu'  => $suresiDoldu,
            'toplam'        => $toplam,
        ];
    }

    public function index(Request $request)
    {
        abort_unless($this->canView(), 403);

        return view('rezerve.list', [
            'stats'     => $this->stats(),
            'filtre'    => $request->input('filtre', 'aktif'),
            'canManage' => $this->canManage(),
        ]);
    }

    /**
     * GET /rezerve/tablo — AJAX liste
     */
    public function tableData(Request $request)
    {
        abort_unless($this->canView(), 403);

        $perPage = in_array((int) $request->input('per_page'), [10, 20, 50, 100, 500], true)
            ? (int) $request->input('per_page')
            : 20;

        $filtre = $request->input('filtre', 'aktif');
        $search = trim($request->input('search', ''));

        $query = $this->rezerveBaseQuery();

        if ($filtre === 'aktif') {
            $query->where('iptalMi', 'false')
                ->where('oduncAldiMi', 'false')
                ->where('rezerve_bitis', '>', now());
        } elseif ($filtre === 'tamamlanan') {
            $query->where('oduncAldiMi', 'true');
        } elseif ($filtre === 'iptal') {
            $query->where('iptalMi', 'true');
        } elseif ($filtre === 'suresi_doldu') {
            $query->where('iptalMi', 'false')
                ->where('oduncAldiMi', 'false')
                ->where('rezerve_bitis', '<=', now());
        }
        // hepsi → filtre yok

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->whereHas('uye', function ($u) use ($search) {
                    $u->where('ad', 'LIKE', "%{$search}%")
                        ->orWhere('soyad', 'LIKE', "%{$search}%")
                        ->orWhere('tc_kimlik', 'LIKE', "%{$search}%");
                })->orWhereHas('katalog', function ($k) use ($search) {
                    $k->where('kunyeEserAdi', 'LIKE', "%{$search}%")
                        ->orWhere('kunyeISBNISSN', 'LIKE', "%{$search}%")
                        ->orWhere('kunyeDemirbasKN', 'LIKE', "%{$search}%");
                });
            });
        }

        $rows = $query->orderByDesc('id')->paginate($perPage);

        $items = collect($rows->items())->map(function (UyeRezerve $r) {
            $uye = $r->uye;
            $kat = $r->katalog;

            $simdiGecerli = $r->iptalMi === 'false'
                && $r->oduncAldiMi === 'false'
                && $r->rezerve_bitis
                && $r->rezerve_bitis->isFuture();

            $oduncYapilabilir = $simdiGecerli && $this->canManage();

            return [
                'id'                  => $r->id,
                'uye_id'              => $r->uye_id,
                'katalog_id'          => $r->katalog_id,
                'uye_ad'              => $uye ? trim($uye->ad . ' ' . $uye->soyad) : '—',
                'uye_initials'        => $uye
                    ? mb_strtoupper(
                        mb_substr($uye->ad, 0, 1, 'UTF-8') . mb_substr($uye->soyad, 0, 1, 'UTF-8'),
                        'UTF-8'
                    )
                    : '?',
                'uye_tc'              => $uye?->tc_kimlik ?? '—',
                'kitap'               => $kat?->kunyeEserAdi ?? '—',
                'kitap_isbn'          => $kat?->kunyeISBNISSN,
                'kitap_demir'         => $kat?->kunyeDemirbasKN,
                'kitap_kapak'         => $kat?->kapak_resim_path,
                'rezerve_baslangic'   => $r->rezerve_baslangic?->format('d.m.Y H:i') ?? '—',
                'rezerve_bitis'       => $r->rezerve_bitis?->format('d.m.Y H:i') ?? '—',
                'iptalMi'             => $r->iptalMi,
                'oduncAldiMi'         => $r->oduncAldiMi,
                'kunyeDurum'          => $kat?->kunyeDurum,
                'kutuphane'           => $r->kutuphane?->title
                    ?? $kat?->kutuphane?->title
                    ?? '—',
                'durum_etiket'        => $this->durumEtiket($r),
                'odunc_yapilabilir'   => $oduncYapilabilir,
                'odunc_new_url'       => $oduncYapilabilir
                    ? route('odunc.new', ['katalog_id' => $r->katalog_id, 'uye_id' => $r->uye_id])
                    : null,
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

    private function durumEtiket(UyeRezerve $r): string
    {
        if ($r->iptalMi === 'true') {
            return 'İptal';
        }
        if ($r->oduncAldiMi === 'true') {
            return 'Ödünç verildi';
        }
        if ($r->rezerve_bitis && $r->rezerve_bitis->isPast()) {
            return 'Süresi doldu';
        }

        return 'Aktif';
    }

    /**
     * POST — yeni rezervasyon (personel)
     */
    public function store(Request $request)
    {
        abort_unless($this->canManage(), 403);

        $request->validate([
            'uye_id'     => ['required', 'exists:uyeler,id'],
            'katalog_id' => ['required', 'exists:katalog,id'],
        ], [
            'uye_id.required'     => 'Üye seçimi zorunludur.',
            'katalog_id.required' => 'Kitap seçimi zorunludur.',
        ]);

        $uyeId     = (int) $request->input('uye_id');
        $katalogId = (int) $request->input('katalog_id');

        if (! $this->canViewAllLibraries()) {
            $ids = auth()->user()->yetkiliKutuphaneIds();
            $k   = Katalog::find($katalogId);
            if (! $k || ($k->kutuphaneId && ! in_array((int) $k->kutuphaneId, $ids ?: [-1], true))) {
                return response()->json(['success' => false, 'message' => 'Bu kütüphaneye ait kitap için rezervasyon yetkiniz yok.'], 403);
            }
        }

        $mevcutUyeRez = UyeRezerve::query()
            ->where('katalog_id', $katalogId)
            ->where('uye_id', $uyeId)
            ->where('iptalMi', 'false')
            ->where('oduncAldiMi', 'false')
            ->where('suresiDolduMu', 'false')
            ->where('deleted_at', null)
            ->where('rezerve_bitis', '>', now())
            ->first();
        if ($mevcutUyeRez) {
            return response()->json(['success' => false, 'message' => 'Bu üyenin bu kitap için zaten aktif bir rezervasyonu var.'], 422);
        }

        $baskaAktif = UyeRezerve::query()
            ->where('katalog_id', $katalogId)
            ->where('uye_id', '!=', $uyeId)
            ->where('iptalMi', 'false')
            ->where('rezerve_bitis', '>', now())
            ->where('oduncAldiMi', 'false')
            ->where('suresiDolduMu', 'false')
            ->where('deleted_at', null)
            ->exists();
        if ($baskaAktif) {
            return response()->json(['success' => false, 'message' => 'Bu kitap başka bir üye tarafından rezerve edilmiş.'], 422);
        }

        $katalog = Katalog::query()
            ->where('id', $katalogId)
            ->whereNull('deleted_at')
            ->first();

        if (! $katalog) {
            return response()->json(['success' => false, 'message' => 'Kitap bulunamadı.'], 422);
        }

        if ($katalog->kunyeDurum === 'Ödünç') {
            return response()->json(['success' => false, 'message' => 'Bu kitap şu an ödünçte; rezervasyon oluşturulamaz.'], 422);
        }

        if ($katalog->kunyeDurum === 'Rezerve') {
            return response()->json(['success' => false, 'message' => 'Bu kitap zaten rezerve durumda.'], 422);
        }

        if ($katalog->kunyeDurum !== 'Rafta') {
            return response()->json(['success' => false, 'message' => 'Yalnızca raftaki kitaplar rezerve edilebilir.'], 422);
        }

        if ($katalog->oduncVerilemez === 'true') {
            return response()->json(['success' => false, 'message' => 'Bu kitap ödünç verilemez olarak işaretli; rezervasyon oluşturulamaz.'], 422);
        }

        $rezerve = UyeRezerve::query()->create([
            'katalog_id'        => $katalogId,
            'uye_id'            => $uyeId,
            'rezerve_baslangic' => now(),
            'rezerve_bitis'     => now()->addHours(24),
            'oduncAldiMi'       => 'false',
            'iptalMi'           => 'false',
            'suresiDolduMu'     => 'false',
        ]);

        $katalog->update(['kunyeDurum' => 'Rezerve']);

        UyeBekleme::query()->where('katalog_id', $katalogId)->where('uye_id', $uyeId)->delete();

        try {
            $uye = Uye::find($uyeId);
            $result = $this->webhookService->sendBildirim(
                tcList:  [$uye->tc_kimlik],
                title:   'Rezervasyonun Oluşturuldu 😊',
                message: $katalog->kunyeEserAdi . ' isimli kitabı senin için ayırdık. 24 saat içerisinde ' . $katalog->kutuphane->title . 'ne gelerek ödünç alabilirsin.',
            );

        } catch (\Exception $e) {
            // İade işlemi tamamlandı, sadece bildirim başarısız
            Log::error('Webhook gönderilemedi: ' . $e->getMessage());

        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Rezervasyon kaydı oluşturuldu.',
                'id'      => $rezerve->id,
            ]);
        }

        return redirect()->route('rezerve.index')->with('success', 'Rezervasyon kaydı oluşturuldu.');
    }
}
