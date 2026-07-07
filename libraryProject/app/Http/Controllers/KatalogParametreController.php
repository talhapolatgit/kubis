<?php

namespace App\Http\Controllers;

use App\Models\AltTur;
use App\Models\Katalog;
use App\Models\KatalogDil;
use App\Models\Koleksiyon;
use App\Models\Ortam;
use App\Models\Sekil;
use App\Models\Tur;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
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
            'koleksiyon' => ['title' => 'Koleksiyon', 'model' => Koleksiyon::class, 'table' => 'koleksiyon', 'fk' => 'koleksiyon_id'],
        ];
    }

    /**
     * @param array{title:string, model:class-string, table:string, fk:?string} $cfg
     */
    private function adColumn(array $cfg): string
    {
        return $cfg['table'] === 'koleksiyon' ? 'title' : 'ad';
    }

    /**
     * @param array{title:string, model:class-string, table:string, fk:?string} $cfg
     */
    private function hasSiraColumn(array $cfg): bool
    {
        return $cfg['table'] !== 'koleksiyon';
    }

    /**
     * @param array{title:string, model:class-string, table:string, fk:?string} $cfg
     */
    private function createdAtColumn(array $cfg): string
    {
        return $cfg['table'] === 'koleksiyon' ? 'created_date' : 'created_at';
    }

    private function formatAuditDate(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '—';
        }

        try {
            return Carbon::parse($value)->format('d.m.Y H:i');
        } catch (\Throwable $e) {
            return '—';
        }
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
        $adColumn = $this->adColumn($cfg);

        if ($request->filled('search')) {
            $s = trim((string) $request->input('search'));
            $query->where($adColumn, 'like', '%' . $s . '%');
        }
        $statu = (string) $request->input('statu', '');
        if ($statu === 'aktif' || $statu === 'pasif') {
            if ($cfg['table'] === 'koleksiyon') {
                $query->where('statu', $statu);
            } else {
                $query->where('aktif', $statu === 'aktif' ? 1 : 0);
            }
        }

        $sortBy = (string) $request->input('sort_by', '');
        $sortDir = strtolower((string) $request->input('sort_dir', 'asc')) === 'desc' ? 'desc' : 'asc';
        if ($sortBy === 'ad') {
            $query->orderBy($adColumn, $sortDir)->orderBy('id');
        } elseif ($sortBy === 'sira' && $this->hasSiraColumn($cfg)) {
            $query->orderBy('sira', $sortDir)->orderBy('id');
        } elseif ($sortBy === 'eser_sayisi') {
            $query->orderBy('eser_sayisi', $sortDir)->orderBy('id');
        } else {
            $sortBy = '';
            if ($this->hasSiraColumn($cfg)) {
                $query->orderBy('sira');
            }
            $query->orderBy($adColumn)->orderBy('id');
        }

        $perPage = (int) $request->input('per_page', 20);
        if (!in_array($perPage, [10, 20, 50, 100], true)) {
            $perPage = 20;
        }

        $rows = $query->paginate($perPage)->withQueryString();
        $items = collect($rows->items());
        $userIds = $items
            ->flatMap(function ($r) {
                return [
                    (int) ($r->created_by ?? 0),
                    (int) ($r->updated_by ?? 0),
                ];
            })
            ->filter(fn (int $id) => $id > 0)
            ->unique()
            ->values()
            ->all();
        $userNames = $userIds === []
            ? collect()
            : User::query()->whereIn('id', $userIds)->pluck('name', 'id');
        $createdAtColumn = $this->createdAtColumn($cfg);

        return response()->json([
            'rows' => $items->map(function ($r) use ($userNames, $createdAtColumn) {
                $createdById = (int) ($r->created_by ?? 0);
                $updatedById = (int) ($r->updated_by ?? 0);

                return [
                    'id' => (int) $r->id,
                    'ad' => (string) ($r->title ?? $r->ad ?? ''),
                    'sira' => (int) ($r->sira ?? 0),
                    'eser_sayisi' => (int) ($r->eser_sayisi ?? 0),
                    'aktif' => (string) ($r->statu ?? '') === 'aktif' || (int) ($r->aktif ?? 0) === 1 ? 'aktif' : 'pasif',
                    'kayit_tarihi' => $this->formatAuditDate($r->{$createdAtColumn} ?? null),
                    'kaydeden' => (string) ($userNames->get($createdById) ?? '—'),
                    'guncelleme_tarihi' => $this->formatAuditDate($r->updated_at ?? null),
                    'guncelleyen' => (string) ($userNames->get($updatedById) ?? '—'),
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
        $adColumn = $this->adColumn($cfg);
        $isKoleksiyon = $cfg['table'] === 'koleksiyon';

        $validated = $request->validate([
            'ad' => ['required', 'string', 'max:100', Rule::unique($cfg['table'], $adColumn)],
            'sira' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'aktif' => ['required', Rule::in(['aktif', 'pasif'])],
        ]);

        $payload = [
            $adColumn => trim((string) $validated['ad']),
            'created_by' => auth()->id(),
        ];
        if ($isKoleksiyon) {
            $payload['statu'] = $validated['aktif'];
        } else {
            $payload['sira'] = (int) ($validated['sira'] ?? 0);
            $payload['aktif'] = $validated['aktif'] === 'aktif' ? 1 : 0;
        }
        $model::create($payload);

        return response()->json(['message' => $cfg['title'] . ' eklendi.']);
    }

    public function update(Request $request, string $tab, int $id)
    {
        $this->ensureAuthorized();
        $cfg = $this->configFor($tab);
        /** @var \Illuminate\Database\Eloquent\Model $model */
        $model = $cfg['model'];
        $row = $model::query()->findOrFail($id);
        $adColumn = $this->adColumn($cfg);
        $isKoleksiyon = $cfg['table'] === 'koleksiyon';

        $validated = $request->validate([
            'ad' => ['required', 'string', 'max:100', Rule::unique($cfg['table'], $adColumn)->ignore($id)],
            'sira' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'aktif' => ['required', Rule::in(['aktif', 'pasif'])],
        ]);

        $payload = [
            $adColumn => trim((string) $validated['ad']),
            'updated_by' => auth()->id(),
        ];
        if ($isKoleksiyon) {
            $payload['statu'] = $validated['aktif'];
        } else {
            $payload['sira'] = (int) ($validated['sira'] ?? 0);
            $payload['aktif'] = $validated['aktif'] === 'aktif' ? 1 : 0;
        }
        $row->update($payload);

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
        $adColumn = $this->adColumn($cfg);

        if ($request->filled('search')) {
            $s = trim((string) $request->input('search'));
            $query->where($adColumn, 'like', '%' . $s . '%');
        }
        $statu = (string) $request->input('statu', '');
        if ($statu === 'aktif' || $statu === 'pasif') {
            if ($cfg['table'] === 'koleksiyon') {
                $query->where('statu', $statu);
            } else {
                $query->where('aktif', $statu === 'aktif' ? 1 : 0);
            }
        }

        $sortBy = (string) $request->input('sort_by', '');
        $sortDir = strtolower((string) $request->input('sort_dir', 'asc')) === 'desc' ? 'desc' : 'asc';
        if ($sortBy === 'ad') {
            $query->orderBy($adColumn, $sortDir)->orderBy('id');
        } elseif ($sortBy === 'sira' && $this->hasSiraColumn($cfg)) {
            $query->orderBy('sira', $sortDir)->orderBy('id');
        } elseif ($sortBy === 'eser_sayisi') {
            $query->orderBy('eser_sayisi', $sortDir)->orderBy('id');
        } else {
            if ($this->hasSiraColumn($cfg)) {
                $query->orderBy('sira');
            }
            $query->orderBy($adColumn)->orderBy('id');
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
                    (string) ($row->title ?? $row->ad ?? ''),
                    (string) ((int) ($row->sira ?? 0)),
                    (string) ((int) ($row->eser_sayisi ?? 0)),
                    ((string) ($row->statu ?? '') === 'aktif' || (int) ($row->aktif ?? 0) === 1) ? 'Aktif' : 'Pasif',
                ], ';');
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
