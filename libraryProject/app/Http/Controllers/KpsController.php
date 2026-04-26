<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class KpsController extends Controller
{
    public function kimlikSorgulaHttp(Request $request)
    {
        $tcKimlikNo  = trim($request->input('tc_kimlik', ''));
        $dogumTarihi = trim($request->input('dogum_tarihi', ''));

        if (!$tcKimlikNo || !$dogumTarihi) {
            return response()->json([
                'success' => false,
                'message' => 'TC Kimlik No ve Doğum Tarihi gereklidir.',
            ], 422);
        }

        if (strlen($tcKimlikNo) !== 11 || !ctype_digit($tcKimlikNo)) {
            return response()->json([
                'success' => false,
                'message' => 'Geçerli bir TC Kimlik No giriniz (11 rakam).',
            ], 422);
        }

        $ham = static::kimlikSorgula($dogumTarihi, $tcKimlikNo);

        // Servisten başarılı yanıt geldiyse ad/soyad çıkar
        if (isset($ham['success']) && $ham['success'] === true && isset($ham['sbsKisiDto'])) {
            $rawCinsiyet = $ham['sbsKisiDto']['cinsiyeti'] ?? $ham['sbsKisiDto']['cinsiyet'] ?? null;
            return response()->json([
                'success' => true,
                'ad'      => $ham['sbsKisiDto']['adi']     ?? '',
                'soyad'   => $ham['sbsKisiDto']['soyadi']  ?? '',
                'cinsiyet'=> $this->normalizeCinsiyet($rawCinsiyet),
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Kimlik doğrulaması başarısız. Lütfen bilgileri kontrol edin.',
        ], 422);
    }

    private function normalizeCinsiyet(?string $raw): ?string
    {
        if ($raw === null) {
            return null;
        }

        $value = mb_strtoupper(trim($raw), 'UTF-8');

        return match ($value) {
            'ERKEK' => 'erkek',
            'KADIN' => 'kadin',
            default => null,
        };
    }

    // ─── Kimlik Sorgula ──────────────────────────────────────────────────────────
    public static function kimlikSorgula($dogumTarihi,$tcKimlikNo)
    {
        // Postman'daki birebir body string'i
        $body = "dogumTarihi={$dogumTarihi}T00:00:00+02:00&tcKimlikNo={$tcKimlikNo}";

        $response = Http::withHeaders([
            'Authorization' => 'applicationkey=BRIDGE,requestdate=2022-07-21T15:55:51+03:00,md5hashcode=9278682f6caad7c8fa5ba3f330a3bfb3',
            'Content-Type'  => 'application/json',
        ])
            ->withBody($body, 'application/json') // Kritik nokta: Veriyi olduğu gibi (raw) gönderiyoruz
            ->withoutVerifying()
            ->post('https://10.40.8.16/FlexCityUi/rest/json/sbs/FindSbsKisiDtoByNvi');

        return $response->json();
    }

    public function adresSorgulaHttp(Request $request)
    {
        $tcKimlikNo  = trim($request->input('tc_kimlik', ''));
        $dogumTarihi = trim($request->input('dogum_tarihi', ''));

        if (!$tcKimlikNo || !$dogumTarihi) {
            return response()->json([
                'success' => false,
                'message' => 'TC Kimlik No ve Doğum Tarihi gereklidir.',
            ], 422);
        }

        if (strlen($tcKimlikNo) !== 11 || !ctype_digit($tcKimlikNo)) {
            return response()->json([
                'success' => false,
                'message' => 'Geçerli bir TC Kimlik No giriniz (11 rakam).',
            ], 422);
        }

        $sonuc = static::adresSorgula($tcKimlikNo, $dogumTarihi);

        return response()->json($sonuc);
    }

    // ─── Adres Sorgula (Core — diğer controller'lardan da çağrılabilir) ─────────
    public static function adresSorgula(string $tcKimlikNo, string $dogumTarihi): array
    {
        try {
        $body = "dogumTarihi={$dogumTarihi}T00:00:00+02:00&tcKimlikNo={$tcKimlikNo}";
        $response = Http::withHeaders([
            'Authorization' => 'applicationkey=BRIDGE,requestdate=2022-07-21T15:55:51+03:00,md5hashcode=9278682f6caad7c8fa5ba3f330a3bfb3',
            'Content-Type'  => 'application/json',
        ])
            ->withBody($body, 'application/json')
            ->withoutVerifying()
            ->post('https://10.40.8.16/FlexCityUi/rest/json/nvi/FindAllBaseAdresDto');
        
            $data = $response->json();
            if (isset($data['baseAdresDtoList'][0])) {
                $a = $data['baseAdresDtoList'][0];

                return [
                    'success'      => true,
                    'ikametEdiyor' => ($a['ilceAdi'] ?? '') === 'BEYOĞLU',
                    'adres'        => $a['ilceAdi'] ?? '',
                    'ilAdi'        => $a['ilAdi'] ?? '',
                    'ilceAdi'      => $a['ilceAdi'] ?? '',
                    'mahalleAdi'   => $a['mahalleAdi'] ?? '',
                    'kapi'         => $a['kapi'] ?? '',
                    'daire'        => $a['daire'] ?? '',
                    'sokakAdi'     => $a['sokakAdi'] ?? $a['sokak'] ?? '',
                ];
            }
        } catch (\Exception $e) {
            Log::error('Adres sorgulama hatası: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Adres sorgulama hatası: ' . $e->getMessage(),
            ];
        }
        return [
            'success' => false,
            'message' => 'Adres bulunamadı.',
        ];
    }
}
