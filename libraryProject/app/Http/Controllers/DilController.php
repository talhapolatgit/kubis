<?php

namespace App\Http\Controllers;

use App\Models\Katalog;
use App\Models\KatalogDil;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DilController extends Controller
{
    private function authorizeAccess(): void
    {
        abort_unless(auth()->user()?->hasYetki(20), 403);
    }

    public function index(Request $request)
    {
        $this->authorizeAccess();

        $perPage = (int) $request->input('per_page', 20);
        if (! in_array($perPage, [10, 20, 50, 100], true)) {
            $perPage = 20;
        }

        $query = KatalogDil::query();
        if ($request->filled('search')) {
            $s = trim((string) $request->input('search'));
            $query->where('ad', 'like', '%' . $s . '%');
        }
        $activeStatu = in_array((string) $request->input('statu'), ['aktif', 'pasif'], true)
            ? (string) $request->input('statu')
            : '';
        if ($activeStatu !== '') {
            $query->where('aktif', $activeStatu === 'aktif' ? 1 : 0);
        }

        $activeSortBy = '';
        $activeSortDir = 'asc';
        $sortBy = (string) $request->input('sort_by', '');
        $sortDir = strtolower((string) $request->input('sort_dir', 'asc')) === 'desc' ? 'desc' : 'asc';
        if (in_array($sortBy, ['ad', 'sira'], true)) {
            $activeSortBy = $sortBy;
            $activeSortDir = $sortDir;
            $query->orderBy($sortBy, $sortDir)->orderBy('id');
        } else {
            $query->orderBy('sira')->orderBy('ad')->orderBy('id');
        }

        $diller = $query->paginate($perPage)->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'rows' => $diller->map(function (KatalogDil $d) {
                    return [
                        'id' => (int) $d->id,
                        'ad' => (string) $d->ad,
                        'sira' => (int) ($d->sira ?? 0),
                        'aktif' => (int) ($d->aktif ?? 0) === 1 ? 'aktif' : 'pasif',
                    ];
                })->values()->all(),
                'meta' => [
                    'total' => $diller->total(),
                    'from' => $diller->firstItem(),
                    'to' => $diller->lastItem(),
                    'current_page' => $diller->currentPage(),
                    'last_page' => $diller->lastPage(),
                    'per_page' => $diller->perPage(),
                    'sort_by' => $activeSortBy !== '' ? $activeSortBy : null,
                    'sort_dir' => $activeSortBy !== '' ? $activeSortDir : null,
                    'statu' => $activeStatu !== '' ? $activeStatu : null,
                ],
            ]);
        }

        return view('dil.list', compact('diller', 'activeStatu', 'activeSortBy', 'activeSortDir', 'perPage'));
    }

    public function export(Request $request)
    {
        $this->authorizeAccess();

        $query = KatalogDil::query();
        if ($request->filled('search')) {
            $s = trim((string) $request->input('search'));
            $query->where('ad', 'like', '%' . $s . '%');
        }
        $activeStatu = in_array((string) $request->input('statu'), ['aktif', 'pasif'], true)
            ? (string) $request->input('statu')
            : '';
        if ($activeStatu !== '') {
            $query->where('aktif', $activeStatu === 'aktif' ? 1 : 0);
        }

        $sortBy = (string) $request->input('sort_by', '');
        $sortDir = strtolower((string) $request->input('sort_dir', 'asc')) === 'desc' ? 'desc' : 'asc';
        if (in_array($sortBy, ['ad', 'sira'], true)) {
            $query->orderBy($sortBy, $sortDir)->orderBy('id');
        } else {
            $query->orderBy('sira')->orderBy('ad')->orderBy('id');
        }

        $rows = $query->get(['ad', 'sira', 'aktif']);
        $filename = 'dil_listesi_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($out, ['Dil', 'Sıra', 'Durum'], ';');
            foreach ($rows as $d) {
                fputcsv($out, [
                    (string) $d->ad,
                    (int) ($d->sira ?? 0),
                    (int) ($d->aktif ?? 0) === 1 ? 'Aktif' : 'Pasif',
                ], ';');
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function store(Request $request)
    {
        $this->authorizeAccess();

        $validated = $request->validate([
            'ad' => ['required', 'string', 'max:100', Rule::unique('katalog_dil', 'ad')],
            'sira' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'aktif' => ['required', Rule::in(['aktif', 'pasif'])],
        ]);

        KatalogDil::create([
            'ad' => trim((string) $validated['ad']),
            'sira' => (int) ($validated['sira'] ?? 0),
            'aktif' => $validated['aktif'] === 'aktif' ? 1 : 0,
        ]);

        return response()->json(['message' => 'Dil başarıyla eklendi.']);
    }

    public function update(Request $request, KatalogDil $dil)
    {
        $this->authorizeAccess();

        $validated = $request->validate([
            'ad' => ['required', 'string', 'max:100', Rule::unique('katalog_dil', 'ad')->ignore($dil->id)],
            'sira' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'aktif' => ['required', Rule::in(['aktif', 'pasif'])],
        ]);

        $dil->update([
            'ad' => trim((string) $validated['ad']),
            'sira' => (int) ($validated['sira'] ?? 0),
            'aktif' => $validated['aktif'] === 'aktif' ? 1 : 0,
        ]);

        return response()->json(['message' => 'Dil güncellendi.']);
    }

    public function destroy(KatalogDil $dil)
    {
        $this->authorizeAccess();

        $kullanimVar = Katalog::query()
            ->where('kunyeDilKN', $dil->ad)
            ->orWhere('kunyeDil2', $dil->ad)
            ->exists();

        if ($kullanimVar) {
            return response()->json(['message' => 'Bu dil katalog kayıtlarında kullanıldığı için silinemez.'], 422);
        }

        $dil->delete();

        return response()->json(['message' => 'Dil silindi.']);
    }
}
