<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Katalog;
use App\Models\Kategori;
use App\Models\Kutuphane;
use App\Models\OduncIslem;
use App\Models\UyeFavori;
use App\Models\UyeBekleme;
use App\Models\UyeRezerve;
use App\Services\WebhookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\DB;

class CatalogApiController extends Controller
{
    public function __construct(
        private readonly WebhookService $webhookService
    ) {}
    /**
     * Katalog tablosundaki kitap listesini döndürür.
     * İlişkili yazar, yayınevi ve kütüphane adlarını da içerir.
     */
    public function index(Request $request): JsonResponse
    {
        /** @var Uye $uye */
        $uye = $request->attributes->get('uye');
        // Sayfa başına kayıt sayısını sınırla (performans için)
        $perPage = (int) $request->input('per_page', 10);
        if ($perPage < 10) {
            $perPage = 10;
        } elseif ($perPage > 100) {
            $perPage = 100;
        }

        $query = Katalog::query()
            ->with([
                'yazarlar:id,ad,soyad',
                'yazar:id,ad,soyad',
                'yayinevi:id,ad',
                'kutuphane:id,title',
            ]);

        $query->whereIn('kunyeDurum', ['Rafta','Ödünç', 'Rezerve']);    

        // Basit arama ve filtreler (opsiyonel – mevcut web arayüzü ile uyumlu)
        if ($request->filled('search')) {
            $s = $request->input('search');
            $query->where(function ($q) use ($s) {
                $q->where('kunyeEserAdi', 'LIKE', "%{$s}%")
                    ->orWhere('kunyeISBNISSN', 'LIKE', "%{$s}%")
                    ->orWhere('kunyeYazar', 'LIKE', "%{$s}%");
            });
        }

        if ($request->filled('katalog_id')) {
            $query->where('id', $request->input('katalog_id'));
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

        // Yazar filtreleri: önce ID (pivot veya eski yazarId), yoksa isim ile LIKE
        if ($request->filled('yazar_id')) {
            $yid = (int) $request->input('yazar_id');
            $query->where(function ($q) use ($yid) {
                $q->where('yazarId', $yid)
                    ->orWhereHas('yazarlar', function ($q2) use ($yid) {
                        $q2->where('yazarlar.id', $yid);
                    });
            });
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
        $rows = $kitaplar->getCollection()->transform(function (Katalog $k) use ($uye) {
            return [
                'id'             => $k->id,
                // 'demirbas_no'    => $k->kunyeDemirbasKN,
                'eser_adi'       => $k->kunyeEserAdi,
                // 'eser_adi_alt'   => $k->kunyeEserAdiAlt,
                // 'isbn_issn'      => $k->kunyeISBNISSN,
                'yazar_adi'      => $k->formattedYazarlarAdi(),
                // 'yayinevi_adi'   => optional($k->yayinevi)->ad ?? $k->kunyeYayinlayan,
                'kutuphane_adi'  => optional($k->kutuphane)->title,
                // 'yayin_yeri'     => $k->kunyeYayinYeri,
                // 'yayin_tarihi'   => $k->kunyeYayinTarihi,
                // 'kategori_id'    => $k->kunyeKategori,
                // 'siniflama_yer'  => $k->kunyeSiniflamaYer,
                // 'dil'            => $k->kunyeDilKN,
                // 'sayfaSayisi'    => $k->kunyeSayfaSayisi,
                // 'aciklama'       => $k->aciklama,
                'durum'          => $k->kunyeDurum,
                // 'odunc_verilemez'=> (bool) $k->oduncVerilemez,
                // 'rezerv_edilemez'=> (bool) false,
                'kapak'          => $k->kapak_resim_path,
                // 'tahmini_musaitlik' => optional($k->tahminiMusaitlik)->iade_tarihi_planlanan?->toDateString() ?? null,
                // 'favorimi'      => $k->favorimi($uye->id),
                // 'rezervemi'     => $k->rezervemi($uye->id),
                // 'beklememi'     => $k->beklememi($uye->id),
                // 'oduncmu'       => $k->oduncmu($uye->id),
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

    public function catalogDetail(Request $request): JsonResponse
    {
        /** @var Uye $uye */
        $uye = $request->attributes->get('uye');
    
        if ($request->filled('katalog_id')) {
            $k = Katalog::where('id', $request->input('katalog_id'));

            if ($k != null) {
                $katalog = $k->with([
                    'yazarlar:id,ad,soyad',
                    'yazar:id,ad,soyad',
                    'yayinevi:id,ad',
                    'kutuphane:id,title',
                ])
                ->first();
            } else {
                return response()->json([
                    'status' => Response::HTTP_CONFLICT,
                    'success' => false,
                    'message' => 'Kitap bulunamadı.',
                ], Response::HTTP_CONFLICT);
            }
        } else {
            return response()->json([
                'status' => Response::HTTP_CONFLICT,
                'success' => false,
                'message' => 'Katalog id zorunlu',
            ], Response::HTTP_CONFLICT);
        }

        // Çıktıyı mobil kullanıma uygun, sade bir formata dönüştür
        $row = [
                'id'             => $katalog->id,
                'demirbas_no'    => $katalog->kunyeDemirbasKN,
                'eser_adi'       => $katalog->kunyeEserAdi,
                'eser_adi_alt'   => $katalog->kunyeEserAdiAlt,
                'isbn_issn'      => $katalog->kunyeISBNISSN,
                'yazar_adi'      => $katalog->formattedYazarlarAdi(),
                'yayinevi_adi'   => optional($katalog->yayinevi)->ad ?? $katalog->kunyeYayinlayan,
                'kutuphane_adi'  => optional($katalog->kutuphane)->title,
                'yayin_yeri'     => $katalog->kunyeYayinYeri,
                'yayin_tarihi'   => $katalog->kunyeYayinTarihi,
                'kategori_id'    => $katalog->kunyeKategori,
                'siniflama_yer'  => $katalog->kunyeSiniflamaYer,
                'dil'            => $katalog->kunyeDilKN,
                'sayfaSayisi'    => $katalog->kunyeSayfaSayisi,
                'aciklama'       => $katalog->aciklama,
                'durum'          => $katalog->kunyeDurum,
                'odunc_verilemez'=> (bool) $katalog->oduncVerilemez,
                'rezerv_edilemez'=> (bool) $katalog->oduncVerilemez,
                'kapak'          => $katalog->kapak_resim_path,
                'tahmini_musaitlik' => optional($katalog->tahminiMusaitlik)->iade_tarihi_planlanan?->toDateString() ?? null,
                'favorimi'      => $katalog->favorimi($uye->id),
                'rezervemi'     => $katalog->rezervemi($uye->id),
                'beklememi'     => $katalog->beklememi($uye->id),
                'oduncmu'       => $katalog->oduncmu($uye->id),
            ];

        return response()->json([
            'status'  => Response::HTTP_OK,
            'success' => true,
            'message' => 'Kitap detayları başarıyla getirildi.',
            'data'    => [$row],
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
            ->get(['id', 'title', 'address', "phone", "email", "latitude", "longitude", "statu"]);

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
                'katalog.yazarlar:id,ad,soyad',
                'katalog.yazar:id,ad,soyad',
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
                    'yazar_adi'     => $katalog->formattedYazarlarAdi(),
                    'yayinevi_adi'  => optional($katalog->yayinevi)->ad ?? $katalog->kunyeYayinlayan,
                    'yayin_tarihi'  => $katalog->kunyeYayinTarihi,
                    'kapak'         => $katalog->kapak_resim_path,
                    'sayfaSayisi'    => $katalog->kunyeSayfaSayisi,
                    'aciklama'       => $katalog->aciklama,
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
                'katalog:id,kunyeEserAdi,kunyeEserAdiAlt,kunyeISBNISSN,kunyeYazar,kunyeYayinlayan,kunyeKategori,kunyeSiniflamaYer,kunyeDilKN,'
                    . 'kunyeYayinTarihi,kunyeKapakResmi,kunyeDurum,kunyeDemirbasKN,yazarId,yayineviId,kutuphaneId,aciklama',
                'katalog.yazarlar:id,ad,soyad',
                'katalog.yazar:id,ad,soyad',
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
                    'yazar_adi'     => $katalog->formattedYazarlarAdi(),
                    'yayinevi_adi'  => optional($katalog->yayinevi)->ad ?? $katalog->kunyeYayinlayan,
                    'yayin_tarihi'  => $katalog->kunyeYayinTarihi,
                    'kapak'         => $katalog->kapak_resim_path,
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


    public function insertFavorites(Request $request): JsonResponse
    {
        /** @var Uye $uye */
        $uye = $request->attributes->get('uye');

        $validated = $request->validate([
            'katalog_id' => ['required']
        ], [
            'katalog_id.required' => 'Katalog seçimi zorunludur.',
        ]);

        $check = UyeFavori::query()->where('katalog_id', $validated['katalog_id'])->where('uye_id', $uye->id)->first();

        if($check) {
            return response()->json([
                'status' => Response::HTTP_CONFLICT,
                'success' => false,
                'message' => 'Bu kitap zaten favoriye eklenmiş.',
            ], Response::HTTP_CONFLICT);
        }

        $katalog = Katalog::query()
        ->where('id', $validated['katalog_id'])
        ->where('deleted_at', null)
        ->whereIn('kunyeDurum', ['Rafta', 'Ödünç', 'Rezerve'])
        ->first();

        if($katalog == null) {
            return response()->json([
                'status' => Response::HTTP_CONFLICT,
                'success' => false,
                'message' => 'Kitap bulunamadı',
            ], Response::HTTP_CONFLICT);
        } else {
            $favori = UyeFavori::query()->create([
                'katalog_id' => $validated['katalog_id'],
                'uye_id' => $uye->id,
            ]);

            if($favori) {
                return response()->json([
                    'status' => Response::HTTP_CREATED,
                    'success' => true,
                    'message' => 'Kitap favorilerinize eklendi.',
                    'data' => [
                        'uye_id' => $uye->id,
                        'katalog_id' => $validated['katalog_id'],
                        'eser_adi' => $katalog->kunyeEserAdi,
                        'kapak' => $katalog->kapak_resim_path,
                    ],
                ], Response::HTTP_CREATED);
            } else {
                return response()->json([
                    'message' => 'Favori ekleme başarısız oldu. Lütfen daha sonra tekrar deneyiniz.',
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }
    }


    public function deleteFavorites(Request $request): JsonResponse
    {
        /** @var Uye $uye */
        $uye = $request->attributes->get('uye');

        $validated = $request->validate([
            'katalog_id' => ['required']
        ], [
            'katalog_id.required' => 'Katalog seçimi zorunludur.',
        ]);

        $check = UyeFavori::query()->where('katalog_id', $validated['katalog_id'])->where('uye_id', $uye->id)->first();

        if(!$check) {
            return response()->json([
                'status' => Response::HTTP_CONFLICT,
                'success' => false,
                'message' => 'Bu kitap zaten favorilerde kayıtlı değil.',
            ], Response::HTTP_CONFLICT);
        }

        $katalog = Katalog::query()
        ->where('id', $validated['katalog_id'])
        ->where('deleted_at', null)
        ->whereIn('kunyeDurum', ['Rafta', 'Ödünç', 'Rezerve'])
        ->first();

        if($katalog == null) {
            return response()->json([
                'status' => Response::HTTP_CONFLICT,
                'success' => false,
                'message' => 'Kitap bulunamadı',
            ], Response::HTTP_CONFLICT);
        } else {
            $silindi = UyeFavori::query()
                ->where('katalog_id', $validated['katalog_id'])
                ->where('uye_id', $uye->id)
                ->delete();

            if($silindi) {
                return response()->json([
                    'status' => Response::HTTP_OK,
                    'success' => true,
                    'message' => 'Kitap favorilerinizden çıkartıldı.',
                    'data' => [
                        'uye_id' => $uye->id,
                        'katalog_id' => $validated['katalog_id'],
                        'eser_adi' => $katalog->kunyeEserAdi,
                        'kapak' => $katalog->kapak_resim_path,
                    ],
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'message' => 'Favorilerden çıkartma işlemi başarısız oldu. Lütfen daha sonra tekrar deneyiniz.',
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }
    }


    public function memberWaitings(Request $request): JsonResponse
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
 
        $query = UyeBekleme::query()
            ->where('uye_id', $uye->id)
            ->with([
                'katalog:id,kunyeEserAdi,kunyeEserAdiAlt,kunyeISBNISSN,kunyeYazar,kunyeYayinlayan,'
                    . 'kunyeYayinTarihi,kunyeKapakResmi,kunyeDurum,kunyeDemirbasKN,yazarId,yayineviId,kutuphaneId',
                'katalog.yazarlar:id,ad,soyad',
                'katalog.yazar:id,ad,soyad',
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
 
        $rows = $islemler->getCollection()->transform(function (UyeBekleme $bekleme) {
            $katalog = $bekleme->katalog;
 
            return [
                // ─ Ödünç işlem bilgileri ─────────────────────────────────────
                'islem_id'               => $bekleme->id,
                'ekleme_tarihi'           => $bekleme->created_at?->toDateString(),
                // ─ Kitap bilgileri ───────────────────────────────────────────
                'kitap'                  => $katalog ? [
                    'id'            => $katalog->id,
                    'demirbas_no'   => $katalog->kunyeDemirbasKN,
                    'eser_adi'      => $katalog->kunyeEserAdi,
                    'eser_adi_alt'  => $katalog->kunyeEserAdiAlt,
                    'isbn_issn'     => $katalog->kunyeISBNISSN,
                    'yazar_adi'     => $katalog->formattedYazarlarAdi(),
                    'yayinevi_adi'  => optional($katalog->yayinevi)->ad ?? $katalog->kunyeYayinlayan,
                    'yayin_tarihi'  => $katalog->kunyeYayinTarihi,
                    'kapak'         => $katalog->kapak_resim_path,
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
            'message' => 'Bekleme listesi başarıyla getirildi.',
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


    public function insertWaitings(Request $request): JsonResponse
    {
        /** @var Uye $uye */
        $uye = $request->attributes->get('uye');

        $validated = $request->validate([
            'katalog_id' => ['required']
        ], [
            'katalog_id.required' => 'Kitap seçimi zorunludur.',
        ]);

        $check = UyeBekleme::query()->where('katalog_id', $validated['katalog_id'])->where('uye_id', $uye->id)->first();

        if($check) {
            return response()->json([
                'status' => Response::HTTP_CONFLICT,
                'success' => false,
                'message' => 'Bu kitap zaten bekleme listenize eklenmiş.',
            ], Response::HTTP_CONFLICT);
        }

        $katalog = Katalog::query()
        ->where('id', $validated['katalog_id'])
        ->where('deleted_at', null)
        ->whereIn('kunyeDurum', ['Rafta', 'Ödünç', 'Rezerve'])
        ->first();

        if($katalog == null) {
            return response()->json([
                'status' => Response::HTTP_CONFLICT,
                'success' => false,
                'message' => 'Kitap bulunamadı.',
            ], Response::HTTP_CONFLICT);
        } else {

            if($katalog->kunyeDurum == "Rafta") {
                return response()->json([
                    'status' => Response::HTTP_CONFLICT,
                    'success' => false,
                    'message' => 'Kitap zaten müsait. Beklemenize gerek yoktur.',
                ], Response::HTTP_CONFLICT);
            }

            $checkRezerve = UyeRezerve::query()->where('katalog_id', $validated['katalog_id'])
            ->where('uye_id', $uye->id)
            ->where('iptalMi', 'false')
            ->where('rezerve_bitis', '>', now())
            ->where('deleted_at', null)
            ->first();

        if($checkRezerve) {
            return response()->json([
                'status' => Response::HTTP_CONFLICT,
                'success' => false,
                'message' => 'Bu kitabı zaten rezerve ettiniz. Rezervasyon süreniz dolmadan kütüphanemize giderek ödünç alabilirsiniz.',
            ], Response::HTTP_CONFLICT);
        }

        $checkOdunc = OduncIslem::query()->where('katalog_id', $validated['katalog_id'])
        ->where('uye_id', $uye->id)
        ->where('statu', 'aktif')
        ->first();

        if($checkOdunc) {
            return response()->json([
                'status' => Response::HTTP_CONFLICT,
                'success' => false,
                'message' => 'Bu kitap zaten sizde ödünçte.',
            ], Response::HTTP_CONFLICT);
        }

            $bekleme = UyeBekleme::query()->create([
                'katalog_id' => $validated['katalog_id'],
                'uye_id' => $uye->id,
            ]);

            if($bekleme) {
                return response()->json([
                    'status' => Response::HTTP_CREATED,
                    'success' => true,
                    'message' => 'Bekleme listenize başarıyla eklendi.',
                    'data' => [
                        'uye_id' => $uye->id,
                        'katalog_id' => $validated['katalog_id'],
                        'eser_adi' => $katalog->kunyeEserAdi,
                        'kapak' => $katalog->kapak_resim_path,
                    ],
                ], Response::HTTP_CREATED);
            } else {
                return response()->json([
                    'message' => 'Bekleme listenize ekleme başarısız oldu. Lütfen daha sonra tekrar deneyiniz.',
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }
    }


    public function deleteWaitings(Request $request): JsonResponse
    {
        /** @var Uye $uye */
        $uye = $request->attributes->get('uye');

        $validated = $request->validate([
            'katalog_id' => ['required']
        ], [
            'katalog_id.required' => 'Katalog seçimi zorunludur.',
        ]);

        $check = UyeBekleme::query()->where('katalog_id', $validated['katalog_id'])->where('uye_id', $uye->id)->first();

        if(!$check) {
            return response()->json([
                'status' => Response::HTTP_CONFLICT,
                'success' => false,
                'message' => 'Bu kitap zaten bekleme listenizde kayıtlı değil.',
            ], Response::HTTP_CONFLICT);
        }

        $katalog = Katalog::query()
        ->where('id', $validated['katalog_id'])
        ->where('deleted_at', null)
        ->whereIn('kunyeDurum', ['Rafta', 'Ödünç', 'Rezerve'])
        ->first();

        if($katalog == null) {
            return response()->json([
                'status' => Response::HTTP_CONFLICT,
                'success' => false,
                'message' => 'Kitap bulunamadı',
            ], Response::HTTP_CONFLICT);
        } else {
            $silindi = UyeBekleme::query()
                ->where('katalog_id', $validated['katalog_id'])
                ->where('uye_id', $uye->id)
                ->delete();

            if($silindi) {
                return response()->json([
                    'status' => Response::HTTP_OK,
                    'success' => true,
                    'message' => 'Kitap bekleme listesinden çıkartıldı.',
                    'data' => [
                        'uye_id' => $uye->id,
                        'katalog_id' => $validated['katalog_id'],
                        'eser_adi' => $katalog->kunyeEserAdi,
                        'kapak' => $katalog->kapak_resim_path
                    ],
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'message' => 'Bekleme listesinden çıkartma işlemi başarısız oldu. Lütfen daha sonra tekrar deneyiniz.',
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }
    }


    public function memberCounts(Request $request): JsonResponse
    {
        /** @var Uye $uye */
        $uye = $request->attributes->get('uye');

        $favoricount  = UyeFavori::where('uye_id', $uye->id)->count();
        $odunccount   = OduncIslem::where('uye_id', $uye->id)->count();
        $rezervecount = UyeRezerve::where('uye_id', $uye->id)->where('deleted_at', null)->count();
        $beklemecount = UyeBekleme::where('uye_id', $uye->id)->count();

        return response()->json([
            'status'  => Response::HTTP_OK,
            'success' => true,
            'message' => 'Üye sayaç listesi başarıyla getirildi.',
            'data'    => [
                'favori_count'  => $favoricount,
                'odunc_count'   => $odunccount,
                'rezerve_count' => $rezervecount,
                'bekleme_count' => $beklemecount,
            ],
        ], Response::HTTP_OK);
    }
    

    public function memberReservations(Request $request): JsonResponse
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
 
        $query = UyeRezerve::query()
            ->where('uye_id', $uye->id)
            ->with([
                'katalog:id,kunyeEserAdi,kunyeEserAdiAlt,kunyeISBNISSN,kunyeYazar,kunyeYayinlayan,'
                    . 'kunyeYayinTarihi,kunyeKapakResmi,kunyeDurum,kunyeDemirbasKN,yazarId,yayineviId,kutuphaneId',
                'katalog.yazarlar:id,ad,soyad',
                'katalog.yazar:id,ad,soyad',
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
 
        $rows = $islemler->getCollection()->transform(function (UyeRezerve $rezerve) {
            $katalog = $rezerve->katalog;
 
            return [
                // ─ Ödünç işlem bilgileri ─────────────────────────────────────
                'islem_id'               => $rezerve->id,
                'ekleme_tarihi'           => $rezerve->created_at?->toDateString(),
                'rezerve_baslangic' => $rezerve->rezerve_baslangic,
                'rezerve_bitis' => $rezerve->rezerve_bitis,
                'oduncAldiMi' => $rezerve->oduncAldiMi,
                'iptalMi' => $rezerve->iptalMi,
                'suresiDolduMu' => $rezerve->suresiDolduMu,
                // ─ Kitap bilgileri ───────────────────────────────────────────
                'kitap'                  => $katalog ? [
                    'id'            => $katalog->id,
                    'demirbas_no'   => $katalog->kunyeDemirbasKN,
                    'eser_adi'      => $katalog->kunyeEserAdi,
                    'eser_adi_alt'  => $katalog->kunyeEserAdiAlt,
                    'isbn_issn'     => $katalog->kunyeISBNISSN,
                    'yazar_adi'     => $katalog->formattedYazarlarAdi(),
                    'yayinevi_adi'  => optional($katalog->yayinevi)->ad ?? $katalog->kunyeYayinlayan,
                    'yayin_tarihi'  => $katalog->kunyeYayinTarihi,
                    'kapak'         => $katalog->kapak_resim_path,
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
            'message' => 'Rezervasyon listesi başarıyla getirildi.',
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

    public function insertReservation(Request $request): JsonResponse
    {
        /** @var Uye $uye */
        $uye = $request->attributes->get('uye');

        $validated = $request->validate([
            'katalog_id' => ['required']
        ], [
            'katalog_id.required' => 'Kitap seçimi zorunludur.',
        ]);

        $check = UyeRezerve::query()->where('katalog_id', $validated['katalog_id'])->where('uye_id', $uye->id)->where('rezerve_bitis', '>', now())->where('iptalMi', 'false')->first();

        if($check) {
            return response()->json([
                'status' => Response::HTTP_CONFLICT,
                'success' => false,
                'message' => 'Bu kitap için aktif rezervasyonunuz bulunmaktadır.',
            ], Response::HTTP_CONFLICT);
        }

        $katalog = Katalog::query()
        ->where('id', $validated['katalog_id'])
        ->where('deleted_at', null)
        ->whereIn('kunyeDurum', ['Rafta', 'Ödünç', 'Rezerve'])
        ->first();

        if($katalog == null) {
            return response()->json([
                'status' => Response::HTTP_CONFLICT,
                'success' => false,
                'message' => 'Kitap bulunamadı.',
            ], Response::HTTP_CONFLICT);
        } else {

            if($katalog->kunyeDurum == "Ödünç") {
                return response()->json([
                    'status' => Response::HTTP_CONFLICT,
                    'success' => false,
                    'message' => 'Bu kitabı başka üyemiz ödünç aldı. Bekleme listenize ekleyerek kitap müsait olduğunda bildirim alabilirsiniz.',
                ], Response::HTTP_CONFLICT);
            }

            if($katalog->kunyeDurum == "Rezerve") {
                return response()->json([
                    'status' => Response::HTTP_CONFLICT,
                    'success' => false,
                    'message' => 'Bu kitabı başka üyemiz rezerve etti. Bekleme listenize ekleyerek kitap müsait olduğunda bildirim alabilirsiniz.',
                ], Response::HTTP_CONFLICT);
            }

            if($katalog->kunyeDurum != "Rafta") {
                return response()->json([
                    'status' => Response::HTTP_CONFLICT,
                    'success' => false,
                    'message' => 'Bu kitap rezerve edilememektedir. Bekleme listenize ekleyerek kitap müsait olduğunda bildirim alabilirsiniz.',
                ], Response::HTTP_CONFLICT);
            }

            if($katalog->oduncVerilemez == "true") {
                return response()->json([
                    'status' => Response::HTTP_CONFLICT,
                    'success' => false,
                    'message' => 'Bu kitap rezerve edilememektedir. Kütüphanemizde kullanıma uygundur.',
                ], Response::HTTP_CONFLICT);
            }

            $rezerve = UyeRezerve::query()->create([
                'katalog_id' => $validated['katalog_id'],
                'uye_id' => $uye->id,
                'rezerve_baslangic' => now(),
                'rezerve_bitis' => now()->addHours(24),
                'oduncAldiMi' => 'false',
                'iptalMi' => 'false',
                'suresiDolduMu' => 'false',
            ]);

            if($rezerve) {

                $katalog->kunyeDurum = "Rezerve";
                $katalog->save();

                $beklemecheck = UyeBekleme::query()->where('katalog_id', $validated['katalog_id'])->where('uye_id', $uye->id)->first();

                if($beklemecheck) {
                $beklemecheck->delete();
                }

                return response()->json([
                    'status' => Response::HTTP_CREATED,
                    'success' => true,
                    'message' => 'Kitap başarıyla rezerve edilmiştir.',
                    'data' => [
                        'rezervasyon_no' => $rezerve->id,
                        'uye_id' => $uye->id,
                        'katalog_id' => $validated['katalog_id'],
                        'eser_adi' => $katalog->kunyeEserAdi,
                        'kapak' => $katalog->kapak_resim_path,
                        'rezerve_baslangic' => $rezerve->rezerve_baslangic?->toDateTimeString(),
                        'rezerve_bitis' => $rezerve->rezerve_bitis?->toDateTimeString(),
                        'oduncAldiMi' => $rezerve->oduncAldiMi,
                        'iptalMi' => $rezerve->iptalMi,
                    ],
                ], Response::HTTP_CREATED);
            } else {
                return response()->json([
                    'message' => 'Kitap rezervasyon işlemi başarısız oldu. Lütfen daha sonra tekrar deneyiniz.',
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }
    }


    public function cancelReservation(Request $request): JsonResponse
    {
        /** @var Uye $uye */
        $uye = $request->attributes->get('uye');

        $validated = $request->validate([
            'katalog_id' => ['required']
        ], [
            'katalog_id.required' => 'Katalog seçimi zorunludur.',
        ]);

        $rezerve = UyeRezerve::query()->where('katalog_id', $validated['katalog_id'])->where('uye_id', $uye->id)->where('rezerve_bitis', '>', now())->where('iptalMi', 'false')->first();

        if(!$rezerve) {
            return response()->json([
                'status' => Response::HTTP_CONFLICT,
                'success' => false,
                'message' => 'Bu kitap için aktif rezervasyonunuz bulunmamaktadır.',
            ], Response::HTTP_CONFLICT);
        }

        if($rezerve->oduncAldiMi == "true") {
            return response()->json([
                'status' => Response::HTTP_CONFLICT,
                'success' => false,
                'message' => 'Kitabı ödünç alarak rezervasyonu tamamladınız. Bu sebeple iptal edilememektedir.',
            ], Response::HTTP_CONFLICT);
        }

        $katalog = Katalog::query()
        ->where('id', $validated['katalog_id'])
        ->where('deleted_at', null)
        ->whereIn('kunyeDurum', ['Rafta', 'Ödünç', 'Rezerve'])
        ->first();

        if($katalog == null) {
            return response()->json([
                'status' => Response::HTTP_CONFLICT,
                'success' => false,
                'message' => 'Kitap bulunamadı',
            ], Response::HTTP_CONFLICT);
        } else {

            $rezerve->iptalMi = "true";
            $rezerve->save();

            $katalog->kunyeDurum = "Rafta";
            $katalog->save();

            if($rezerve->iptalMi == "true") {

                $beklemeList = UyeBekleme::where('katalog_id', $katalog->id)
                    ->with('uye')
                    ->get()
                    ->pluck('uye.tc_kimlik')
                    ->toArray();

            if (!empty($beklemeList)) {
                try {
                    $result = $this->webhookService->sendBildirim(
                        tcList:  $beklemeList,
                        title:   'Beklediğiniz kitap artık müsait!',
                        message: $katalog->kunyeEserAdi . " isimli kitap artık müsait. Kaçırmamak için tıkla ve hemen rezerve et 😊",
                    );

                    UyeBekleme::where('katalog_id', $katalog->id)
                        ->update(['bildirim' => DB::raw('COALESCE(bildirim, 0) + 1')]);
    
                } catch (\Exception $e) {
                    // İade işlemi tamamlandı, sadece bildirim başarısız
    
                }
            }

                return response()->json([
                    'status' => Response::HTTP_OK,
                    'success' => true,
                    'message' => 'Kitap rezervasyonunuz iptal edilmiştir.',
                    'data' => [
                        'uye_id' => $uye->id,
                        'katalog_id' => $validated['katalog_id'],
                        'eser_adi' => $katalog->kunyeEserAdi,
                        'kapak' => $katalog->kapak_resim_path,
                        'rezerve_baslangic' => $rezerve->rezerve_baslangic,
                        'rezerve_bitis' => $rezerve->rezerve_bitis,
                        'oduncAldiMi' => $rezerve->oduncAldiMi,
                        'iptalMi' => $rezerve->iptalMi,
                    ],
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'message' => 'Kitap rezervasyonu iptal işlemi başarısız oldu. Lütfen daha sonra tekrar deneyiniz.',
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }
    }


}

