<?php

namespace App\Http\Controllers;

use App\Models\Katalog;
use App\Models\Kategori;
use App\Models\Kutuphane;
use App\Models\Yazar;
use App\Models\Yayinevi;
use App\Models\Tur;
use App\Models\AltTur;
use App\Models\Sekil;
use App\Models\Ortam;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class KatalogController extends Controller
{
    private function canListAllBooks(): bool
    {
        $u = auth()->user();
        return $u && ($u->hasYetki(4) || $u->hasYetki(5));
    }

    private function canListScopedBooks(): bool
    {
        $u = auth()->user();
        return $u && ($u->hasYetki(1) || $u->hasYetki(2));
    }

    private function canUpdateBooks(): bool
    {
        $u = auth()->user();
        return $u && ($u->hasYetki(2) || $u->hasYetki(5));
    }

    private function canSaveBooks(): bool
    {
        $u = auth()->user();
        return $u && ($u->hasYetki(3) || $u->hasYetki(6));
    }

    private function allowedKutuphaneIdsForSave(): array
    {
        $u = auth()->user();
        if (!$u) return [];
        if ($u->hasYetki(6)) {
            return Kutuphane::whereNull('deleted_at')->pluck('id')->map(fn($v) => (int) $v)->values()->all();
        }
        if ($u->hasYetki(3)) {
            return $u->yetkiliKutuphaneIds();
        }
        return [];
    }

    private function allowedKutuphaneIdsForUpdate(): array
    {
        $u = auth()->user();
        if (!$u) return [];
        if ($u->hasYetki(5)) {
            return Kutuphane::whereNull('deleted_at')->pluck('id')->map(fn($v) => (int) $v)->values()->all();
        }
        if ($u->hasYetki(2)) {
            return $u->yetkiliKutuphaneIds();
        }
        return [];
    }

    // ─── Liste ──────────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $bookcount = Katalog::count();

        if (!$request->ajax() && !$request->wantsJson()) {
            $kategoriler  = Kategori::aktif()->orderBy('title')->get(['id', 'title']);
            abort_unless($this->canListAllBooks() || $this->canListScopedBooks(), 403);

            if ($this->canListAllBooks()) {
                $kutuphaneler = Kutuphane::whereNull('deleted_at')->orderBy('title')->get(['id', 'title']);
            } else {
                $ids = auth()->user()->yetkiliKutuphaneIds();
                $kutuphaneler = Kutuphane::whereNull('deleted_at')
                    ->whereIn('id', $ids ?: [-1])
                    ->orderBy('title')
                    ->get(['id', 'title']);
            }
            // Sadece en az bir kitabı olan yazarları getir
            $yazarIds    = Katalog::whereNotNull('yazarId')->distinct()->pluck('yazarId');
            $yazarlar    = Yazar::whereIn('id', $yazarIds)->orderBy('ad')->get(['id', 'ad']);
            // Sadece en az bir kitabı olan yayınevlerini getir
            $yayineviIds = Katalog::whereNotNull('yayineviId')->distinct()->pluck('yayineviId');
            $yayinevleri = Yayinevi::whereIn('id', $yayineviIds)->orderBy('ad')->get(['id', 'ad']);
            $turler      = \App\Models\Tur::aktif()->orderBy('sira')->get(['id', 'ad']);
            return view('book.list', compact('bookcount', 'kategoriler', 'kutuphaneler', 'yazarlar', 'yayinevleri', 'turler'));
        }

        $perPage = in_array((int) $request->input('per_page'), [10, 20, 50, 100, 500])
            ? (int) $request->input('per_page') : 20;

        abort_unless($this->canListAllBooks() || $this->canListScopedBooks(), 403);

        $query = Katalog::query();

        if (!$this->canListAllBooks()) {
            $ids = auth()->user()->yetkiliKutuphaneIds();
            $query->whereIn('kutuphaneId', $ids ?: [-1]);
        }

        if ($request->filled('search')) {
            $s = $request->input('search');
            $query->where(function ($q) use ($s) {
                $q->where('kunyeEserAdi',  'LIKE', "%{$s}%")
                    ->orWhere('kunyeISBNISSN', 'LIKE', "%{$s}%");
            });
        }
        if ($request->filled('kategori'))     $query->where('kunyeKategori', (int) $request->input('kategori'));
        if ($request->filled('siniflamaYer')) $query->where('kunyeSiniflamaYer', 'LIKE', '%' . $request->input('siniflamaYer') . '%');
        if ($request->filled('kutuphaneId'))  $query->where('kutuphaneId', (int) $request->input('kutuphaneId'));
        if ($request->filled('turId'))        $query->where('turId', (int) $request->input('turId'));
        if ($request->filled('durum'))        $query->where('kunyeDurum', $request->input('durum'));
        if ($request->filled('dil'))          $query->where('kunyeDilKN', $request->input('dil'));
        if ($request->filled('konuBasligi'))  $query->where('kunyeKonuBasligi', 'LIKE', '%' . $request->input('konuBasligi') . '%');
        if ($request->filled('ozelNotlar'))   $query->where(function ($q) use ($request) {
            $n = $request->input('ozelNotlar');
            $q->where('ozelNotlar',  'LIKE', "%{$n}%")
                ->orWhere('ozelNotlar2', 'LIKE', "%{$n}%")
                ->orWhere('ozelNotlar3', 'LIKE', "%{$n}%");
        });
        if ($request->filled('oduncVerilebilir')) {
            $query->where('oduncVerilemez', $request->input('oduncVerilebilir') === 'evet' ? 0 : 1);
        }
        if ($request->filled('etiketlendi')) {
            $query->where('etiketlendi', $request->input('etiketlendi') === 'evet' ? 1 : 0);
        }

        // Yazar filtresi: önce ID ile dene (dropdown), yoksa metin LIKE
        if ($request->filled('yazarId')) {
            $query->where('yazarId', (int) $request->input('yazarId'));
        } elseif ($request->filled('yazar')) {
            $query->where('kunyeYazar', 'LIKE', '%' . $request->input('yazar') . '%');
        }

        // Yayınevi filtresi: önce ID ile dene, yoksa metin LIKE
        if ($request->filled('yayineviId')) {
            $query->where('yayineviId', (int) $request->input('yayineviId'));
        } elseif ($request->filled('yayinevi')) {
            $query->where('kunyeYayinlayan', 'LIKE', '%' . $request->input('yayinevi') . '%');
        }

        $kitaplar = $query->orderBy('id', 'desc')->paginate($perPage);

        return response()->json([
            'rows'          => $kitaplar->items(),
            'current_page'  => $kitaplar->currentPage(),
            'last_page'     => $kitaplar->lastPage(),
            'per_page'      => $kitaplar->perPage(),
            'total_records' => $kitaplar->total(),
            'from'          => $kitaplar->firstItem() ?? 0,
            'to'            => $kitaplar->lastItem()  ?? 0,
        ]);
    }

    // ─── CSV / Excel Export ──────────────────────────────────────────────────────
    public function export(Request $request)
    {
        $query = Katalog::query();
        if ($request->filled('search')) {
            $s = $request->input('search');
            $query->where(function ($q) use ($s) {
                $q->where('kunyeEserAdi',  'LIKE', "%{$s}%")
                    ->orWhere('kunyeISBNISSN', 'LIKE', "%{$s}%");
            });
        }
        if ($request->filled('kategori'))     $query->where('kunyeKategori', (int) $request->input('kategori'));
        if ($request->filled('siniflamaYer')) $query->where('kunyeSiniflamaYer', 'LIKE', '%' . $request->input('siniflamaYer') . '%');
        if ($request->filled('kutuphaneId'))  $query->where('kutuphaneId', (int) $request->input('kutuphaneId'));
        if ($request->filled('turId'))        $query->where('turId', (int) $request->input('turId'));
        if ($request->filled('durum'))        $query->where('kunyeDurum', $request->input('durum'));
        if ($request->filled('dil'))          $query->where('kunyeDilKN', $request->input('dil'));
        if ($request->filled('konuBasligi'))  $query->where('kunyeKonuBasligi', 'LIKE', '%' . $request->input('konuBasligi') . '%');
        if ($request->filled('ozelNotlar'))   $query->where(function ($q) use ($request) {
            $n = $request->input('ozelNotlar');
            $q->where('ozelNotlar',  'LIKE', "%{$n}%")
                ->orWhere('ozelNotlar2', 'LIKE', "%{$n}%")
                ->orWhere('ozelNotlar3', 'LIKE', "%{$n}%");
        });
        if ($request->filled('oduncVerilebilir')) {
            $query->where('oduncVerilemez', $request->input('oduncVerilebilir') === 'evet' ? 0 : 1);
        }
        if ($request->filled('etiketlendi')) {
            $query->where('etiketlendi', $request->input('etiketlendi') === 'evet' ? 1 : 0);
        }
        if ($request->filled('yazarId'))      $query->where('yazarId', (int) $request->input('yazarId'));
        elseif ($request->filled('yazar'))    $query->where('kunyeYazar', 'LIKE', '%' . $request->input('yazar') . '%');
        if ($request->filled('yayineviId'))   $query->where('yayineviId', (int) $request->input('yayineviId'));
        elseif ($request->filled('yayinevi')) $query->where('kunyeYayinlayan', 'LIKE', '%' . $request->input('yayinevi') . '%');

        $kitaplar    = $query->orderBy('id', 'desc')->get();
        $kategoriMap = Kategori::pluck('title', 'id');
        $filename    = 'katalog_' . now()->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use ($kitaplar, $kategoriMap) {
            $out = fopen('php://output', 'w');
            fputs($out, "\xEF\xBB\xBF");
            fputcsv($out, ['#', 'Demirbaş No', 'Eser Adı', 'Alt Başlık', 'Yazar', 'Yayınlayan',
                'Yayın Yeri', 'Yayın Tarihi', 'ISBN / ISSN', 'Sınıflama / Yer Kodu',
                'Kategori', 'Kopya Sayısı', 'Dil', 'Giriş Tarihi', 'Durum'], ';');
            foreach ($kitaplar as $k) {
                fputcsv($out, [
                    $k->id, $k->kunyeDemirbasKN ?? '—', $k->kunyeEserAdi ?? '—',
                    $k->kunyeEserAdiAlt ?? '—', $k->kunyeYazar ?? '—', $k->kunyeYayinlayan ?? '—',
                    $k->kunyeYayinYeri ?? '—', $k->kunyeYayinTarihi ?? '—', $k->kunyeISBNISSN ?? '—',
                    $k->kunyeSiniflamaYer ?? '—',
                    $k->kunyeKategori ? ($kategoriMap[$k->kunyeKategori] ?? '—') : '—',
                    $k->kunyeKopya ?? 1, $k->kunyeDilKN ?? '—',
                    $k->kunyeGelisTarihi ?? '—', $k->kunyeDurum ?? '—',
                ], ';');
            }
            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    // ─── ISBN Arama ─────────────────────────────────────────────────────────────
    public function isbnSearch(Request $request)
    {
        $isbn = trim($request->input('isbn', ''));
        if (!$isbn) return response()->json(['success' => false, 'message' => 'ISBN boş olamaz.'], 422);
        $isbnClean = preg_replace('/[\s\-]/', '', $isbn);
        $apiKey    = config('services.isbndb.key');
        try {
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'Authorization' => $apiKey, 'Content-Type' => 'application/json',
            ])->get("https://api2.isbndb.com/book/{$isbnClean}");
            if ($response->successful()) {
                $book = $response->json('book');
                if (!$book) return response()->json(['success' => false, 'message' => 'Kitap bulunamadı.']);
                return response()->json([
                    'success'   => true,
                    'title'     => $book['title']     ?? null,
                    'cover'     => $book['image']     ?? null,
                    'publisher' => $book['publisher'] ?? null,
                    'authors'   => isset($book['authors']) ? implode(', ', (array) $book['authors']) : null,
                ]);
            }
            return response()->json(['success' => false, 'message' => 'Kitap bulunamadı veya API yanıt vermedi.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Sorgu hatası: ' . $e->getMessage()], 500);
        }
    }

    // ─── Kapak Resmi Arama ──────────────────────────────────────────────────────
    public function coverSearch(Request $request)
    {
        $isbn = trim($request->input('isbn', ''));
        if (!$isbn) return response()->json(['success' => false, 'message' => 'ISBN boş olamaz.'], 422);
        $isbnClean = preg_replace('/[\s\-]/', '', $isbn);
        $apiKey    = config('services.isbndb.key');
        try {
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'Authorization' => $apiKey, 'Content-Type' => 'application/json',
            ])->get("https://api2.isbndb.com/book/{$isbnClean}");
            if ($response->successful()) {
                $book = $response->json('book');
                if (!$book || empty($book['image']))
                    return response()->json(['success' => false, 'message' => 'Bu ISBN için kapak görseli bulunamadı.']);
                return response()->json(['success' => true, 'cover' => $book['image']]);
            }
            return response()->json(['success' => false, 'message' => 'Kitap bulunamadı veya API yanıt vermedi.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Sorgu hatası: ' . $e->getMessage()], 500);
        }
    }

    // ─── Yardımcı: Sonraki Demirbaş No ──────────────────────────────────────────
    // Format: YYYYMMDD + 4 haneli sıra (örn. 202603150001)
    private function nextDemirbasNo(): string
    {
        $bugun  = now()->format('Ymd');      // örn. "20260315"
        $prefix = $bugun;                    // 8 karakter

        // Bugün girilen en yüksek demirbaşı bul
        $son = Katalog::where('kunyeDemirbasKN', 'LIKE', $prefix . '%')
            ->orderBy('kunyeDemirbasKN', 'desc')
            ->value('kunyeDemirbasKN');

        if ($son) {
            // Sondaki 4 haneli sırayı al ve 1 artır
            $sira = (int) substr($son, strlen($prefix)) + 1;
        } else {
            $sira = 1;
        }

        return $prefix . str_pad($sira, 4, '0', STR_PAD_LEFT);
    }

    // ─── Yeni Form ──────────────────────────────────────────────────────────────
    public function new()
    {
        abort_unless($this->canSaveBooks(), 403);
        $kategoriler  = Kategori::aktif()->orderBy('title')->get();
        $girisTurleri = \App\Models\GirisTuru::where('aktif', 1)->orderBy('sira')->get();
        $allowedIds   = $this->allowedKutuphaneIdsForSave();
        $kutuphaneler = Kutuphane::whereNull('deleted_at')
            ->whereIn('id', $allowedIds ?: [-1])
            ->orderBy('title')->get();
        $yazarlar     = Yazar::orderBy('ad')->get(['id', 'ad']);
        $yayinevleri  = Yayinevi::orderBy('ad')->get(['id', 'ad']);
        $demirbasNo   = $this->nextDemirbasNo();
        $turler       = Tur::aktif()->orderBy('sira')->get(['id', 'ad']);
        $altturler    = AltTur::aktif()->orderBy('sira')->get(['id', 'ad']);
        $sekiller     = Sekil::aktif()->orderBy('sira')->get(['id', 'ad']);
        $ortamlar     = Ortam::aktif()->orderBy('sira')->get(['id', 'ad']);

        return view('book.new', compact(
            'kategoriler', 'girisTurleri', 'kutuphaneler',
            'yazarlar', 'yayinevleri', 'demirbasNo',
            'turler', 'altturler', 'sekiller', 'ortamlar'
        ));
    }

    // ─── Kaydet ─────────────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        abort_unless($this->canSaveBooks(), 403);
        $request->validate([
            'kunyeEserAdi'  => 'required|string|max:500',
            'kunyeYazar'    => 'required|string|max:255',
            'kunyeISBNISSN' => 'required|string|max:50',
        ]);

        $data = $request->only([
            'kunyeDemirbasKN', 'kunyeSiniflamaYer', 'kunyeYayinTarihi',
            'kunyeKopya', 'kunyeCilt', 'kunyeDilKN', 'kunyeDil2', 'kunyeEserAdi',
            'kunyeEserAdiAlt', 'kunyeYazar', 'kunyeSorumlular',
            'kunyeYayinYeri', 'kunyeYayinlayan', 'kunyeFizikselTanim',
            'kunyeISBNISSN', 'kunyeBasimKaydi', 'kunyeDiziKaydi',
            'kunyeKonuBasligi', 'kunyeKategori', 'kunyeGelisTarihi',
            'kunyeDurum',
            'girisTuruId', 'faturaNo', 'faturaTarihi',
            'tedarikci', 'tedarikciTelefon', 'tedarikciEposta', 'fiyat',
            'kutuphaneId',
            // Yeni alanlar
            'turId', 'altTurId', 'sekilId', 'ortamId',
            'icerik', 'aciklama', 'ozelNotlar', 'ozelNotlar2', 'ozelNotlar3', 'ustEserKatalogId',
        ]);

        // ── Checkbox alanları (işaretli değilse form göndermez → 0 olarak kaydet) ─
        $data['oduncVerilemez'] = $request->has('oduncVerilemez') ? 1 : 0;
        $data['etiketlendi']    = $request->has('etiketlendi')    ? 1 : 0;

        // Kaydı oluşturan kullanıcı otomatik atanır
        $data['created_user'] = auth()->id();

        // Kütüphane kaydetme yetkisine göre doğrulama
        $allowedIds = $this->allowedKutuphaneIdsForSave();
        if (!empty($data['kutuphaneId']) && !in_array((int) $data['kutuphaneId'], $allowedIds, true)) {
            return response()->json(['success' => false, 'message' => 'Bu kütüphaneye kayıt yetkiniz yok.'], 403);
        }

        // ── Demirbaş No: gönderilen değer boşsa / elle geçersizse yeniden üret ──
        // DB'ye kayıt sırasında her zaman güncel ve benzersiz numara garantilensin.
        $data['kunyeDemirbasKN'] = $this->nextDemirbasNo();

        // ── Yazar: DB'de bul veya oluştur ─────────────────────────────────────
        $yazarAd = trim($request->input('kunyeYazar', ''));
        if ($yazarAd !== '') {
            $yazar              = Yazar::findOrCreateByAd($yazarAd);
            $data['yazarId']    = $yazar->id;
            $data['kunyeYazar'] = $yazar->ad; // normalize edilmiş
        }

        // ── Yayınevi: DB'de bul veya oluştur ──────────────────────────────────
        $yayineviAd = trim($request->input('kunyeYayinlayan', ''));
        if ($yayineviAd !== '') {
            $yayinevi                = Yayinevi::findOrCreateByAd($yayineviAd);
            $data['yayineviId']      = $yayinevi->id;
            $data['kunyeYayinlayan'] = $yayinevi->ad;
        }

        // ── Giriş türü ────────────────────────────────────────────────────────
        $girisTuruAd = '';
        if (!empty($data['girisTuruId'])) {
            $gt = \App\Models\GirisTuru::find($data['girisTuruId']);
            $girisTuruAd = $gt ? mb_strtolower($gt->ad, 'UTF-8') : '';
        }
        if ($girisTuruAd !== 'satın alma') {
            $data['faturaNo'] = null; $data['faturaTarihi'] = null; $data['fiyat'] = null;
        }
        if (!in_array($girisTuruAd, ['satın alma', 'hibe', 'bağış'])) {
            $data['tedarikci'] = null; $data['tedarikciTelefon'] = null; $data['tedarikciEposta'] = null;
        }

        // ── Kapak ──────────────────────────────────────────────────────────────
        if ($request->hasFile('kunyeKapakResmi')) {
            $data['kunyeKapakResmi'] = $request->file('kunyeKapakResmi')->store('kapaklar', 'public');
        } elseif ($request->filled('isbn_cover_url')) {
            try {
                $imageContents = \Illuminate\Support\Facades\Http::get($request->input('isbn_cover_url'))->body();
                $filename = 'kapaklar/' . uniqid('isbn_') . '.jpg';
                Storage::disk('public')->put($filename, $imageContents);
                $data['kunyeKapakResmi'] = $filename;
            } catch (\Exception $e) {}
        }

        Katalog::create($data);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => '"' . $data['kunyeEserAdi'] . '" başarıyla kütüphaneye eklendi.']);
        }
        return redirect()->route('katalog.index')->with('success', '"' . $data['kunyeEserAdi'] . '" başarıyla kütüphaneye eklendi.');
    }

    // ─── Kopyala Form ───────────────────────────────────────────────────────────
    // Seçilen kitabın tüm verileri new.blade ile aynı copy.blade'e pre-filled olarak aktarılır.
    // Yeni demirbaş no otomatik üretilir.
    public function copy(Katalog $kitap)
    {
        abort_unless($this->canSaveBooks(), 403);
        $kategoriler  = Kategori::aktif()->orderBy('title')->get();
        $girisTurleri = \App\Models\GirisTuru::where('aktif', 1)->orderBy('sira')->get();
        $allowedIds   = $this->allowedKutuphaneIdsForSave();
        $kutuphaneler = Kutuphane::whereNull('deleted_at')
            ->whereIn('id', $allowedIds ?: [-1])
            ->orderBy('title')->get();
        $yazarlar     = Yazar::orderBy('ad')->get(['id', 'ad']);
        $yayinevleri  = Yayinevi::orderBy('ad')->get(['id', 'ad']);
        $demirbasNo   = $this->nextDemirbasNo();
        $turler       = Tur::aktif()->orderBy('sira')->get(['id', 'ad']);
        $altturler    = AltTur::aktif()->orderBy('sira')->get(['id', 'ad']);
        $sekiller     = Sekil::aktif()->orderBy('sira')->get(['id', 'ad']);
        $ortamlar     = Ortam::aktif()->orderBy('sira')->get(['id', 'ad']);

        return view('book.copy', compact(
            'kitap',
            'kategoriler', 'girisTurleri', 'kutuphaneler',
            'yazarlar', 'yayinevleri', 'demirbasNo',
            'turler', 'altturler', 'sekiller', 'ortamlar'
        ));
    }

    // ─── Düzenle Form ───────────────────────────────────────────────────────────
    public function edit(Katalog $kitap)
    {
        abort_unless($this->canUpdateBooks(), 403);
        if (!$this->canListAllBooks()) {
            $ids = auth()->user()->yetkiliKutuphaneIds();
            abort_unless(in_array((int) $kitap->kutuphaneId, $ids, true), 403);
        }
        $kategoriler  = Kategori::aktif()->orderBy('title')->get();
        $girisTurleri = \App\Models\GirisTuru::where('aktif', 1)->orderBy('sira')->get();
        $allowedIds   = $this->allowedKutuphaneIdsForSave();
        $kutuphaneler = Kutuphane::whereNull('deleted_at')
            //->whereIn('id', $allowedIds ?: [-1])
            ->orderBy('title')->get();
        $yazarlar     = Yazar::orderBy('ad')->get(['id', 'ad']);
        $yayinevleri  = Yayinevi::orderBy('ad')->get(['id', 'ad']);
        $turler       = Tur::aktif()->orderBy('sira')->get(['id', 'ad']);
        $altturler    = AltTur::aktif()->orderBy('sira')->get(['id', 'ad']);
        $sekiller     = Sekil::aktif()->orderBy('sira')->get(['id', 'ad']);
        $ortamlar     = Ortam::aktif()->orderBy('sira')->get(['id', 'ad']);

        $createdUser = $kitap->created_user ? \App\Models\User::find($kitap->created_user) : null;
        $updatedUser = $kitap->updated_user ? \App\Models\User::find($kitap->updated_user) : null;

        return view('book.edit', compact(
            'kitap', 'kategoriler', 'girisTurleri', 'kutuphaneler',
            'yazarlar', 'yayinevleri',
            'turler', 'altturler', 'sekiller', 'ortamlar',
            'createdUser', 'updatedUser'
        ));
    }

    // ─── Güncelle ───────────────────────────────────────────────────────────────
    public function update(Request $request, Katalog $kitap)
    {
        abort_unless($this->canUpdateBooks(), 403);
        if (!$this->canListAllBooks()) {
            $ids = auth()->user()->yetkiliKutuphaneIds();
            abort_unless(in_array((int) $kitap->kutuphaneId, $ids, true), 403);
        }
        $request->validate([
            'kunyeEserAdi'  => 'required|string|max:500',
            'kunyeYazar'    => 'required|string|max:255',
            'kunyeISBNISSN' => 'required|string|max:100',
        ]);

        $data = $request->only([
            'kunyeSiniflamaYer', 'kunyeYayinTarihi',
            'kunyeKopya', 'kunyeCilt', 'kunyeDilKN', 'kunyeDil2', 'kunyeEserAdi',
            'kunyeEserAdiAlt', 'kunyeYazar', 'kunyeSorumlular',
            'kunyeYayinYeri', 'kunyeYayinlayan', 'kunyeFizikselTanim',
            'kunyeISBNISSN', 'kunyeBasimKaydi', 'kunyeDiziKaydi',
            'kunyeKonuBasligi', 'kunyeGelisTarihi',
            'kunyeDurum', 'kunyeKategori',
            'girisTuruId', 'faturaNo', 'faturaTarihi',
            'tedarikci', 'tedarikciTelefon', 'tedarikciEposta', 'fiyat',
            'kutuphaneId',
            // Yeni alanlar
            'turId', 'altTurId', 'sekilId', 'ortamId',
            'icerik', 'aciklama', 'ozelNotlar', 'ozelNotlar2', 'ozelNotlar3', 'ustEserKatalogId',
        ]);
        // kunyeDemirbasKN güncellenmez — ne formdan gelirse gelsin yoksayılır

        // ── Checkbox alanları ─────────────────────────────────────────────────
        $data['oduncVerilemez'] = $request->has('oduncVerilemez') ? 1 : 0;
        $data['etiketlendi']    = $request->has('etiketlendi')    ? 1 : 0;

        // ── Güncellemeyi yapan kullanıcı otomatik atanır ───────────────────────
        $data['updated_user'] = auth()->id();

        // Kütüphane değişikliği yapılacaksa "kayıt" yetkisi gerekir (3/6)
        $incomingKutuphaneId = array_key_exists('kutuphaneId', $data) ? (int) $data['kutuphaneId'] : (int) $kitap->kutuphaneId;
        $changingKutuphane   = $incomingKutuphaneId !== (int) $kitap->kutuphaneId;
        if ($changingKutuphane && !$this->canSaveBooks()) {
            return response()->json(['success' => false, 'message' => 'Kütüphane değişikliği için kayıt yetkiniz yok.'], 403);
        }

        if ($this->canUpdateBooks()) {
            $allowedIds = $this->allowedKutuphaneIdsForUpdate();
            if (!empty($incomingKutuphaneId) && !in_array($incomingKutuphaneId, $allowedIds, true)) {
                return response()->json(['success' => false, 'message' => 'Bu kütüphaneye kayıt/güncelleme yetkiniz yok.'], 403);
            }
        } else {
            // Kayıt yetkisi yoksa güvenli tarafta kal: kutuphaneId alanını yok say
            unset($data['kutuphaneId']);
        }

        // ── Yazar ─────────────────────────────────────────────────────────────
        $yazarAd = trim($request->input('kunyeYazar', ''));
        if ($yazarAd !== '') {
            $yazar              = Yazar::findOrCreateByAd($yazarAd);
            $data['yazarId']    = $yazar->id;
            $data['kunyeYazar'] = $yazar->ad;
        } else {
            $data['yazarId'] = null;
        }

        // ── Yayınevi ──────────────────────────────────────────────────────────
        $yayineviAd = trim($request->input('kunyeYayinlayan', ''));
        if ($yayineviAd !== '') {
            $yayinevi                = Yayinevi::findOrCreateByAd($yayineviAd);
            $data['yayineviId']      = $yayinevi->id;
            $data['kunyeYayinlayan'] = $yayinevi->ad;
        } else {
            $data['yayineviId'] = null;
        }

        // ── Giriş türü ────────────────────────────────────────────────────────
        $girisTuruAd = '';
        if (!empty($data['girisTuruId'])) {
            $gt = \App\Models\GirisTuru::find($data['girisTuruId']);
            $girisTuruAd = $gt ? mb_strtolower($gt->ad, 'UTF-8') : '';
        }
        if ($girisTuruAd !== 'satın alma') {
            $data['faturaNo'] = null; $data['faturaTarihi'] = null; $data['fiyat'] = null;
        }
        if (!in_array($girisTuruAd, ['satın alma', 'hibe', 'bağış'])) {
            $data['tedarikci'] = null; $data['tedarikciTelefon'] = null; $data['tedarikciEposta'] = null;
        }

        // ── Kapak ──────────────────────────────────────────────────────────────
        if ($request->hasFile('kunyeKapakResmi')) {
            if ($kitap->kunyeKapakResmi) Storage::disk('public')->delete($kitap->kunyeKapakResmi);
            $data['kunyeKapakResmi'] = $request->file('kunyeKapakResmi')->store('kapaklar', 'public');
        } elseif ($request->filled('isbn_cover_url')) {
            try {
                if ($kitap->kunyeKapakResmi) Storage::disk('public')->delete($kitap->kunyeKapakResmi);
                $imageContents = \Illuminate\Support\Facades\Http::get($request->input('isbn_cover_url'))->body();
                $filename = 'kapaklar/' . uniqid('isbn_') . '.jpg';
                Storage::disk('public')->put($filename, $imageContents);
                $data['kunyeKapakResmi'] = $filename;
            } catch (\Exception $e) {}
        } elseif ($request->input('kapak_sil') === '1') {
            if ($kitap->kunyeKapakResmi) Storage::disk('public')->delete($kitap->kunyeKapakResmi);
            $data['kunyeKapakResmi'] = null;
        }

        $kitap->update($data);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => '"' . $data['kunyeEserAdi'] . '" başarıyla güncellendi.']);
        }
        return redirect()->route('katalog.index')->with('success', '"' . $data['kunyeEserAdi'] . '" başarıyla güncellendi.');
    }

}
