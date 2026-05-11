<?php

namespace App\Http\Controllers;

use App\Models\Katalog;
use App\Models\Yayinevi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class YayineviController extends Controller
{
    private function buildFilteredQuery(Request $request)
    {
        $query = Yayinevi::query()
            ->with(['olusturan:id,name', 'guncelleyen:id,name'])
            ->withCount('kataloglar as eser_sayisi');

        $filterAd = trim((string) $request->input('filter_ad', ''));

        if ($filterAd !== '') {
            $query->where('ad', 'like', '%' . $filterAd . '%');
        }

        $legacySearch = trim((string) $request->input('search', ''));
        if ($filterAd === '' && $legacySearch !== '') {
            $query->where('ad', 'like', '%' . $legacySearch . '%');
        }

        $activeEserDurumu = (string) $request->input('eser_durumu', 'tum');
        if ($activeEserDurumu === 'var') {
            $query->has('kataloglar');
        } elseif ($activeEserDurumu === 'yok') {
            $query->doesntHave('kataloglar');
        }

        return $query;
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     */
    private function applySort($query, Request $request): void
    {
        $sortBy = (string) $request->input('sort_by', '');
        $sortDir = strtolower((string) $request->input('sort_dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        $allowed = ['ad', 'eser_sayisi'];
        if (! in_array($sortBy, $allowed, true)) {
            $query->orderBy('ad')->orderBy('id');

            return;
        }

        if ($sortBy === 'eser_sayisi') {
            $query->orderBy('eser_sayisi', $sortDir)->orderBy('id');

            return;
        }

        $query->orderBy('ad', $sortDir)->orderBy('id');
    }

    private function canListYayinevleri(): bool
    {
        $u = auth()->user();

        return $u && $u->hasYetki(24);
    }

    private function canManageYayinevleri(): bool
    {
        $u = auth()->user();

        return $u && $u->hasYetki(25);
    }

    public function index(Request $request)
    {
        abort_unless($this->canListYayinevleri(), 403);
        $canManageYayinevleri = $this->canManageYayinevleri();
        $activeFilterAd = trim((string) $request->input('filter_ad', ''));
        $activeEserDurumu = (string) $request->input('eser_durumu', 'tum');

        $activeSortBy = '';
        $activeSortDir = 'asc';
        $sb = $request->input('sort_by');
        if (in_array((string) $sb, ['ad', 'eser_sayisi'], true)) {
            $activeSortBy = (string) $sb;
            $activeSortDir = strtolower((string) $request->input('sort_dir', 'asc')) === 'desc' ? 'desc' : 'asc';
        }

        $query = $this->buildFilteredQuery($request);
        $this->applySort($query, $request);

        $perPage = (int) $request->input('per_page', 20);
        if (! in_array($perPage, [10, 20, 50, 100], true)) {
            $perPage = 20;
        }

        $yayinevleri = $query->paginate($perPage)->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'rows' => $yayinevleri->map(function (Yayinevi $y) use ($canManageYayinevleri) {
                    $eser = (int) ($y->eser_sayisi ?? 0);

                    return [
                        'id' => (int) $y->id,
                        'ad' => (string) $y->ad,
                        'eser_sayisi' => $eser,
                        'can_manage' => $canManageYayinevleri,
                        'can_delete' => $canManageYayinevleri && $eser === 0,
                        'kayit_tarihi' => $y->created_at?->format('d.m.Y H:i') ?? '—',
                        'kaydeden' => ($n = $y->olusturan?->name) !== null && $n !== '' ? $n : '—',
                        'guncelleme_tarihi' => $y->updated_at?->format('d.m.Y H:i') ?? '—',
                        'guncelleyen' => ($n = $y->guncelleyen?->name) !== null && $n !== '' ? $n : '—',
                    ];
                })->values()->all(),
                'meta' => [
                    'total' => $yayinevleri->total(),
                    'from' => $yayinevleri->firstItem(),
                    'to' => $yayinevleri->lastItem(),
                    'current_page' => $yayinevleri->currentPage(),
                    'last_page' => $yayinevleri->lastPage(),
                    'per_page' => $yayinevleri->perPage(),
                    'sort_by' => $activeSortBy !== '' ? $activeSortBy : null,
                    'sort_dir' => $activeSortBy !== '' ? $activeSortDir : null,
                ],
            ]);
        }

        $mergeYayineviOptions = Yayinevi::query()
            ->withCount('kataloglar as eser_sayisi')
            ->orderBy('ad')
            ->get(['id', 'ad'])
            ->map(fn (Yayinevi $y) => [
                'id' => (int) $y->id,
                'ad' => (string) $y->ad,
                'eser_sayisi' => (int) ($y->eser_sayisi ?? 0),
            ]);

        return view('yayinevi.list', compact(
            'yayinevleri',
            'canManageYayinevleri',
            'activeFilterAd',
            'activeEserDurumu',
            'perPage',
            'activeSortBy',
            'activeSortDir',
            'mergeYayineviOptions'
        ));
    }

    public function merge(Request $request)
    {
        abort_unless($this->canManageYayinevleri(), 403);

        $data = $request->validate([
            'main_yayinevi_id' => ['required', 'integer', 'exists:yayinevleri,id'],
            'merge_yayinevi_ids' => ['required', 'array', 'min:1'],
            'merge_yayinevi_ids.*' => ['integer', 'exists:yayinevleri,id', 'distinct'],
        ], [
            'main_yayinevi_id.required' => 'Asıl yayınevi seçimi zorunludur.',
            'merge_yayinevi_ids.required' => 'Aktarılacak yayınevlerini seçin.',
            'merge_yayinevi_ids.min' => 'En az bir aktarılacak yayınevi seçin.',
        ]);

        $mainId = (int) $data['main_yayinevi_id'];
        $mergeIds = collect($data['merge_yayinevi_ids'])
            ->map(fn ($v) => (int) $v)
            ->filter(fn ($id) => $id > 0 && $id !== $mainId)
            ->unique()
            ->values()
            ->all();

        if ($mergeIds === []) {
            return response()->json(['message' => 'Aktarılacak yayınevleri içinde asıl yayınevi dışı en az bir kayıt olmalıdır.'], 422);
        }

        DB::transaction(function () use ($mainId, $mergeIds) {
            $main = Yayinevi::query()->findOrFail($mainId);
            Katalog::query()->whereIn('yayineviId', $mergeIds)->update([
                'yayineviId' => $mainId,
                'kunyeYayinlayan' => $main->ad,
            ]);

            Yayinevi::query()->whereIn('id', $mergeIds)->delete();
        });

        return response()->json(['message' => 'Yayınevi birleştirme işlemi tamamlandı.']);
    }

    public function export(Request $request)
    {
        abort_unless($this->canListYayinevleri(), 403);

        $query = $this->buildFilteredQuery($request);
        $this->applySort($query, $request);
        $rows = $query->get();

        $filename = 'yayinevleri_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($out, ['Yayınevi Adı', 'Eser Sayısı'], ';');
            foreach ($rows as $y) {
                fputcsv($out, [
                    $y->ad,
                    $y->eser_sayisi ?? 0,
                ], ';');
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function store(Request $request)
    {
        abort_unless($this->canManageYayinevleri(), 403);

        $validated = $this->validatePayload($request);

        Yayinevi::create([
            'ad' => $validated['ad'],
            'created_by' => Auth::id(),
        ]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['message' => 'Yayınevi başarıyla eklendi.']);
        }

        return redirect()->route('yayinevleri.index')->with('success', 'Yayınevi başarıyla eklendi.');
    }

    public function update(Request $request, Yayinevi $yayinevi)
    {
        abort_unless($this->canManageYayinevleri(), 403);

        $validated = $this->validatePayload($request, $yayinevi->id);

        $yayinevi->update([
            'ad' => $validated['ad'],
            'updated_by' => Auth::id(),
        ]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['message' => 'Yayınevi bilgisi güncellendi.']);
        }

        return redirect()->route('yayinevleri.index')->with('success', 'Yayınevi bilgisi güncellendi.');
    }

    public function destroy(Request $request, Yayinevi $yayinevi)
    {
        abort_unless($this->canManageYayinevleri(), 403);

        if ($yayinevi->kataloglar()->exists()) {
            $msg = 'Bu yayınevine bağlı katalog kaydı bulunduğu için silinemez.';
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['message' => $msg], 422);
            }

            return redirect()->route('yayinevleri.index')->withErrors(['sil' => $msg]);
        }

        $yayinevi->delete();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['message' => 'Yayınevi silindi.']);
        }

        return redirect()->route('yayinevleri.index')->with('success', 'Yayınevi silindi.');
    }

    private function validatePayload(Request $request, ?int $ignoreId = null): array
    {
        $ad = trim((string) $request->input('ad', ''));

        $request->merge([
            'ad' => $ad,
        ]);

        return $request->validate([
            'ad' => ['required', 'string', 'max:255', Rule::unique('yayinevleri', 'ad')->ignore($ignoreId)],
        ], [
            'ad.required' => 'Yayınevi adı zorunludur.',
            'ad.unique' => 'Bu yayınevi adı zaten kayıtlı.',
        ]);
    }
}
