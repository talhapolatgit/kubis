<?php

namespace App\Http\Controllers;

use App\Models\Katalog;
use App\Models\Kutuphane;
use Illuminate\Http\Request;

class EtiketController extends Controller
{
    // ─── Etiket Sayfası ─────────────────────────────────────────────────────────
    public function index()
    {
        $user = auth()->user();
        abort_unless($user?->hasYetki(20), 403);

        $kutuphaneler = Kutuphane::query()
            ->whereNull('deleted_at');

        if (!$user->isAdmin()) {
            $ids = $user->yetkiliKutuphaneIds();
            $kutuphaneler->whereIn('id', $ids ?: [-1]);
        }

        $kutuphaneler = $kutuphaneler
            ->orderBy('title')
            ->get(['id', 'title']);

        return view('book.etiket', compact('kutuphaneler'));
    }

    // ─── AJAX Kitap Arama (Etiket filtreleriyle) ─────────────────────────────────
    // GET /etiket/ara
    public function ara(Request $request)
    {
        $user = auth()->user();
        abort_unless($user?->hasYetki(20), 403);
        $perPage = min((int) $request->input('per_page', 50), 200);
        $query   = Katalog::query();
        $yetkiliKutuphaneIds = $user->yetkiliKutuphaneIds();

        // Admin tüm kütüphaneleri görebilir, diğer kullanıcılar yalnızca yetkilileri.
        if (!$user->isAdmin()) {
            $query->whereIn('katalog.kutuphaneId', $yetkiliKutuphaneIds ?: [-1]);
        }

        // ── Eser adı / ISBN ───────────────────────────────────────────────────
        if ($request->filled('search')) {
            $s = $request->input('search');
            $query->where(function ($q) use ($s) {
                $q->where('kunyeEserAdi',  'LIKE', "%{$s}%")
                    ->orWhere('kunyeISBNISSN', 'LIKE', "%{$s}%");
            });
        }

        // ── Demirbaş No ───────────────────────────────────────────────────────
        if ($request->filled('demirbasNo')) {
            $query->where('kunyeDemirbasKN', 'LIKE', '%' . $request->input('demirbasNo') . '%');
        }

        // ── Özel Notlar ───────────────────────────────────────────────────────
        if ($request->filled('ozelNotlar')) {
            $query->where('ozelNotlar', 'LIKE', '%' . $request->input('ozelNotlar') . '%');
        }

        // ── Kütüphane (seçmeli) ───────────────────────────────────────────────
        if ($request->filled('kutuphaneId')) {
            $kutuphaneId = (int) $request->input('kutuphaneId');
            if (!$user->isAdmin() && !in_array($kutuphaneId, $yetkiliKutuphaneIds ?: [], true)) {
                return response()->json(['rows' => []]);
            }
            $query->where('katalog.kutuphaneId', $kutuphaneId);
        }

        // ── Kayıt Tarihi Aralığı (created_at) ────────────────────────────────
        if ($request->filled('kayitBaslangic')) {
            $query->whereDate('katalog.created_at', '>=', $request->input('kayitBaslangic'));
        }

        if ($request->filled('kayitBitis')) {
            $query->whereDate('katalog.created_at', '<=', $request->input('kayitBitis'));
        }

        // ── Yalnızca etiketlenmeyenler ────────────────────────────────────────
        // Seçiliyse etiketlendi = 0 olan kayıtlar getirilir
        if ($request->boolean('etiketOlusmayanlar')) {
            $query->where('etiketlendi', 0);
        }

        $kitaplar = $query
            ->leftJoin('kutuphane', 'katalog.kutuphaneId', '=', 'kutuphane.id')
            ->select([
                'katalog.id',
                'kunyeEserAdi',
                'kunyeISBNISSN',
                'kunyeYazar',
                'kunyeDemirbasKN',
                'kunyeSiniflamaYer',
                'kunyeYayinTarihi',
                'kunyeKopya',
                'kunyeCilt',
                'ozelNotlar',
                'etiketOlustumu',
                'katalog.created_at',
                'kutuphane.title as kutuphaneAdi',
            ])
            ->orderBy('katalog.id', 'desc')
            ->limit($perPage)
            ->get();

        return response()->json(['rows' => $kitaplar->toArray()]);
    }

    // ─── Etiketlendi olarak işaretleme ──────────────────────────────────────────
    // POST /etiket/isaretle
    public function isaretle(Request $request)
    {
        $user = auth()->user();
        abort_unless($user?->hasYetki(20), 403);

        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'min:1'],
            'dry_run' => ['nullable', 'boolean'],
        ]);

        $ids = collect($validated['ids'])
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $yetkiliKutuphaneIds = $user->yetkiliKutuphaneIds();

        $baseQuery = Katalog::query()
            ->whereIn('id', $ids)
            ->whereNull('deleted_at')
            ->where(function ($q) {
                $q->whereNull('etiketlendi')
                    ->orWhere('etiketlendi', '!=', 1);
            });

        if (!$user->isAdmin()) {
            $baseQuery->whereIn('kutuphaneId', $yetkiliKutuphaneIds ?: [-1]);
        }

        $updateableCount = (clone $baseQuery)->count();
        if ($request->boolean('dry_run')) {
            return response()->json([
                'ok' => true,
                'updateable' => $updateableCount,
            ]);
        }

        $updatedCount = $baseQuery->update(['etiketlendi' => 1]);

        return response()->json([
            'ok' => true,
            'updated' => $updatedCount,
        ]);
    }
}
