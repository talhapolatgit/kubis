<?php

namespace App\Http\Controllers;

use App\Models\Katalog;
use Illuminate\Http\Request;

class EtiketController extends Controller
{
    // ─── Etiket Sayfası ─────────────────────────────────────────────────────────
    public function index()
    {
        abort_unless(auth()->user()?->hasYetki(20), 403);
        return view('book.etiket');
    }

    // ─── AJAX Kitap Arama (Etiket filtreleriyle) ─────────────────────────────────
    // GET /etiket/ara
    public function ara(Request $request)
    {
        abort_unless(auth()->user()?->hasYetki(20), 403);
        $perPage = min((int) $request->input('per_page', 50), 200);
        $query   = Katalog::query();

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

        // ── Kayıt Tarihi Aralığı (created_at) ────────────────────────────────
        if ($request->filled('kayitBaslangic')) {
            $query->whereDate('katalog.created_at', '>=', $request->input('kayitBaslangic'));
        }

        if ($request->filled('kayitBitis')) {
            $query->whereDate('katalog.created_at', '<=', $request->input('kayitBitis'));
        }

        // ── Yalnızca etiket oluşmayanlar ──────────────────────────────────────
        // Seçiliyse etiketOlustumu = 'evet' olan kayıtlar getirilir
        if ($request->boolean('etiketOlusmayanlar')) {
            $query->where('etiketOlustumu', 'hayir');
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
}
