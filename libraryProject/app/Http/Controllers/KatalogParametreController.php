<?php

namespace App\Http\Controllers;

use App\Models\AltTur;
use App\Models\Katalog;
use App\Models\KatalogDil;
use App\Models\Ortam;
use App\Models\Sekil;
use App\Models\Tur;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KatalogParametreController extends Controller
{
    /**
     * @return array<string, array{title:string, model:class-string, table:string, fk:?string}>
     */
    private function tabConfig(): array
    {
        return [
            'tur' => ['title' => 'Tür', 'model' => Tur::class, 'table' => 'tur', 'fk' => 'turId'],
            'alttur' => ['title' => 'Alt Tür', 'model' => AltTur::class, 'table' => 'alttur', 'fk' => 'altTurId'],
            'sekil' => ['title' => 'Şekil', 'model' => Sekil::class, 'table' => 'sekil', 'fk' => 'sekilId'],
            'ortam' => ['title' => 'Ortam', 'model' => Ortam::class, 'table' => 'ortam', 'fk' => 'ortamId'],
            'dil' => ['title' => 'Dil', 'model' => KatalogDil::class, 'table' => 'katalog_dil', 'fk' => null],
        ];
    }

    private function ensureAuthorized(): void
    {
        abort_unless(auth()->user()?->hasYetki(20), 403);
    }

    /**
     * @return array{title:string, model:class-string, table:string, fk:?string}
     */
    private function configFor(string $tab): array
    {
        $config = $this->tabConfig();
        abort_unless(array_key_exists($tab, $config), 404);

        return $config[$tab];
    }

    /**
     * @param array{title:string, model:class-string, table:string, fk:?string} $cfg
     */
    private function applyEserSayisiSelect($query, string $table, array $cfg): void
    {
        $query->select($table . '.*');

        if ($cfg['fk'] !== null) {
            $query->selectSub(
                Katalog::query()
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('katalog.' . $cfg['fk'], $table . '.id'),
                'eser_sayisi'
            );
            return;
        }

        // Dil tablosu katalog ile ID ile değil, ad alanı üzerinden bağlıdır (dil / dil2).
        $query->selectSub(
            Katalog::query()
                ->selectRaw('COUNT(*)')
                ->where(function ($q) use ($table) {
                    $q->whereColumn('katalog.kunyeDilKN', $table . '.ad')
                        ->orWhereColumn('katalog.kunyeDil2', $table . '.ad');
                }),
            'eser_sayisi'
        );
    }

    public function index(Request $request)
    {
        $this->ensureAuthorized();

        $config = $this->tabConfig();
        $activeTab = (string) $request->input('tab', 'tur');
        if (!array_key_exists($activeTab, $config)) {
            $activeTab = 'tur';
        }

        return view('katalog_parametre.list', compact('activeTab'));
    }

    public function list(Request $request, string $tab)
    {
        $this->ensureAuthorized();
        $cfg = $this->configFor($tab);
        /** @var \Illuminate\Database\Eloquent\Model $model */
        $model = $cfg['model'];
        $table = (new $model())->getTable();
        $query = $model::query();
        $this->applyEserSayisiSelect($query, $table, $cfg);

        if ($request->filled('search')) {
            $s = trim((string) $request->input('search'));
            $query->where('ad', 'like', '%' . $s . '%');
        }
        $statu = (string) $request->input('statu', '');
        if ($statu === 'aktif' || $statu === 'pasif') {
            $query->where('aktif', $statu === 'aktif' ? 1 : 0);
        }

        $sortBy = (string) $request->input('sort_by', '');
        $sortDir = strtolower((string) $request->input('sort_dir', 'asc')) === 'desc' ? 'desc' : 'asc';
        if ($sortBy === 'ad' || $sortBy === 'sira' || $sortBy === 'eser_sayisi') {
            $query->orderBy($sortBy, $sortDir)->orderBy('id');
        } else {
            $sortBy = '';
            $query->orderBy('sira')->orderBy('ad')->orderBy('id');
        }

        $perPage = (int) $request->input('per_page', 20);
        if (!in_array($perPage, [10, 20, 50, 100], true)) {
            $perPage = 20;
        }

        $rows = $query->paginate($perPage)->withQueryString();

        return response()->json([
            'rows' => collect($rows->items())->map(function ($r) {
                return [
                    'id' => (int) $r->id,
                    'ad' => (string) $r->ad,
                    'sira' => (int) ($r->sira ?? 0),
                    'eser_sayisi' => (int) ($r->eser_sayisi ?? 0),
                    'aktif' => (int) ($r->aktif ?? 0) === 1 ? 'aktif' : 'pasif',
                ];
            })->values()->all(),
            'meta' => [
                'title' => $cfg['title'],
                'total' => $rows->total(),
                'from' => $rows->firstItem(),
                'to' => $rows->lastItem(),
                'current_page' => $rows->currentPage(),
                'last_page' => $rows->lastPage(),
                'per_page' => $rows->perPage(),
                'sort_by' => $sortBy !== '' ? $sortBy : null,
                'sort_dir' => $sortBy !== '' ? $sortDir : null,
                'statu' => ($statu === 'aktif' || $statu === 'pasif') ? $statu : null,
            ],
        ]);
    }

    public function store(Request $request, string $tab)
    {
        $this->ensureAuthorized();
        $cfg = $this->configFor($tab);
        /** @var \Illuminate\Database\Eloquent\Model $model */
        $model = $cfg['model'];

        $validated = $request->validate([
            'ad' => ['required', 'string', 'max:100', Rule::unique($cfg['table'], 'ad')],
            'sira' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'aktif' => ['required', Rule::in(['aktif', 'pasif'])],
        ]);

        $model::create([
            'ad' => trim((string) $validated['ad']),
            'sira' => (int) ($validated['sira'] ?? 0),
            'aktif' => $validated['aktif'] === 'aktif' ? 1 : 0,
        ]);

        return response()->json(['message' => $cfg['title'] . ' eklendi.']);
    }

    public function update(Request $request, string $tab, int $id)
    {
        $this->ensureAuthorized();
        $cfg = $this->configFor($tab);
        /** @var \Illuminate\Database\Eloquent\Model $model */
        $model = $cfg['model'];
        $row = $model::query()->findOrFail($id);

        $validated = $request->validate([
            'ad' => ['required', 'string', 'max:100', Rule::unique($cfg['table'], 'ad')->ignore($id)],
            'sira' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'aktif' => ['required', Rule::in(['aktif', 'pasif'])],
        ]);

        $row->update([
            'ad' => trim((string) $validated['ad']),
            'sira' => (int) ($validated['sira'] ?? 0),
            'aktif' => $validated['aktif'] === 'aktif' ? 1 : 0,
        ]);

        return response()->json(['message' => $cfg['title'] . ' güncellendi.']);
    }

    public function destroy(string $tab, int $id)
    {
        $this->ensureAuthorized();
        $cfg = $this->configFor($tab);
        /** @var \Illuminate\Database\Eloquent\Model $model */
        $model = $cfg['model'];
        $row = $model::query()->findOrFail($id);

        $inUse = $cfg['fk'] !== null
            ? Katalog::query()->where($cfg['fk'], $id)->exists()
            : Katalog::query()
                ->where(function ($q) use ($row) {
                    $q->where('kunyeDilKN', (string) $row->ad)->orWhere('kunyeDil2', (string) $row->ad);
                })
                ->exists();
        if ($inUse) {
            return response()->json(['message' => 'Bu kayıt kataloglarda kullanıldığı için silinemez.'], 422);
        }

        $row->delete();

        return response()->json(['message' => $cfg['title'] . ' silindi.']);
    }

    public function export(Request $request, string $tab)
    {
        $this->ensureAuthorized();
        $cfg = $this->configFor($tab);
        /** @var \Illuminate\Database\Eloquent\Model $model */
        $model = $cfg['model'];
        $query = $model::query();

        if ($request->filled('search')) {
            $s = trim((string) $request->input('search'));
            $query->where('ad', 'like', '%' . $s . '%');
        }
        $statu = (string) $request->input('statu', '');
        if ($statu === 'aktif' || $statu === 'pasif') {
            $query->where('aktif', $statu === 'aktif' ? 1 : 0);
        }

        $sortBy = (string) $request->input('sort_by', '');
        $sortDir = strtolower((string) $request->input('sort_dir', 'asc')) === 'desc' ? 'desc' : 'asc';
        if ($sortBy === 'ad' || $sortBy === 'sira' || $sortBy === 'eser_sayisi') {
            $query->orderBy($sortBy, $sortDir)->orderBy('id');
        } else {
            $query->orderBy('sira')->orderBy('ad')->orderBy('id');
        }

        $table = (new $model())->getTable();
        $this->applyEserSayisiSelect($query, $table, $cfg);
        $rows = $query->get();
        $filename = 'katalog_parametre_' . $tab . '_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($out, ['Ad', 'Sıra', 'Eser Sayısı', 'Durum'], ';');

            foreach ($rows as $row) {
                fputcsv($out, [
                    (string) $row->ad,
                    (string) ((int) ($row->sira ?? 0)),
                    (string) ((int) ($row->eser_sayisi ?? 0)),
                    (int) ($row->aktif ?? 0) === 1 ? 'Aktif' : 'Pasif',
                ], ';');
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
