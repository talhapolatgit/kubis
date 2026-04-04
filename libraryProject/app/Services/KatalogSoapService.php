<?php

namespace App\Services;

use App\Models\Katalog;
use App\Models\Kutuphane;
use Illuminate\Support\Facades\Log; 

class KatalogSoapService
{

    private function logService(string $operation, $input, $output): void
    {
        Log::info("SOAP_LOG [{$operation}]", [
            'ip'       => request()->ip(),
            'input'    => $input,
            'response' => $output
        ]);
    }


    // ─── Kitap Listesi ───────────────────────────────────────────────────────────

    public function kitapListesi(): array
    {
        return Katalog::whereNull('deleted_at')
            ->select([
                'id', 'kunyeEserAdi', 'kunyeYazar',
                'kunyeISBNISSN', 'kunyeYayinlayan',
                'kunyeYayinTarihi', 'kunyeDurum',
            ])
            ->get()
            ->toArray();
    }

    public function isbnIleAra(string $isbn): array
    {
        $kitap = Katalog::whereNull('deleted_at')
            ->where('kunyeISBNISSN', 'LIKE', "%{$isbn}%")
            ->first();

        return $kitap ? $kitap->toArray() : ['hata' => 'Kitap bulunamadı.'];
    }

    public function metinIleAra(string $kelime): array
    {
        return Katalog::whereNull('deleted_at')
            ->where(function ($q) use ($kelime) {
                $q->where('kunyeEserAdi', 'LIKE', "%{$kelime}%")
                    ->orWhere('kunyeYazar',  'LIKE', "%{$kelime}%");
            })
            ->select(['id', 'kunyeEserAdi', 'kunyeYazar', 'kunyeISBNISSN', 'kunyeDurum'])
            ->limit(50)
            ->get()
            ->toArray();
    }

    public function idIleGetir(int $id): array
    {
        $kitap = Katalog::whereNull('deleted_at')->find($id);

        return $kitap ? $kitap->toArray() : ['hata' => 'Kayıt bulunamadı.'];
    }

    // ─── kutuphaneSorgula ────────────────────────────────────────────────────────
    /**
     * BelediyeStandartV3 WSDL — kutuphaneSorgula operasyonu.
     *
     * Input  (kutuphaneSorgulaG → sorguParametreType):
     *   belediyeKodu, kullaniciAdi, sifre, ipAdresi
     *
     * Output (kutuphaneSorgulaC → kutuphaneSorgulaCType extends islemSonucType):
     *   belediyeKodu, sonucKodu, sonucAciklamasi,
     *   kutuphaneListesi[ kutuphaneBilgileri[] ], detayListesi[]
     *
     * @param \stdClass $part1
     * @return object
     */
    public function kutuphaneSorgula(\stdClass $part1): object
    {
        $belediyeKodu = $part1->belediyeKodu ?? '';

        if (!$this->kimlikDogrula($part1->kullaniciAdi ?? '', $part1->sifre ?? '')) {
            $yanit =$this->kutuphaneYanit($belediyeKodu, '0001', 'Kullanıcı Adı veya Şifre Hatalı', []);
            $this->logService('kutuphaneSorgula', $part1, $yanit); // LOG
            return $yanit;
        }

        $kutuphaneler = Kutuphane::whereNull('deleted_at')
            ->where('statu', 'aktif')
            ->select(['id', 'title', 'kutuphanePOIId', 'uyelikBasvurusundaBulunulabilirMi'])
            ->orderBy('title')
            ->get();

        if ($kutuphaneler->isEmpty()) {
            $yanit = $this->kutuphaneYanit($belediyeKodu, '0003', 'Kayıt bulunamadı.', []);
            $this->logService('kutuphaneSorgula', $part1, $yanit); // LOG
            return $yanit;
        }

        $liste = $kutuphaneler->map(fn($k) => (object) [
            'kutuphaneKodu'                    => (string) $k->id,
            'kutuphaneAdi'                     => (string) $k->title,
            'kutuphanePOIId'                   => (string) ($k->kutuphanePOIId ?? ''),
            'uyelikBasvurusundaBulunulabilirMi' => (bool) $k->uyelikBasvurusundaBulunulabilirMi,
            'detayListesi'                     => (object) ['detay' => []],
        ])->values()->all();

        $yanit = $this->kutuphaneYanit($belediyeKodu, '0000', 'İşlem Başarılı', $liste);
        $this->logService('kutuphaneSorgula', $part1, $yanit); // LOG
        return $yanit;
    }

    // ─── katalogTarama ───────────────────────────────────────────────────────────
    /**
     * BelediyeStandartV3 WSDL — katalogTarama operasyonu.
     *
     * Input  (katalogTaramaG → katalogTaramaGType extends sorguParametreType):
     *   belediyeKodu, kullaniciAdi, sifre, ipAdresi,
     *   arananKutuphaneKodu, aramaMetni, aramaTuru, detayListesi[]
     *
     * Output (katalogTaramaC → katalogTaramaCType extends islemSonucType):
     *   belediyeKodu, sonucKodu, sonucAciklamasi,
     *   taramaSonucListesi[ taramaSonucu[] ], detayListesi[]
     *
     * aramaTuru: AD | YAZAR | ISBN | KONU | GENEL
     *
     * @param \stdClass $part1
     * @return object
     */
    public function katalogTarama(\stdClass $part1): object
    {
        $belediyeKodu        = $part1->belediyeKodu        ?? '';
        $arananKutuphaneKodu = $part1->arananKutuphaneKodu ?? '';
        $aramaMetni          = trim($part1->aramaMetni     ?? '');
        $aramaTuru           = strtoupper(trim($part1->aramaTuru ?? 'GENEL'));

        if ($arananKutuphaneKodu == 0) {
            $arananKutuphaneKodu = '';
        }

        if (!$this->kimlikDogrula($part1->kullaniciAdi ?? '', $part1->sifre ?? '')) {
            $yanit = $this->katalogYanit($belediyeKodu, '0001', 'Kullanıcı Adı veya Şifre Hatalı', []);
            $this->logService('katalogTarama', $part1, $yanit); // LOG
            return $yanit;
        }

        if ($aramaMetni === '') {
            $yanit = $this->katalogYanit($belediyeKodu, '0003', 'Sorguladığınız bilgilere ait sonuç bulunmamaktadır.', []);
            $this->logService('katalogTarama', $part1, $yanit); // LOG
            return $yanit;
        }

        $query = Katalog::whereNull('deleted_at');

        if ($arananKutuphaneKodu !== '') {
            $query->where('kutuphaneId', $arananKutuphaneKodu);
        }

        switch ($aramaTuru) {
            case '01':    $query->where('kunyeEserAdi',     'LIKE', "%{$aramaMetni}%"); break;
            case '02': $query->where('kunyeYazar',       'LIKE', "%{$aramaMetni}%"); break;
            case '03': $query->where('kunyeYayinlayan',       'LIKE', "%{$aramaMetni}%"); break;
            case '04':  $query->where('kunyeISBNISSN',    'LIKE', "%{$aramaMetni}%"); break;
            case '05':  $query->where('kunyeSiniflamaYer', 'LIKE', "%{$aramaMetni}%"); break;
            default:
                $query->where(function ($q) use ($aramaMetni) {
                    $q->where('kunyeEserAdi',     'LIKE', "%{$aramaMetni}%")
                        ->orWhere('kunyeYazar',      'LIKE', "%{$aramaMetni}%")
                        ->orWhere('kunyeYayinlayan',      'LIKE', "%{$aramaMetni}%")
                        ->orWhere('kunyeISBNISSN',   'LIKE', "%{$aramaMetni}%")
                        ->orWhere('kunyeSiniflamaYer','LIKE', "%{$aramaMetni}%");
                });
        }

        $kitaplar = $query->select([
            'id', 'kunyeEserAdi', 'kunyeYazar', 'kunyeYayinYeri',
            'kunyeYayinlayan', 'kunyeYayinTarihi', 'kunyeISBNISSN',
            'kunyeSiniflamaYer', 'kunyeDurum', 'kutuphaneId',
        ])->limit(100)->get();

        if ($kitaplar->isEmpty()) {
            $yanit = $this->katalogYanit($belediyeKodu, '0003', 'Sorguladığınız bilgilere ait sonuç bulunmamaktadır.', []);
            $this->logService('katalogTarama', $part1, $yanit); // LOG
            return $yanit;
        }

        $kutuphaneler = Kutuphane::whereIn('id', $kitaplar->pluck('kutuphaneId')->filter()->unique())
            ->pluck('title', 'id');

        $liste = $kitaplar->map(function ($k) use ($kutuphaneler) {
            $yayinBilgisi = implode(' ', array_filter([
                $k->kunyeYayinYeri  ? $k->kunyeYayinYeri . ' :' : null,
                $k->kunyeYayinlayan ?? null,
                $k->kunyeYayinTarihi ? ', ' . $k->kunyeYayinTarihi : null,
            ]));

            return (object) [
                'eserAdi'                => (string) ($k->kunyeEserAdi     ?? ''),
                'yazarAdi'               => (string) ($k->kunyeYazar       ?? ''),
                'yayinBilgisi'           => (string) $yayinBilgisi,
                'isbniisn'               => (string) ($k->kunyeISBNISSN    ?? ''),
                'bulunduguKutuphane'     => (string) ($kutuphaneler[$k->kutuphaneId] ?? ''),
                'bulunduguKutuphaneKodu' => (string) ($k->kutuphaneId ?? ''),
                'yerNumarasi'            => (string) ($k->kunyeSiniflamaYer ?? ''),
                'durumu'                 => (string) ($k->kunyeDurum        ?? ''),
                'sonIadeTarihi'          => '',
                'detayListesi'           => (object) ['detay' => []],
            ];
        })->values()->all();

        $yanit = $this->katalogYanit($belediyeKodu, '0000', 'İşlem Başarılı', $liste);
        $this->logService('katalogTarama', $part1, $yanit); // LOG
        return $yanit;
    }

    // ─── ayarOku ─────────────────────────────────────────────────────────────────
    /**
     * BelediyeStandartV3 WSDL — ayarOku operasyonu.
     *
     * Input  (ayarOkuG → ayarOkuGType extends sorguParametreType):
     *   belediyeKodu, kullaniciAdi, sifre, ipAdresi, ayar
     *
     * Output (ayarOkuC → ayarOkuCType extends islemSonucType):
     *   belediyeKodu, sonucKodu, sonucAciklamasi, deger
     *
     * deger her zaman "0" döner.
     *
     * @param \stdClass $part1
     * @return object
     */
    public function ayarOku(\stdClass $part1): object
    {
        $belediyeKodu = $part1->belediyeKodu ?? '';

        if (!$this->kimlikDogrula($part1->kullaniciAdi ?? '', $part1->sifre ?? '')) {
            $yanit = (object) [
                'belediyeKodu'    => $belediyeKodu,
                'sonucKodu'       => '0001',
                'sonucAciklamasi' => 'Kullanıcı Adı veya Şifre Hatalı',
                'deger'           => '',
            ];
            $this->logService('ayarOku', $part1, $yanit); // LOG
            return $yanit;
        }

        $yanit = (object) [
            'belediyeKodu'    => $belediyeKodu,
            'sonucKodu'       => '0000',
            'sonucAciklamasi' => 'İşlem Başarılı',
            'deger'           => '0',
        ];
        $this->logService('ayarOku', $part1, $yanit); // LOG
        return $yanit;
    }


    // ─── ayarOkuListe ────────────────────────────────────────────────────────────
/**
 * BelediyeStandartV3 WSDL — ayarOkuListe operasyonu.
 *
 * Input  (ayarOkuListeG → ayarOkuListeGType extends sorguParametreType):
 *   belediyeKodu, kullaniciAdi, sifre, ipAdresi, ayar
 *
 * Output (ayarOkuListeC → ayarOkuListeCType extends islemSonucType):
 *   belediyeKodu, sonucKodu, sonucAciklamasi,
 *   detayListesi[ detay{ group, key, value }[] ]
 *
 * Desteklenen ayar değerleri:
 *   katalogTaramaAramaTurleri → katalogTarama'da kullanılan arama türü listesi
 *
 * @param \stdClass $part1
 * @return object
 */
public function ayarOkuListe(\stdClass $part1): object
{
    $belediyeKodu = $part1->belediyeKodu ?? '';
    $ayar         = trim($part1->ayar    ?? '');

    if (!$this->kimlikDogrula($part1->kullaniciAdi ?? '', $part1->sifre ?? '')) {
        $yanit = $this->ayarOkuListeYanit($belediyeKodu, '0001', 'Kullanıcı Adı veya Şifre Hatalı', []);
            $this->logService('ayarOkuListe', $part1, $yanit); // LOG
            return $yanit;
    }

    $detaylar = match ($ayar) {
        'katalogTaramaAramaTurleri' => [
            ['group' => '1', 'key' => '1', 'value' => 'Eser Adı'],
            ['group' => '1', 'key' => '2', 'value' => 'Yazar Adı'],
            ['group' => '1', 'key' => '3', 'value' => 'Yayınlayan'],
            ['group' => '1', 'key' => '4', 'value' => 'ISBN/ISSN'],
            ['group' => '1', 'key' => '5', 'value' => 'Sınıflama/Yer'],
        ],
        default => null,
    };

    if ($detaylar === null) {
        $yanit = $this->ayarOkuListeYanit($belediyeKodu, '0002', "Hatalı Parametre {$ayar}", []);
            $this->logService('ayarOkuListe', $part1, $yanit); // LOG
            return $yanit;
    }

    $liste = array_map(
        fn($d) => (object) ['group' => $d['group'], 'key' => $d['key'], 'value' => $d['value']],
        $detaylar
    );

    $yanit = $this->ayarOkuListeYanit($belediyeKodu, '0000', 'İşlem Başarılı', $liste);
        $this->logService('ayarOkuListe', $part1, $yanit); // LOG
        return $yanit;
}


// ─── Yardımcı: ayarOkuListeCType ─────────────────────────────────────────────
private function ayarOkuListeYanit(string $belediyeKodu, string $sonucKodu, string $sonucAciklamasi, array $detaylar): object
{
    return (object) [
        'belediyeKodu'    => $belediyeKodu,
        'sonucKodu'       => $sonucKodu,
        'sonucAciklamasi' => $sonucAciklamasi,
        'detayListesi'    => (object) ['detay' => $detaylar],
    ];
}

    // ─── Yardımcı: kimlik doğrulama ──────────────────────────────────────────────
    private function kimlikDogrula(string $kullaniciAdi, string $sifre): bool
    {
        return $kullaniciAdi === config('services.soap.kullanici_adi', 'admin')
            && $sifre        === config('services.soap.sifre',         'secret');
    }

    // ─── Yardımcı: kutuphaneSorgulaCType ─────────────────────────────────────────
    private function kutuphaneYanit(string $belediyeKodu, string $sonucKodu, string $sonucAciklamasi, array $liste): object
    {
        return (object) [
            'belediyeKodu'     => $belediyeKodu,
            'sonucKodu'        => $sonucKodu,
            'sonucAciklamasi'  => $sonucAciklamasi,
            'kutuphaneListesi' => (object) ['kutuphaneBilgileri' => $liste],
            'detayListesi'     => (object) ['detay' => []],
        ];
    }

    // ─── Yardımcı: katalogTaramaCType ────────────────────────────────────────────
    private function katalogYanit(string $belediyeKodu, string $sonucKodu, string $sonucAciklamasi, array $liste): object
    {
        return (object) [
            'belediyeKodu'       => $belediyeKodu,
            'sonucKodu'          => $sonucKodu,
            'sonucAciklamasi'    => $sonucAciklamasi,
            'taramaSonucListesi' => (object) ['taramaSonucu' => $liste],
            'detayListesi'       => (object) ['detay' => []],
        ];
    }
}
