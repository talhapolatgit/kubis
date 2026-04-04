<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Katalog;
use App\Models\Kategori;
use App\Models\Kutuphane;
use App\Models\OduncIslem;
use App\Models\UyeFavori;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CatalogApiController extends Controller
{
    /**
     * Katalog tablosundaki kitap listesini döndürür.
     * İlişkili yazar, yayınevi ve kütüphane adlarını da içerir.
     */
    public function index(Request $request): JsonResponse
    {
        // Sayfa başına kayıt sayısını sınırla (performans için)
        $perPage = (int) $request->input('per_page', 10);
        if ($perPage < 10) {
            $perPage = 10;
        } elseif ($perPage > 100) {
            $perPage = 100;
        }

        $query = Katalog::query()
            ->with([
                'yazar:id,ad',
                'yayinevi:id,ad',
                'kutuphane:id,title',
            ]);

        $query->whereIn('kunyeDurum', ['Rafta','Ödünç']);    

        // Basit arama ve filtreler (opsiyonel – mevcut web arayüzü ile uyumlu)
        if ($request->filled('search')) {
            $s = $request->input('search');
            $query->where(function ($q) use ($s) {
                $q->where('kunyeEserAdi', 'LIKE', "%{$s}%")
                    ->orWhere('kunyeISBNISSN', 'LIKE', "%{$s}%")
                    ->orWhere('kunyeYazar', 'LIKE', "%{$s}%");
            });
        }

        if ($request->filled('durum')) {
            $query->where('kunyeDurum', $request->input('durum'));
        }

        if ($request->filled('kategori_id')) {
            $query->where('kunyeKategori', (int) $request->input('kategori_id'));
        }

        if ($request->filled('kutuphane_id')) {
            $query->where('kutuphaneId', (int) $request->input('kutuphane_id'));
        }

        // Yazar filtreleri: önce ID, yoksa isim ile LIKE
        if ($request->filled('yazar_id')) {
            $query->where('yazarId', (int) $request->input('yazar_id'));
        } elseif ($request->filled('yazar_adi')) {
            $y = $request->input('yazar_adi');
            $query->where(function ($q) use ($y) {
                $q->where('kunyeYazar', 'LIKE', '%' . $y . '%');
            });
        }

        // Yayınevi filtreleri: önce ID, yoksa isim ile LIKE
        if ($request->filled('yayinevi_id')) {
            $query->where('yayineviId', (int) $request->input('yayinevi_id'));
        } elseif ($request->filled('yayinevi_adi')) {
            $p = $request->input('yayinevi_adi');
            $query->where(function ($q) use ($p) {
                $q->where('kunyeYayinlayan', 'LIKE', '%' . $p . '%');
            });
        }

        // En yeni kayıtlar önce gelecek şekilde sırala
        $kitaplar = $query
            ->orderByDesc('id')
            ->paginate($perPage);

        // Çıktıyı mobil kullanıma uygun, sade bir formata dönüştür
        $rows = $kitaplar->getCollection()->transform(function (Katalog $k) {
            return [
                'id'             => $k->id,
                'demirbas_no'    => $k->kunyeDemirbasKN,
                'eser_adi'       => $k->kunyeEserAdi,
                'eser_adi_alt'   => $k->kunyeEserAdiAlt,
                'isbn_issn'      => $k->kunyeISBNISSN,
                'yazar_adi'      => optional($k->yazar)->ad ?? $k->kunyeYazar,
                'yayinevi_adi'   => optional($k->yayinevi)->ad ?? $k->kunyeYayinlayan,
                'kutuphane_adi'  => optional($k->kutuphane)->title,
                'yayin_yeri'     => $k->kunyeYayinYeri,
                'yayin_tarihi'   => $k->kunyeYayinTarihi,
                'kategori_id'    => $k->kunyeKategori,
                'siniflama_yer'  => $k->kunyeSiniflamaYer,
                'dil'            => $k->kunyeDilKN,
                'sayfaSayisi'    => $k->kunyeSayfaSayisi,
                'aciklama'       => $k->aciklama,
                'durum'          => $k->kunyeDurum,
                'odunc_verilemez'=> (bool) $k->oduncVerilemez,
                'rezerv_edilemez'=> (bool) false,
                'kapak'          => 'storage/'.$k->kunyeKapakResmi,
                'tahmini_musaitlik' => optional($k->tahminiMusaitlik)->iade_tarihi_planlanan?->toDateString() ?? null,
            ];
        });

        return response()->json([
            'status'  => Response::HTTP_OK,
            'success' => true,
            'message' => 'Kitap listesi başarıyla getirildi.',
            'data'    => [
                'rows'          => $rows,
                'current_page'  => $kitaplar->currentPage(),
                'last_page'     => $kitaplar->lastPage(),
                'per_page'      => $kitaplar->perPage(),
                'total_records' => $kitaplar->total(),
                'from'          => $kitaplar->firstItem() ?? 0,
                'to'            => $kitaplar->lastItem() ?? 0,
            ],
        ], Response::HTTP_OK);
    }

    /**
     * Aktif kategori listesini döndürür.
     */
    public function categories(): JsonResponse
    {
        $kategoriler = Kategori::aktif()
            ->orderBy('title')
            ->get(['id', 'title']);

        return response()->json([
            'status'  => Response::HTTP_OK,
            'success' => true,
            'message' => 'Kategori listesi başarıyla getirildi.',
            'data'    => $kategoriler,
        ], Response::HTTP_OK);
    }

    /**
     * Aktif kütüphane listesini döndürür.
     */
    public function libraries(): JsonResponse
    {
        $kutuphaneler = Kutuphane::aktif()
            ->orderBy('title')
            ->get(['id', 'title', 'address', "phone", "email", "statu"]);

        return response()->json([
            'status'  => Response::HTTP_OK,
            'success' => true,
            'message' => 'Kütüphane listesi başarıyla getirildi.',
            'data'    => $kutuphaneler,
        ], Response::HTTP_OK);
    }


    /**
     * Giriş yapmış üyenin ödünç işlemlerini listeler.
     *
     * Query parametreleri:
     *   statu        → aktif | iade_edildi | kayip  (varsayılan: tümü)
     *   per_page     → 10-100 arası (varsayılan: 10)
     */
    public function loans(Request $request): JsonResponse
    {
        /** @var Uye $uye */
        $uye = $request->attributes->get('uye');
 
        // Sayfa başına kayıt sayısını sınırla
        $perPage = (int) $request->input('per_page', 10);
        if ($perPage < 10) {
            $perPage = 10;
        } elseif ($perPage > 100) {
            $perPage = 100;
        }
 
        $query = OduncIslem::query()
            ->where('uye_id', $uye->id)
            ->with([
                'katalog:id,kunyeEserAdi,kunyeEserAdiAlt,kunyeISBNISSN,kunyeYazar,kunyeYayinlayan,'
                    . 'kunyeYayinTarihi,kunyeKapakResmi,kunyeDurum,kunyeDemirbasKN,yazarId,yayineviId,kutuphaneId',
                'katalog.yazar:id,ad',
                'katalog.yayinevi:id,ad',
                'katalog.kutuphane:id,title',
                'kutuphane:id,title',
            ]);
 
        // Statu filtresi
        if ($request->filled('statu')) {
            $query->where('statu', $request->input('statu'));
        }
 
        // En yeni işlemler önce
        /* $islemler = $query
            ->orderBy('odunc_tarihi', )
            ->orderByDesc('id')
            ->paginate($perPage); */


            $islemler = $query
            ->orderByRaw("CASE WHEN statu = 'iade_edildi' THEN 1 ELSE 0 END ASC")
            ->orderByRaw("CASE WHEN statu = 'aktif' THEN iade_tarihi_planlanan END ASC")
            ->orderByDesc('id')
            ->paginate($perPage);
 
        $rows = $islemler->getCollection()->transform(function (OduncIslem $islem) {
            $katalog = $islem->katalog;
 
            return [
                // ─ Ödünç işlem bilgileri ─────────────────────────────────────
                'islem_id'               => $islem->id,
                'odunc_tarihi'           => $islem->odunc_tarihi?->toDateString(),
                'iade_tarihi_planlanan'  => $islem->iade_tarihi_planlanan?->toDateString(),
                'iade_tarihi_gercek'     => $islem->iade_tarihi_gercek?->toDateString(),
                'sure_uzatimi'           => $islem->sure_uzatimi,
                'statu'                  => $islem->statu,
                'statu_label'            => $islem->statu_label,
                'gecikiyor_mu'           => $islem->gecikiyor_mu,
                'gecikme_gun'            => $islem->gecikme_gun,
                'kalan_gun'              => $islem->kalan_gun,
                // ─ Kitap bilgileri ───────────────────────────────────────────
                'kitap'                  => $katalog ? [
                    'id'            => $katalog->id,
                    'demirbas_no'   => $katalog->kunyeDemirbasKN,
                    'eser_adi'      => $katalog->kunyeEserAdi,
                    'eser_adi_alt'  => $katalog->kunyeEserAdiAlt,
                    'isbn_issn'     => $katalog->kunyeISBNISSN,
                    'yazar_adi'     => optional($katalog->yazar)->ad ?? $katalog->kunyeYazar,
                    'yayinevi_adi'  => optional($katalog->yayinevi)->ad ?? $katalog->kunyeYayinlayan,
                    'yayin_tarihi'  => $katalog->kunyeYayinTarihi,
                    'kapak'         => 'storage/' . $katalog->kunyeKapakResmi,
                    'sayfaSayisi'    => $katalog->kunyeSayfaSayisi,
                    'aciklama'       => $k->aciklama,
                ] : null,
                // ─ Kütüphane bilgisi ─────────────────────────────────────────
                'kutuphane_adi'          => optional($islem->kutuphane)->title,
            ];
        })
        ->filter(fn($row) => $row['kitap'] !== null) // katalog null olanları çıkar
        ->values(); // index'leri sıfırla;
 
        return response()->json([
            'status'  => Response::HTTP_OK,
            'success' => true,
            'message' => 'Ödünç işlem listesi başarıyla getirildi.',
            'data'    => [
                'rows'          => $rows,
                'current_page'  => $islemler->currentPage(),
                'last_page'     => $islemler->lastPage(),
                'per_page'      => $islemler->perPage(),
                'total_records' => $islemler->total(),
                'from'          => $islemler->firstItem() ?? 0,
                'to'            => $islemler->lastItem() ?? 0,
            ],
        ], Response::HTTP_OK);
    }


    public function memberFavorites(Request $request): JsonResponse
    {
        /** @var Uye $uye */
        $uye = $request->attributes->get('uye');
 
        // Sayfa başına kayıt sayısını sınırla
        $perPage = (int) $request->input('per_page', 10);
        if ($perPage < 10) {
            $perPage = 10;
        } elseif ($perPage > 100) {
            $perPage = 100;
        }
 
        $query = UyeFavori::query()
            ->where('uye_id', $uye->id)
            ->with([
                'katalog:id,kunyeEserAdi,kunyeEserAdiAlt,kunyeISBNISSN,kunyeYazar,kunyeYayinlayan,'
                    . 'kunyeYayinTarihi,kunyeKapakResmi,kunyeDurum,kunyeDemirbasKN,yazarId,yayineviId,kutuphaneId',
                'katalog.yazar:id,ad',
                'katalog.yayinevi:id,ad',
                'katalog.kutuphane:id,title',
                'kutuphane:id,title',
            ]);
 
        // Statu filtresi
        if ($request->filled('statu')) {
            $query->where('statu', $request->input('statu'));
        }
 
        // En yeni işlemler önce
        /* $islemler = $query
            ->orderBy('odunc_tarihi', )
            ->orderByDesc('id')
            ->paginate($perPage); */


            $islemler = $query
            ->orderByDesc('id')
            ->paginate($perPage);
 
        $rows = $islemler->getCollection()->transform(function (UyeFavori $favori) {
            $katalog = $favori->katalog;
 
            return [
                // ─ Ödünç işlem bilgileri ─────────────────────────────────────
                'islem_id'               => $favori->id,
                'ekleme_tarihi'           => $favori->created_at?->toDateString(),
                // ─ Kitap bilgileri ───────────────────────────────────────────
                'kitap'                  => $katalog ? [
                    'id'            => $katalog->id,
                    'demirbas_no'   => $katalog->kunyeDemirbasKN,
                    'eser_adi'      => $katalog->kunyeEserAdi,
                    'eser_adi_alt'  => $katalog->kunyeEserAdiAlt,
                    'isbn_issn'     => $katalog->kunyeISBNISSN,
                    'yazar_adi'     => optional($katalog->yazar)->ad ?? $katalog->kunyeYazar,
                    'yayinevi_adi'  => optional($katalog->yayinevi)->ad ?? $katalog->kunyeYayinlayan,
                    'yayin_tarihi'  => $katalog->kunyeYayinTarihi,
                    'kapak'         => 'storage/' . $katalog->kunyeKapakResmi,
                    'kategori_id'   => $katalog->kunyeKategori,
                    'siniflama_yer'  => $katalog->kunyeSiniflamaYer,
                    'dil'            => $katalog->kunyeDilKN,
                    'durum'          => $katalog->kunyeDurum,
                    'aciklama'       => $katalog->aciklama,
                    'kutuphane'      => $katalog->kutuphane,
                    'tahmini_musaitlik' => optional($katalog->tahminiMusaitlik)->iade_tarihi_planlanan?->toDateString() ?? null,
                ] : null,
            ];
        })
        ->filter(fn($row) => $row['kitap'] !== null) // katalog null olanları çıkar
        ->values(); // index'leri sıfırla;
 
        return response()->json([
            'status'  => Response::HTTP_OK,
            'success' => true,
            'message' => 'Favori listesi başarıyla getirildi.',
            'data'    => [
                'rows'          => $rows,
                'current_page'  => $islemler->currentPage(),
                'last_page'     => $islemler->lastPage(),
                'per_page'      => $islemler->perPage(),
                'total_records' => $islemler->total(),
                'from'          => $islemler->firstItem() ?? 0,
                'to'            => $islemler->lastItem() ?? 0,
            ],
        ], Response::HTTP_OK);
    }
    
}

