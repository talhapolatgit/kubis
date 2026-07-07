<?php

namespace App\Http\Controllers;

use App\Models\Katalog;
use App\Models\Yazar;
use App\Support\TurkishSearch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class YazarController extends Controller
{
    /**
     * Bir yazara bağlı benzersiz katalog kaydı sayısı (pivot + eski katalog.yazarId;
     * kesişimde tek eser olarak sayılır).
     *
     * Not: UNION + dış tabloyla korelasyon MySQL/MySQL uyumlu sürümlerde hata üretebildiği için
     * tek seferlik gruplanmış alt tabloya LEFT JOIN kullanılır.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<\App\Models\Yazar>  $query
     */
    private function applyDistinctEserSayisiSubquery($query): void
    {
        $yazarKatalogPairs = DB::table('katalog_yazarlar')
            ->select('yazar_id', 'katalog_id')
            ->union(
                DB::table('katalog')
                    ->selectRaw('yazarId AS yazar_id, id AS katalog_id')
                    ->whereNotNull('yazarId')
            );

        $perYazarSayim = DB::query()
            ->fromSub($yazarKatalogPairs, 'yk_pairs')
            ->selectRaw('yk_pairs.yazar_id, COUNT(*) AS eser_agg')
            ->groupBy('yk_pairs.yazar_id');

        $query->leftJoinSub($perYazarSayim, 'yazar_eser_agg', function ($join): void {
            $join->on('yazar_eser_agg.yazar_id', '=', 'yazarlar.id');
        });

        // addSelect ile columns başlatılırsa yalnızca bu sütunlar seçilir; önce ana tabloyu seç.
        if ($query->getQuery()->columns === null) {
            $query->select($query->qualifyColumn('*'));
        }

        $query->addSelect(DB::raw('COALESCE(yazar_eser_agg.eser_agg, 0) AS eser_sayisi'));
    }

    private function buildFilteredQuery(Request $request)
    {
        $query = Yazar::query()
            ->with(['olusturan:id,name', 'guncelleyen:id,name']);

        $this->applyDistinctEserSayisiSubquery($query);

        $filterAd = trim((string) $request->input('filter_ad', ''));
        $filterSoyad = trim((string) $request->input('filter_soyad', ''));

        if ($filterAd !== '') {
            TurkishSearch::whereLike($query, 'ad', $filterAd);
        }
        if ($filterSoyad !== '') {
            TurkishSearch::whereLike($query, 'soyad', $filterSoyad);
        }

        // Eski tek alan `search` (yer imleri) — yeni filtreler boşsa uygulanır
        $legacySearch = trim((string) $request->input('search', ''));
        if ($filterAd === '' && $filterSoyad === '' && $legacySearch !== '') {
            TurkishSearch::whereLikeAny($query, ['ad', 'soyad', 'siralama_adi'], $legacySearch);
        }

        $activeEserDurumu = (string) $request->input('eser_durumu', 'tum');
        if ($activeEserDurumu === 'var') {
            $query->where(function ($q) {
                $q->whereHas('kataloglar')
                    ->orWhereHas('kataloglarLegacy');
            });
        } elseif ($activeEserDurumu === 'yok') {
            $query->whereDoesntHave('kataloglar')
                ->whereDoesntHave('kataloglarLegacy');
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

        $allowed = ['ad', 'soyad', 'eser_sayisi'];
        if (! in_array($sortBy, $allowed, true)) {
            $query->orderBy('siralama_adi')->orderBy('id');

            return;
        }

        if ($sortBy === 'eser_sayisi') {
            $query->orderBy('eser_sayisi', $sortDir)->orderBy('id');

            return;
        }

        $query->orderBy($sortBy, $sortDir)->orderBy('id');
    }

    private function canListYazarlar(): bool
    {
        $u = auth()->user();

        return $u && $u->hasYetki(22);
    }

    private function canManageYazarlar(): bool
    {
        $u = auth()->user();

        return $u && $u->hasYetki(23);
    }

    public function index(Request $request)
    {
        abort_unless($this->canListYazarlar(), 403);
        $canManageYazarlar = $this->canManageYazarlar();
        $activeFilterAd = trim((string) $request->input('filter_ad', ''));
        $activeFilterSoyad = trim((string) $request->input('filter_soyad', ''));
        $activeEserDurumu = (string) $request->input('eser_durumu', 'tum');

        $activeSortBy = '';
        $activeSortDir = 'asc';
        $sb = $request->input('sort_by');
        if (in_array((string) $sb, ['ad', 'soyad', 'eser_sayisi'], true)) {
            $activeSortBy = (string) $sb;
            $activeSortDir = strtolower((string) $request->input('sort_dir', 'asc')) === 'desc' ? 'desc' : 'asc';
        }

        $query = $this->buildFilteredQuery($request);
        $this->applySort($query, $request);

        $perPage = (int) $request->input('per_page', 20);
        if (!in_array($perPage, [10, 20, 50, 100], true)) {
            $perPage = 20;
        }

        $yazarlar = $query->paginate($perPage)->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'rows' => $yazarlar->map(function (Yazar $yazar) use ($canManageYazarlar) {
                    $eser = (int) ($yazar->eser_sayisi ?? 0);

                    return [
                        'id' => (int) $yazar->id,
                        'ad' => (string) $yazar->ad,
                        'soyad' => (string) ($yazar->soyad ?? ''),
                        'tam_ad' => (string) $yazar->tam_ad,
                        'fotograf_url' => $yazar->fotograf_url,
                        'eser_sayisi' => $eser,
                        'can_manage' => $canManageYazarlar,
                        'can_delete' => $canManageYazarlar && $eser === 0,
                        'kayit_tarihi' => $yazar->created_at?->format('d.m.Y H:i') ?? '—',
                        'kaydeden' => ($n = $yazar->olusturan?->name) !== null && $n !== '' ? $n : '—',
                        'guncelleme_tarihi' => $yazar->updated_at?->format('d.m.Y H:i') ?? '—',
                        'guncelleyen' => ($n = $yazar->guncelleyen?->name) !== null && $n !== '' ? $n : '—',
                    ];
                })->values()->all(),
                'meta' => [
                    'total' => $yazarlar->total(),
                    'from' => $yazarlar->firstItem(),
                    'to' => $yazarlar->lastItem(),
                    'current_page' => $yazarlar->currentPage(),
                    'last_page' => $yazarlar->lastPage(),
                    'per_page' => $yazarlar->perPage(),
                    'sort_by' => $activeSortBy !== '' ? $activeSortBy : null,
                    'sort_dir' => $activeSortBy !== '' ? $activeSortDir : null,
                ],
            ]);
        }

        $mergeYazarOptions = tap(Yazar::query(), fn ($q) => $this->applyDistinctEserSayisiSubquery($q))
            ->orderBy('siralama_adi')
            ->orderBy('ad')
            ->orderBy('soyad')
            ->get()
            ->map(fn (Yazar $y) => [
                'id' => (int) $y->id,
                'tam_ad' => (string) $y->tam_ad,
                'eser_sayisi' => (int) ($y->eser_sayisi ?? 0),
            ]);

        return view('yazar.list', compact(
            'yazarlar',
            'canManageYazarlar',
            'activeFilterAd',
            'activeFilterSoyad',
            'activeEserDurumu',
            'perPage',
            'activeSortBy',
            'activeSortDir',
            'mergeYazarOptions'
        ));
    }

    public function merge(Request $request)
    {
        abort_unless($this->canManageYazarlar(), 403);

        $data = $request->validate([
            'main_yazar_id' => ['required', 'integer', 'exists:yazarlar,id'],
            'merge_yazar_ids' => ['required', 'array', 'min:1'],
            'merge_yazar_ids.*' => ['integer', 'exists:yazarlar,id', 'distinct'],
        ], [
            'main_yazar_id.required' => 'Asıl yazar seçimi zorunludur.',
            'merge_yazar_ids.required' => 'Aktarılacak yazarları seçin.',
            'merge_yazar_ids.min' => 'En az bir aktarılacak yazar seçin.',
        ]);

        $mainId = (int) $data['main_yazar_id'];
        $mergeIds = collect($data['merge_yazar_ids'])
            ->map(fn ($v) => (int) $v)
            ->filter(fn ($id) => $id > 0 && $id !== $mainId)
            ->unique()
            ->values()
            ->all();

        if ($mergeIds === []) {
            return response()->json(['message' => 'Aktarılacak yazarlar içinde asıl yazar dışı en az bir kayıt olmalıdır.'], 422);
        }

        DB::transaction(function () use ($mainId, $mergeIds) {
            $authors = Yazar::query()->whereIn('id', array_merge([$mainId], $mergeIds))->get()->keyBy('id');
            $main = $authors->get($mainId);
            abort_if(!$main, 422, 'Asıl yazar bulunamadı.');

            $affectedKatalogIds = DB::table('katalog_yazarlar')
                ->whereIn('yazar_id', $mergeIds)
                ->pluck('katalog_id')
                ->merge(
                    Katalog::query()->whereIn('yazarId', $mergeIds)->pluck('id')
                )
                ->map(fn ($v) => (int) $v)
                ->unique()
                ->values()
                ->all();

            Katalog::query()->whereIn('yazarId', $mergeIds)->update(['yazarId' => $mainId]);

            $now = now();
            foreach ($affectedKatalogIds as $katalogId) {
                $pivotIds = DB::table('katalog_yazarlar')
                    ->where('katalog_id', $katalogId)
                    ->orderBy('sira')
                    ->orderBy('id')
                    ->pluck('yazar_id')
                    ->map(fn ($v) => (int) $v)
                    ->all();

                $normalized = [];
                foreach ($pivotIds as $yazarId) {
                    $normalized[] = in_array($yazarId, $mergeIds, true) ? $mainId : $yazarId;
                }
                if ($normalized === []) {
                    $normalized[] = $mainId;
                }

                $finalIds = [];
                foreach ($normalized as $id) {
                    if (!in_array($id, $finalIds, true)) {
                        $finalIds[] = $id;
                    }
                }

                DB::table('katalog_yazarlar')->where('katalog_id', $katalogId)->delete();
                $insertRows = [];
                foreach ($finalIds as $index => $id) {
                    $insertRows[] = [
                        'katalog_id' => $katalogId,
                        'yazar_id' => $id,
                        'sira' => $index,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
                if ($insertRows !== []) {
                    DB::table('katalog_yazarlar')->insert($insertRows);
                }

                $names = [];
                foreach ($finalIds as $id) {
                    $a = $authors->get($id) ?: Yazar::find($id);
                    if ($a) $names[] = $a->tam_ad;
                }
                Katalog::query()->where('id', $katalogId)->update([
                    'kunyeYazar' => $names !== [] ? implode(' ; ', $names) : null,
                    'yazarId' => $finalIds[0] ?? $mainId,
                ]);
            }

            foreach ($mergeIds as $id) {
                $y = $authors->get($id);
                if ($y && $y->fotograf_path && Storage::disk('public')->exists($y->fotograf_path)) {
                    Storage::disk('public')->delete($y->fotograf_path);
                }
            }

            DB::table('katalog_yazarlar')->whereIn('yazar_id', $mergeIds)->delete();
            Yazar::query()->whereIn('id', $mergeIds)->delete();
        });

        return response()->json(['message' => 'Yazar birleştirme işlemi tamamlandı.']);
    }

    public function export(Request $request)
    {
        abort_unless($this->canListYazarlar(), 403);

        $query = $this->buildFilteredQuery($request);
        $this->applySort($query, $request);
        $rows = $query->get();

        $filename = 'yazarlar_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($out, ['Ad', 'Soyad', 'Eser Sayısı'], ';');
            foreach ($rows as $yazar) {
                fputcsv($out, [
                    $yazar->ad,
                    $yazar->soyad,
                    $yazar->eser_sayisi ?? 0,
                ], ';');
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function store(Request $request)
    {
        abort_unless($this->canManageYazarlar(), 403);

        $validated = $this->validatePayload($request);

        $fotoPath = null;
        if ($request->hasFile('fotograf')) {
            $fotoPath = $request->file('fotograf')->store('yazarlar', 'public');
        }

        Yazar::create([
            'ad' => $validated['ad'],
            'soyad' => $validated['soyad'],
            'siralama_adi' => $this->buildSiralamaAdi($validated['ad'], $validated['soyad']),
            'fotograf_path' => $fotoPath,
            'created_by' => Auth::id(),
        ]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['message' => 'Yazar başarıyla eklendi.']);
        }

        return redirect()->route('yazarlar.index')->with('success', 'Yazar başarıyla eklendi.');
    }

    public function update(Request $request, Yazar $yazar)
    {
        abort_unless($this->canManageYazarlar(), 403);

        $validated = $this->validatePayload($request, $yazar->id);

        $update = [
            'ad' => $validated['ad'],
            'soyad' => $validated['soyad'],
            'siralama_adi' => $this->buildSiralamaAdi($validated['ad'], $validated['soyad']),
            'updated_by' => Auth::id(),
        ];

        if ($request->hasFile('fotograf')) {
            if ($yazar->fotograf_path && Storage::disk('public')->exists($yazar->fotograf_path)) {
                Storage::disk('public')->delete($yazar->fotograf_path);
            }
            $update['fotograf_path'] = $request->file('fotograf')->store('yazarlar', 'public');
        } elseif ($request->boolean('fotograf_kaldir')) {
            if ($yazar->fotograf_path && Storage::disk('public')->exists($yazar->fotograf_path)) {
                Storage::disk('public')->delete($yazar->fotograf_path);
            }
            $update['fotograf_path'] = null;
        }

        $yazar->update($update);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['message' => 'Yazar bilgisi güncellendi.']);
        }

        return redirect()->route('yazarlar.index')->with('success', 'Yazar bilgisi güncellendi.');
    }

    public function destroy(Request $request, Yazar $yazar)
    {
        abort_unless($this->canManageYazarlar(), 403);

        if ($this->yazarHasLinkedKatalog($yazar)) {
            $msg = 'Bu yazara bağlı katalog kaydı bulunduğu için silinemez.';
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['message' => $msg], 422);
            }

            return redirect()->route('yazarlar.index')->withErrors(['sil' => $msg]);
        }

        if ($yazar->fotograf_path && Storage::disk('public')->exists($yazar->fotograf_path)) {
            Storage::disk('public')->delete($yazar->fotograf_path);
        }

        $yazar->kataloglar()->detach();
        $yazar->delete();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['message' => 'Yazar silindi.']);
        }

        return redirect()->route('yazarlar.index')->with('success', 'Yazar silindi.');
    }

    private function yazarHasLinkedKatalog(Yazar $yazar): bool
    {
        return $yazar->kataloglar()->exists()
            || Katalog::where('yazarId', $yazar->id)->exists();
    }

    private function validatePayload(Request $request, ?int $ignoreId = null): array
    {
        $ad = trim((string) $request->input('ad', ''));
        $soyad = trim((string) $request->input('soyad', ''));

        $request->merge([
            'ad' => $ad,
            'soyad' => $soyad,
        ]);

        return $request->validate([
            'ad' => ['required', 'string', 'max:255', Rule::unique('yazarlar', 'ad')->where(function ($q) use ($soyad) {
                $q->where('soyad', $soyad);
            })->ignore($ignoreId)],
            'soyad' => ['nullable', 'string', 'max:255'],
            'fotograf' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
            'fotograf_kaldir' => ['nullable', 'boolean'],
        ], [
            'ad.required' => 'Yazar adı zorunludur.',
            'ad.unique' => 'Bu ad/soyad kombinasyonuyla bir yazar zaten kayıtlı.',
            'fotograf.image' => 'Fotoğraf alanına geçerli bir görsel yükleyin.',
            'fotograf.mimes' => 'Fotoğraf formatı jpg, jpeg, png veya webp olmalıdır.',
            'fotograf.max' => 'Fotoğraf boyutu en fazla 3MB olabilir.',
        ]);
    }

    private function buildSiralamaAdi(string $ad, string $soyad): string
    {
        $ad = trim($ad);
        $soyad = trim($soyad);

        return $soyad !== '' ? ($soyad . ', ' . $ad) : $ad;
    }
}

