<?php

namespace App\Http\Controllers;

use App\Models\Katalog;
use App\Models\Koleksiyon;
use App\Models\Kategori;
use App\Models\Kutuphane;
use App\Models\Yazar;
use App\Models\Yayinevi;
use App\Models\Tur;
use App\Models\AltTur;
use App\Models\Sekil;
use App\Models\Ortam;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

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

    private function normalizeKoleksiyonIdInput(Request $request): void
    {
        $raw = $request->input('koleksiyon_id');
        $request->merge([
            'koleksiyon_id' => ($raw === '' || $raw === null) ? null : (int) $raw,
        ]);
    }

    /**
     * @return array<int, \Illuminate\Contracts\Validation\ValidationRule|string>
     */
    private function koleksiyonIdValidationRule(): array
    {
        return [
            'nullable',
            'integer',
            Rule::exists('koleksiyon', 'id')->where(function ($q) {
                $q->where('statu', 'aktif')->whereNull('deleted_at');
            }),
        ];
    }

    /**
     * Katalog sorgusunu kullanıcı yetkisine göre daraltır.
     */
    private function scopeKatalogForUser($query, $requestUser): void
    {
        if (!$requestUser) {
            $query->whereRaw('1=0');
            return;
        }
        if ($requestUser->hasYetki(4) || $requestUser->hasYetki(5)) {
            return;
        }
        if ($requestUser->hasYetki(1) || $requestUser->hasYetki(2)) {
            $ids = $requestUser->yetkiliKutuphaneIds();
            $query->whereIn('kutuphaneId', $ids ?: [-1]);
            return;
        }
        $query->whereRaw('1=0');
    }

    /**
     * Katalog liste ekranındaki filtreleri sorguya uygular.
     */
    private function applyListFiltersToKatalogQuery($query, Request $request): void
    {
        if ($request->filled('search')) {
            $s = $request->input('search');
            $query->where(function ($q) use ($s) {
                $q->where('kunyeEserAdi',  'LIKE', "%{$s}%")
                    ->orWhere('kunyeISBNISSN', 'LIKE', "%{$s}%")
                    ->orWhere('kunyeDemirbasKN', 'LIKE', "%{$s}%");
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
        if ($request->filled('kayitBaslangic')) {
            $kayitBaslangic = $request->input('kayitBaslangic');
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $kayitBaslangic)) {
                $query->whereDate('created_at', '>=', $kayitBaslangic);
            }
        }
        if ($request->filled('kayitBitis')) {
            $kayitBitis = $request->input('kayitBitis');
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $kayitBitis)) {
                $query->whereDate('created_at', '<=', $kayitBitis);
            }
        }
        if ($request->filled('yazarId')) {
            $this->applyYazarIdFilter($query, (int) $request->input('yazarId'));
        } elseif ($request->filled('yazar')) {
            $query->where('kunyeYazar', 'LIKE', '%' . $request->input('yazar') . '%');
        }
        if ($request->filled('yayineviId')) {
            $query->where('yayineviId', (int) $request->input('yayineviId'));
        } elseif ($request->filled('yayinevi')) {
            $query->where('kunyeYayinlayan', 'LIKE', '%' . $request->input('yayinevi') . '%');
        }
    }

    /**
     * @return list<int>
     */
    private function normalizeYazarIdsFromRequest(Request $request): array
    {
        $raw = $request->input('yazar_ids');
        if (!is_array($raw)) {
            return [];
        }
        $out = [];
        foreach ($raw as $v) {
            $id = (int) $v;
            if ($id > 0 && !in_array($id, $out, true)) {
                $out[] = $id;
            }
        }

        return $out;
    }

    /**
     * @param list<int> $orderedYazarIds
     */
    private function syncKatalogYazarlar(Katalog $katalog, array $orderedYazarIds): void
    {
        $sync = [];
        foreach (array_values($orderedYazarIds) as $i => $id) {
            $sync[$id] = ['sira' => $i];
        }
        $katalog->yazarlar()->sync($sync);
    }

    private function applyYazarIdFilter($query, int $yazarId): void
    {
        $query->where(function ($q) use ($yazarId) {
            $q->where('yazarId', $yazarId)
                ->orWhereHas('yazarlar', function ($q2) use ($yazarId) {
                    $q2->where('yazarlar.id', $yazarId);
                });
        });
    }

    /**
     * Kayıtlı / manuel yazar girişine göre sıralı yazar id listesi ve kunyeYazar alanını üretir.
     *
     * @param  array<string, mixed>  $data  katalog mass-assign verisi (kunyeYazar güncellenir)
     * @return list<int>
     */
    private function resolveOrderedYazarIdsForSave(Request $request, array &$data): array
    {
        $tip = $request->input('yazar_giris_tipi', 'kayitli');

        if ($tip === 'manuel') {
            $ads = (array) $request->input('yazar_manuel_ad', []);
            $soyads = (array) $request->input('yazar_manuel_soyad', []);
            $ordered = [];
            $names = [];
            $n = max(count($ads), count($soyads));
            for ($i = 0; $i < $n; $i++) {
                $ad = isset($ads[$i]) ? trim((string) $ads[$i]) : '';
                $soyad = isset($soyads[$i]) ? trim((string) $soyads[$i]) : '';
                if ($ad === '' && $soyad === '') {
                    continue;
                }
                if ($ad === '') {
                    continue;
                }
                $y = Yazar::findOrCreateByAdSoyad($ad, $soyad);
                $ordered[] = $y->id;
                $names[] = $y->tam_ad;
            }
            $data['kunyeYazar'] = $names !== [] ? implode(' ; ', $names) : null;

            return $ordered;
        }

        $ordered = $this->normalizeYazarIdsFromRequest($request);
        if ($ordered !== []) {
            $data['kunyeYazar'] = Yazar::whereIn('id', $ordered)->get()
                ->sortBy(fn ($y) => array_search($y->id, $ordered, true))
                ->pluck('tam_ad')
                ->implode(' ; ');
        } else {
            $data['kunyeYazar'] = null;
        }

        return $ordered;
    }

    // ─── Liste ──────────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $bookcount = Katalog::count();

        if (!$request->ajax() && !$request->wantsJson()) {
            $kategoriler  = Kategori::aktif()->get(['id', 'title']);
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
            // Sadece en az bir kitabı olan yazarları getir (pivot + eski yazarId)
            $pivotYazarIds = DB::table('katalog_yazarlar')->distinct()->pluck('yazar_id');
            $legacyYazarIds = Katalog::whereNotNull('yazarId')->distinct()->pluck('yazarId');
            $yazarIds      = $pivotYazarIds->merge($legacyYazarIds)->unique()->values();
            $yazarlar      = Yazar::whereIn('id', $yazarIds->isEmpty() ? [-1] : $yazarIds->all())
                ->orderBy('siralama_adi')->orderBy('ad')->orderBy('soyad')->get(['id', 'ad', 'soyad']);
            // Sadece en az bir kitabı olan yayınevlerini getir
            $yayineviIds = Katalog::whereNotNull('yayineviId')->distinct()->pluck('yayineviId');
            $yayinevleri = Yayinevi::whereIn('id', $yayineviIds)->orderBy('ad')->get(['id', 'ad']);
            $turler      = \App\Models\Tur::aktif()->orderBy('sira')->get(['id', 'ad']);
            return view('book.list', compact('bookcount', 'kategoriler', 'kutuphaneler', 'yazarlar', 'yayinevleri', 'turler'));
        }

        $perPage = in_array((int) $request->input('per_page'), [10, 20, 50, 100, 500])
            ? (int) $request->input('per_page') : 20;

        abort_unless($this->canListAllBooks() || $this->canListScopedBooks(), 403);

        $query = Katalog::query()->with([
            'yazarlar:id,ad,soyad',
        ]);

        if (!$this->canListAllBooks()) {
            $ids = auth()->user()->yetkiliKutuphaneIds();
            $query->whereIn('kutuphaneId', $ids ?: [-1]);
        }

        if ($request->filled('search')) {
            $s = $request->input('search');
            $query->where(function ($q) use ($s) {
                $q->where('kunyeEserAdi',  'LIKE', "%{$s}%")
                    ->orWhere('kunyeISBNISSN', 'LIKE', "%{$s}%")
                    ->orWhere('kunyeDemirbasKN', 'LIKE', "%{$s}%");
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
        if ($request->filled('kayitBaslangic')) {
            $kayitBaslangic = $request->input('kayitBaslangic');
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $kayitBaslangic)) {
                $query->whereDate('created_at', '>=', $kayitBaslangic);
            }
        }
        if ($request->filled('kayitBitis')) {
            $kayitBitis = $request->input('kayitBitis');
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $kayitBitis)) {
                $query->whereDate('created_at', '<=', $kayitBitis);
            }
        }

        // Yazar filtresi: önce ID ile dene (dropdown), yoksa metin LIKE
        if ($request->filled('yazarId')) {
            $this->applyYazarIdFilter($query, (int) $request->input('yazarId'));
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
        $rows = collect($kitaplar->items())->map(function (Katalog $kitap) {
            $row = $kitap->toArray();
            $row['kunyeKapakResmi'] = $kitap->kapak_resim_path;

            return $row;
        })->all();

        return response()->json([
            'rows'          => $rows,
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
        if ($request->filled('kayitBaslangic')) {
            $kayitBaslangic = $request->input('kayitBaslangic');
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $kayitBaslangic)) {
                $query->whereDate('created_at', '>=', $kayitBaslangic);
            }
        }
        if ($request->filled('kayitBitis')) {
            $kayitBitis = $request->input('kayitBitis');
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $kayitBitis)) {
                $query->whereDate('created_at', '<=', $kayitBitis);
            }
        }
        if ($request->filled('yazarId')) {
            $this->applyYazarIdFilter($query, (int) $request->input('yazarId'));
        } elseif ($request->filled('yazar')) {
            $query->where('kunyeYazar', 'LIKE', '%' . $request->input('yazar') . '%');
        }
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

    /**
     * ISBN karşılaştırması için yalnızca rakam ve ISBN-10 kontrol hanesi X.
     */
    private function normalizeIsbnForMatch(string $isbn): string
    {
        return strtoupper(preg_replace('/[^0-9X]/i', '', $isbn));
    }

    /**
     * Veritabanında aynı ISBN’ye sahip katalog kaydı (en güncel id).
     */
    private function findKatalogByNormalizedIsbn(string $normalized): ?Katalog
    {
        if ($normalized === '') {
            return null;
        }

        $driver = DB::connection()->getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            try {
                return Katalog::query()
                    ->whereRaw(
                        "REGEXP_REPLACE(UPPER(TRIM(COALESCE(kunyeISBNISSN,''))), '[^0-9X]', '') = ?",
                        [$normalized]
                    )
                    ->orderByDesc('id')
                    ->first();
            } catch (\Throwable) {
                // MySQL 8 öncesi vb.: aşağıdaki tarama yoluna düş
            }
        }

        foreach (Katalog::query()
            ->whereNotNull('kunyeISBNISSN')
            ->where('kunyeISBNISSN', '!=', '')
            ->orderByDesc('id')
            ->cursor() as $row) {
            if ($this->normalizeIsbnForMatch((string) $row->kunyeISBNISSN) === $normalized) {
                return $row;
            }
        }

        return null;
    }

    /**
     * ISBN'den bulunan mevcut kaydın forma uygulanacak alanları.
     * GirisTuru, Durum, Kutuphane, OduncVerilemez ve Etiketlendi hariçtir.
     */
    private function buildIsbnPrefillData(Katalog $katalog): array
    {
        $fields = [
            'kunyeDemirbasKN', 'kunyeSiniflamaYer', 'kunyeYayinTarihi',
            'kunyeKopya', 'kunyeCilt', 'kunyeDilKN', 'kunyeDil2', 'kunyeEserAdi',
            'kunyeEserAdiAlt', 'kunyeYazar', 'kunyeSorumlular',
            'kunyeYayinYeri', 'kunyeYayinlayan', 'kunyeFizikselTanim',
            'kunyeSayfaSayisi',
            'kunyeISBNISSN', 'kunyeBasimKaydi', 'kunyeDiziKaydi',
            'kunyeKonuBasligi', 'kunyeKategori', 'kunyeGelisTarihi',
            'faturaNo', 'faturaTarihi', 'tedarikci', 'tedarikciTelefon',
            'tedarikciEposta', 'fiyat',
            'yazarId', 'yayineviId',
            'turId', 'altTurId', 'sekilId', 'ortamId',
            'icerik', 'aciklama', 'ozelNotlar', 'ozelNotlar2', 'ozelNotlar3',
            'ustEserKatalogId',
            'koleksiyon_id',
        ];

        $prefill = [];
        foreach ($fields as $field) {
            $prefill[$field] = $katalog->{$field};
        }

        $yRel = $katalog->yazarlar()->getRelated();
        $prefill['yazar_ids'] = $katalog->yazarlar()
            ->orderByPivot('sira')
            ->pluck($yRel->getQualifiedKeyName())
            ->values()
            ->all();

        return $prefill;
    }

    // ─── ISBN Arama ─────────────────────────────────────────────────────────────
    public function isbnSearch(Request $request)
    {
        $isbn = trim($request->input('isbn', ''));
        if (!$isbn) {
            return response()->json(['success' => false, 'message' => 'ISBN boş olamaz.'], 422);
        }

        $normalized = $this->normalizeIsbnForMatch($isbn);
        if ($normalized === '') {
            return response()->json(['success' => false, 'message' => 'ISBN geçersiz.'], 422);
        }

        $existing = $this->findKatalogByNormalizedIsbn($normalized);
        if ($existing) {
            $coverPath = $existing->kunyeKapakResmi;

            return response()->json([
                'success'          => true,
                'source'           => 'database',
                'title'            => $existing->kunyeEserAdi ?: null,
                'cover'            => $coverPath,
                'coverPreviewUrl'  => $coverPath ? asset('storage/' . $coverPath) : null,
                'sourceKatalogId'  => (int) $existing->id,
                'publisher'        => $existing->kunyeYayinlayan ?: null,
                'authors'          => $existing->kunyeYazar ?: null,
                'prefill'          => $this->buildIsbnPrefillData($existing),
            ]);
        }

        $isbnClean = preg_replace('/[\s\-]/', '', $isbn);
        $apiKey    = config('services.isbndb.key');
        try {
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'Authorization' => $apiKey, 'Content-Type' => 'application/json',
            ])->get("https://api2.isbndb.com/book/{$isbnClean}");
            if ($response->successful()) {
                $book = $response->json('book');
                if (!$book) {
                    return response()->json(['success' => false, 'message' => 'Kitap bulunamadı.']);
                }

                return response()->json([
                    'success'   => true,
                    'source'    => 'api',
                    'title'     => $book['title'] ?? null,
                    'cover'     => $book['image'] ?? null,
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
        if (!$isbn) {
            return response()->json(['success' => false, 'message' => 'ISBN boş olamaz.'], 422);
        }

        $normalized = $this->normalizeIsbnForMatch($isbn);
        if ($normalized === '') {
            return response()->json(['success' => false, 'message' => 'ISBN geçersiz.'], 422);
        }

        $existing = $this->findKatalogByNormalizedIsbn($normalized);
        if ($existing && $existing->kapak_resim_path) {
            return response()->json([
                'success' => true,
                'source'  => 'database',
                'cover'   => $existing->kapak_resim_path,
            ]);
        }

        $isbnClean = preg_replace('/[\s\-]/', '', $isbn);
        $apiKey    = config('services.isbndb.key');
        try {
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'Authorization' => $apiKey, 'Content-Type' => 'application/json',
            ])->get("https://api2.isbndb.com/book/{$isbnClean}");
            if ($response->successful()) {
                $book = $response->json('book');
                if (!$book || empty($book['image'])) {
                    return response()->json(['success' => false, 'message' => 'Bu ISBN için kapak görseli bulunamadı.']);
                }

                return response()->json([
                    'success' => true,
                    'source'  => 'api',
                    'cover'   => $book['image'],
                ]);
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
        $bugun  = now()->format('ym');      // örn. "202603"
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

        return $prefix . str_pad($sira, 5, '0', STR_PAD_LEFT);
    }

    // ─── Yeni Form ──────────────────────────────────────────────────────────────
    public function new()
    {
        abort_unless($this->canSaveBooks(), 403);
        $kategoriler  = Kategori::aktif()->get();
        $girisTurleri = \App\Models\GirisTuru::where('aktif', 1)->orderBy('sira')->get();
        $allowedIds   = $this->allowedKutuphaneIdsForSave();
        $kutuphaneler = Kutuphane::whereNull('deleted_at')
            ->whereIn('id', $allowedIds ?: [-1])
            ->orderBy('title')->get();
        $yazarlar     = Yazar::orderBy('siralama_adi')->orderBy('ad')->orderBy('soyad')->get(['id', 'ad', 'soyad']);
        $yayinevleri  = Yayinevi::orderBy('ad')->get(['id', 'ad']);
        $demirbasNo   = $this->nextDemirbasNo();
        $turler       = Tur::aktif()->orderBy('sira')->get(['id', 'ad']);
        $altturler    = AltTur::aktif()->orderBy('sira')->get(['id', 'ad']);
        $sekiller     = Sekil::aktif()->orderBy('sira')->get(['id', 'ad']);
        $ortamlar     = Ortam::aktif()->orderBy('sira')->get(['id', 'ad']);
        $koleksiyonlar = Koleksiyon::aktif()->orderBy('title')->get();
        $yayinYeriOneriler = Katalog::query()
            ->select('kunyeYayinYeri', DB::raw('MAX(id) as last_id'))
            ->whereNotNull('kunyeYayinYeri')
            ->where('kunyeYayinYeri', '!=', '')
            ->groupBy('kunyeYayinYeri')
            ->orderByDesc('last_id')
            ->limit(5)
            ->pluck('kunyeYayinYeri');
        $sorumluOneriler = Katalog::query()
            ->select('kunyeSorumlular', DB::raw('MAX(id) as last_id'))
            ->whereNotNull('kunyeSorumlular')
            ->where('kunyeSorumlular', '!=', '')
            ->groupBy('kunyeSorumlular')
            ->orderByDesc('last_id')
            ->limit(5)
            ->pluck('kunyeSorumlular');
        $diziKaydiOneriler = Katalog::query()
            ->select('kunyeDiziKaydi', DB::raw('MAX(id) as last_id'))
            ->whereNotNull('kunyeDiziKaydi')
            ->where('kunyeDiziKaydi', '!=', '')
            ->groupBy('kunyeDiziKaydi')
            ->orderByDesc('last_id')
            ->limit(5)
            ->pluck('kunyeDiziKaydi');

        return view('book.new', compact(
            'kategoriler', 'girisTurleri', 'kutuphaneler',
            'yazarlar', 'yayinevleri', 'demirbasNo',
            'turler', 'altturler', 'sekiller', 'ortamlar', 'koleksiyonlar',
            'yayinYeriOneriler', 'sorumluOneriler', 'diziKaydiOneriler'
        ));
    }

    // ─── Kaydet ─────────────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        abort_unless($this->canSaveBooks(), 403);

        $this->normalizeKoleksiyonIdInput($request);

        $allowedKutuphaneIds = $this->allowedKutuphaneIdsForSave();
        $request->validate([
            'kunyeEserAdi'  => 'required|string|max:500',
            'kunyeYazar'    => 'nullable|string|max:500',
            'kunyeISBNISSN' => 'nullable|string|max:50',
            'kunyeSayfaSayisi' => 'nullable|integer|min:1',
            'kutuphaneId'   => ['required', 'integer', Rule::in($allowedKutuphaneIds)],
            'koleksiyon_id' => $this->koleksiyonIdValidationRule(),
            'yazar_giris_tipi' => 'nullable|in:kayitli,manuel',
            'yazar_ids'        => 'nullable|array',
            'yazar_ids.*'      => 'integer|exists:yazarlar,id',
            'yazar_manuel_ad'   => 'nullable|array',
            'yazar_manuel_ad.*' => 'nullable|string|max:255',
            'yazar_manuel_soyad'   => 'nullable|array',
            'yazar_manuel_soyad.*' => 'nullable|string|max:255',
        ], [
            'kutuphaneId.required' => 'Lütfen bir kütüphane seçin.',
            'kutuphaneId.in'       => 'Seçilen kütüphane geçerli değil veya bu kütüphaneye kayıt yetkiniz yok.',
        ]);

        $data = $request->only([
            'kunyeDemirbasKN', 'kunyeSiniflamaYer', 'kunyeYayinTarihi',
            'kunyeKopya', 'kunyeCilt', 'kunyeDilKN', 'kunyeDil2', 'kunyeEserAdi',
            'kunyeEserAdiAlt', 'kunyeYazar', 'kunyeSorumlular',
            'kunyeYayinYeri', 'kunyeYayinlayan', 'kunyeFizikselTanim',
            'kunyeSayfaSayisi',
            'kunyeISBNISSN', 'kunyeBasimKaydi', 'kunyeDiziKaydi',
            'kunyeKonuBasligi', 'kunyeKategori', 'kunyeGelisTarihi',
            'kunyeDurum',
            'girisTuruId', 'faturaNo', 'faturaTarihi',
            'tedarikci', 'tedarikciTelefon', 'tedarikciEposta', 'fiyat',
            'kutuphaneId',
            // Yeni alanlar
            'turId', 'altTurId', 'sekilId', 'ortamId',
            'icerik', 'aciklama', 'ozelNotlar', 'ozelNotlar2', 'ozelNotlar3', 'ustEserKatalogId',
            'koleksiyon_id',
        ]);

        // ── Checkbox alanları (işaretli değilse form göndermez → 0 olarak kaydet) ─
        $data['oduncVerilemez'] = $request->has('oduncVerilemez') ? 1 : 0;
        $data['etiketlendi']    = $request->has('etiketlendi')    ? 1 : 0;

        // Kaydı oluşturan kullanıcı otomatik atanır
        $data['created_user'] = auth()->id();

        // ── Demirbaş No: gönderilen değer boşsa / elle geçersizse yeniden üret ──
        // DB'ye kayıt sırasında her zaman güncel ve benzersiz numara garantilensin.
        $data['kunyeDemirbasKN'] = $this->nextDemirbasNo();

        // ── Yazar: kayıtlı (yazar_ids[]) veya manuel (ad/soyad dizileri) ───────────
        $orderedYazarIds = $this->resolveOrderedYazarIdsForSave($request, $data);
        $data['yazarId'] = $orderedYazarIds[0] ?? null;

        // ── Alt Tür: serbest metinden DB'de bul/oluştur ─────────────────────
        $altTurAd = trim($request->input('altTurAd', ''));
        if ($altTurAd !== '') {
            $altTur = AltTur::findOrCreateByAd($altTurAd);
            $data['altTurId'] = $altTur->id;
        } else {
            $data['altTurId'] = null;
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
        } elseif ($request->filled('copy_kapak_from_katalog_id')) {
            $copied = $this->duplicateKunyeKapakFromKatalog((int) $request->input('copy_kapak_from_katalog_id'));
            if ($copied !== null) {
                $data['kunyeKapakResmi'] = $copied;
            }
        }

        $katalog = Katalog::create($data);
        $this->syncKatalogYazarlar($katalog, $orderedYazarIds);

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
        $kategoriler  = Kategori::aktif()->get();
        $girisTurleri = \App\Models\GirisTuru::where('aktif', 1)->orderBy('sira')->get();
        $allowedIds   = $this->allowedKutuphaneIdsForSave();
        $kutuphaneler = Kutuphane::whereNull('deleted_at')
            ->whereIn('id', $allowedIds ?: [-1])
            ->orderBy('title')->get();
        $yazarlar     = Yazar::orderBy('siralama_adi')->orderBy('ad')->orderBy('soyad')->get(['id', 'ad', 'soyad']);
        $yayinevleri  = Yayinevi::orderBy('ad')->get(['id', 'ad']);
        $demirbasNo   = $this->nextDemirbasNo();
        $turler       = Tur::aktif()->orderBy('sira')->get(['id', 'ad']);
        $altturler    = AltTur::aktif()->orderBy('sira')->get(['id', 'ad']);
        $sekiller     = Sekil::aktif()->orderBy('sira')->get(['id', 'ad']);
        $ortamlar     = Ortam::aktif()->orderBy('sira')->get(['id', 'ad']);
        $koleksiyonlar = Koleksiyon::aktif()->orderBy('title')->get();
        $copyPrefill  = $this->buildIsbnPrefillData($kitap);
        unset($copyPrefill['kunyeDemirbasKN']);
        $copyKapakFromKatalogId = $kitap->id;
        $copyCoverPreview = $kitap->kapak_resim_path;

        return view('book.copy', compact(
            'kitap',
            'kategoriler', 'girisTurleri', 'kutuphaneler',
            'yazarlar', 'yayinevleri', 'demirbasNo',
            'turler', 'altturler', 'sekiller', 'ortamlar', 'koleksiyonlar',
            'copyPrefill', 'copyKapakFromKatalogId', 'copyCoverPreview'
        ));
    }

    // ─── Düzenle Form ───────────────────────────────────────────────────────────
    public function view(Request $request, Katalog $kitap)
    {
        abort_unless($this->canListAllBooks() || $this->canListScopedBooks(), 403);
        if (!$this->canListAllBooks()) {
            $ids = auth()->user()->yetkiliKutuphaneIds();
            abort_unless(in_array((int) $kitap->kutuphaneId, $ids, true), 403);
        }

        $kategoriler  = Kategori::aktif()->get();
        $girisTurleri = \App\Models\GirisTuru::where('aktif', 1)->orderBy('sira')->get();
        $kutuphaneler = Kutuphane::whereNull('deleted_at')
            ->orderBy('title')->get();
        $yazarlar     = Yazar::orderBy('siralama_adi')->orderBy('ad')->orderBy('soyad')->get(['id', 'ad', 'soyad']);
        $yayinevleri  = Yayinevi::orderBy('ad')->get(['id', 'ad']);
        $turler       = Tur::aktif()->orderBy('sira')->get(['id', 'ad']);
        $altturler    = AltTur::aktif()->orderBy('sira')->get(['id', 'ad']);
        $sekiller     = Sekil::aktif()->orderBy('sira')->get(['id', 'ad']);
        $ortamlar     = Ortam::aktif()->orderBy('sira')->get(['id', 'ad']);
        $koleksiyonlar = Koleksiyon::aktif()->orderBy('title')->get();

        $createdUser = $kitap->created_user ? \App\Models\User::find($kitap->created_user) : null;
        $updatedUser = $kitap->updated_user ? \App\Models\User::find($kitap->updated_user) : null;
        $navBase = Katalog::query()->select('id');
        $this->scopeKatalogForUser($navBase, auth()->user());
        $this->applyListFiltersToKatalogQuery($navBase, $request);

        // Liste sırası id desc olduğundan:
        // "Önceki" = mevcut kaydın üstündeki (daha büyük id), "Sonraki" = alttaki (daha küçük id).
        $prevKatalogId = (clone $navBase)
            ->where('id', '>', $kitap->id)
            ->orderBy('id')
            ->value('id');
        $nextKatalogId = (clone $navBase)
            ->where('id', '<', $kitap->id)
            ->orderByDesc('id')
            ->value('id');

        $kitap->loadMissing(['yazarlar' => fn ($q) => $q->orderByPivot('sira')]);

        return view('book.view', compact(
            'kitap', 'kategoriler', 'girisTurleri', 'kutuphaneler',
            'yazarlar', 'yayinevleri',
            'turler', 'altturler', 'sekiller', 'ortamlar', 'koleksiyonlar',
            'createdUser', 'updatedUser', 'prevKatalogId', 'nextKatalogId'
        ));
    }

    // ─── Düzenle Form ───────────────────────────────────────────────────────────
    public function edit(Request $request, Katalog $kitap)
    {
        abort_unless($this->canUpdateBooks(), 403);
        if (!$this->canListAllBooks()) {
            $ids = auth()->user()->yetkiliKutuphaneIds();
            abort_unless(in_array((int) $kitap->kutuphaneId, $ids, true), 403);
        }
        $kategoriler  = Kategori::aktif()->get();
        $girisTurleri = \App\Models\GirisTuru::where('aktif', 1)->orderBy('sira')->get();
        $allowedIds   = $this->allowedKutuphaneIdsForSave();
        $kutuphaneler = Kutuphane::whereNull('deleted_at')
            //->whereIn('id', $allowedIds ?: [-1])
            ->orderBy('title')->get();
        $yazarlar     = Yazar::orderBy('siralama_adi')->orderBy('ad')->orderBy('soyad')->get(['id', 'ad', 'soyad']);
        $yayinevleri  = Yayinevi::orderBy('ad')->get(['id', 'ad']);
        $turler       = Tur::aktif()->orderBy('sira')->get(['id', 'ad']);
        $altturler    = AltTur::aktif()->orderBy('sira')->get(['id', 'ad']);
        $sekiller     = Sekil::aktif()->orderBy('sira')->get(['id', 'ad']);
        $ortamlar     = Ortam::aktif()->orderBy('sira')->get(['id', 'ad']);
        $koleksiyonlar = Koleksiyon::aktif()->orderBy('title')->get();

        $createdUser = $kitap->created_user ? \App\Models\User::find($kitap->created_user) : null;
        $updatedUser = $kitap->updated_user ? \App\Models\User::find($kitap->updated_user) : null;
        $navBase = Katalog::query()->select('id');
        $this->scopeKatalogForUser($navBase, auth()->user());
        $this->applyListFiltersToKatalogQuery($navBase, $request);
        $prevKatalogId = (clone $navBase)
            ->where('id', '>', $kitap->id)
            ->orderBy('id')
            ->value('id');
        $nextKatalogId = (clone $navBase)
            ->where('id', '<', $kitap->id)
            ->orderByDesc('id')
            ->value('id');

        $kitap->loadMissing(['yazarlar' => fn ($q) => $q->orderByPivot('sira')]);

        return view('book.edit', compact(
            'kitap', 'kategoriler', 'girisTurleri', 'kutuphaneler',
            'yazarlar', 'yayinevleri',
            'turler', 'altturler', 'sekiller', 'ortamlar', 'koleksiyonlar',
            'createdUser', 'updatedUser', 'prevKatalogId', 'nextKatalogId'
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
        $durumKilitli = in_array($kitap->kunyeDurum, ['Ödünç', 'Rezerve'], true);

        $this->normalizeKoleksiyonIdInput($request);

        $validateRules = [
            'kunyeEserAdi'  => 'required|string|max:500',
            'kunyeYazar'    => 'nullable|string|max:500',
            'kunyeISBNISSN' => 'nullable|string|max:100',
            'kunyeSayfaSayisi' => 'nullable|integer|min:1',
            'koleksiyon_id' => $this->koleksiyonIdValidationRule(),
            'yazar_giris_tipi' => 'nullable|in:kayitli,manuel',
            'yazar_ids'        => 'nullable|array',
            'yazar_ids.*'      => 'integer|exists:yazarlar,id',
            'yazar_manuel_ad'   => 'nullable|array',
            'yazar_manuel_ad.*' => 'nullable|string|max:255',
            'yazar_manuel_soyad'   => 'nullable|array',
            'yazar_manuel_soyad.*' => 'nullable|string|max:255',
        ];
        if (!$durumKilitli) {
            $validateRules['kunyeDurum'] = ['required', Rule::in(['Rafta', 'Kayıp', 'Bakımda', 'Hurdaya Ayrıldı'])];
        }
        $request->validate($validateRules);

        $data = $request->only([
            'kunyeSiniflamaYer', 'kunyeYayinTarihi',
            'kunyeKopya', 'kunyeCilt', 'kunyeDilKN', 'kunyeDil2', 'kunyeEserAdi',
            'kunyeEserAdiAlt', 'kunyeYazar', 'kunyeSorumlular',
            'kunyeYayinYeri', 'kunyeYayinlayan', 'kunyeFizikselTanim',
            'kunyeSayfaSayisi',
            'kunyeISBNISSN', 'kunyeBasimKaydi', 'kunyeDiziKaydi',
            'kunyeKonuBasligi', 'kunyeGelisTarihi',
            'kunyeKategori',
            'girisTuruId', 'faturaNo', 'faturaTarihi',
            'tedarikci', 'tedarikciTelefon', 'tedarikciEposta', 'fiyat',
            'kutuphaneId',
            // Yeni alanlar
            'turId', 'altTurId', 'sekilId', 'ortamId',
            'icerik', 'aciklama', 'ozelNotlar', 'ozelNotlar2', 'ozelNotlar3', 'ustEserKatalogId',
            'koleksiyon_id',
        ]);
        // kunyeDemirbasKN güncellenmez — ne formdan gelirse gelsin yoksayılır

        // ── Checkbox alanları ─────────────────────────────────────────────────
        $data['oduncVerilemez'] = $request->has('oduncVerilemez') == "true" ? "true" : "false";
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

        // ── Yazar: kayıtlı (yazar_ids[]) veya manuel (ad/soyad dizileri) ───────────
        $orderedYazarIds = $this->resolveOrderedYazarIdsForSave($request, $data);
        $data['yazarId'] = $orderedYazarIds[0] ?? null;

        // ── Alt Tür: serbest metinden DB'de bul/oluştur ─────────────────────
        $altTurAd = trim($request->input('altTurAd', ''));
        if ($altTurAd !== '') {
            $altTur = AltTur::findOrCreateByAd($altTurAd);
            $data['altTurId'] = $altTur->id;
        } else {
            $data['altTurId'] = null;
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

        // ── Durum: ödünç/rezerve iken güncellenmez; aksi halde yalnızca manuel değerler ──
        if (!$durumKilitli) {
            $data['kunyeDurum'] = $request->input('kunyeDurum');
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
        } elseif ($request->filled('copy_kapak_from_katalog_id')) {
            $copied = $this->duplicateKunyeKapakFromKatalog((int) $request->input('copy_kapak_from_katalog_id'));
            if ($copied !== null) {
                if ($kitap->kunyeKapakResmi) {
                    Storage::disk('public')->delete($kitap->kunyeKapakResmi);
                }
                $data['kunyeKapakResmi'] = $copied;
            }
        } elseif ($request->input('kapak_sil') === '1') {
            if ($kitap->kunyeKapakResmi) Storage::disk('public')->delete($kitap->kunyeKapakResmi);
            $data['kunyeKapakResmi'] = null;
        }

        $kitap->update($data);
        $this->syncKatalogYazarlar($kitap, $orderedYazarIds);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => '"' . $data['kunyeEserAdi'] . '" başarıyla güncellendi.']);
        }
        return redirect()->route('katalog.index')->with('success', '"' . $data['kunyeEserAdi'] . '" başarıyla güncellendi.');
    }

    /**
     * Kitap kopyalama: kaynak kaydın kapağını public disk üzerinde yeni bir dosyaya kopyalar (veya URL ise indirir).
     */
    private function duplicateKunyeKapakFromKatalog(int $katalogId): ?string
    {
        $src = Katalog::find($katalogId);
        if (!$src || !$src->kunyeKapakResmi) {
            return null;
        }

        $p = trim($src->kunyeKapakResmi);
        if ($p === '') {
            return null;
        }

        if (str_starts_with($p, 'http://') || str_starts_with($p, 'https://')) {
            try {
                $imageContents = \Illuminate\Support\Facades\Http::timeout(30)->get($p)->body();
                if ($imageContents === '' || $imageContents === false) {
                    return null;
                }
                $filename = 'kapaklar/' . uniqid('copy_') . '.jpg';
                Storage::disk('public')->put($filename, $imageContents);

                return $filename;
            } catch (\Throwable $e) {
                return null;
            }
        }

        $oldPath = ltrim($p, '/');
        if (str_starts_with($oldPath, 'storage/')) {
            $oldPath = substr($oldPath, strlen('storage/'));
        }

        if (!Storage::disk('public')->exists($oldPath)) {
            return null;
        }

        $ext = pathinfo($oldPath, PATHINFO_EXTENSION);
        if ($ext === '') {
            $ext = 'jpg';
        }
        $newPath = 'kapaklar/' . uniqid('copy_') . '.' . $ext;
        Storage::disk('public')->copy($oldPath, $newPath);

        return $newPath;
    }
}
