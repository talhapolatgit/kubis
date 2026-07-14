<?php

namespace App\Http\Controllers;

use App\Models\Entegrasyon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

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
            'message' => $ham['message'] ?? 'Kimlik doğrulaması başarısız. Lütfen bilgileri kontrol edin.',
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
    public static function kimlikSorgula($dogumTarihi, $tcKimlikNo)
    {
        $entegrasyon = Entegrasyon::kimlik();
        if (! $entegrasyon) {
            return [
                'success' => false,
                'message' => 'Kimlik sorgulama entegrasyonu aktif değil veya tanımlı değil.',
            ];
        }

        $ayarlar = is_array($entegrasyon->ayarlar) ? $entegrasyon->ayarlar : [];
        $baseUrl = trim((string) ($ayarlar['base_url'] ?? ''));
        $authorization = (string) ($ayarlar['authorization'] ?? '');
        $contentType = (string) ($ayarlar['content_type'] ?? 'application/json');
        $verifySsl = (bool) ($ayarlar['verify_ssl'] ?? false);

        if ($baseUrl === '' || $authorization === '') {
            return [
                'success' => false,
                'message' => 'Kimlik sorgulama entegrasyon ayarları eksik.',
            ];
        }

        $body = "dogumTarihi={$dogumTarihi}T00:00:00+02:00&tcKimlikNo={$tcKimlikNo}";

        try {
            $request = Http::withHeaders([
                'Authorization' => $authorization,
                'Content-Type' => $contentType,
            ])->withBody($body, $contentType);

            if (! $verifySsl) {
                $request = $request->withoutVerifying();
            }

            $response = $request->post($baseUrl);

            return $response->json() ?? [
                'success' => false,
                'message' => 'Kimlik servisinden geçersiz yanıt alındı.',
            ];
        } catch (Throwable $e) {
            Log::error('Kimlik sorgulama hatası: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Kimlik sorgulama hatası: ' . $e->getMessage(),
            ];
        }
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
        $entegrasyon = Entegrasyon::adres();
        if (! $entegrasyon) {
            return [
                'success' => false,
                'message' => 'Adres sorgulama entegrasyonu aktif değil veya tanımlı değil.',
            ];
        }

        $ayarlar = is_array($entegrasyon->ayarlar) ? $entegrasyon->ayarlar : [];
        $baseUrl = trim((string) ($ayarlar['base_url'] ?? ''));
        $authorization = (string) ($ayarlar['authorization'] ?? '');
        $contentType = (string) ($ayarlar['content_type'] ?? 'application/json');
        $verifySsl = (bool) ($ayarlar['verify_ssl'] ?? false);

        if ($baseUrl === '' || $authorization === '') {
            return [
                'success' => false,
                'message' => 'Adres sorgulama entegrasyon ayarları eksik.',
            ];
        }

        try {
            $body = "dogumTarihi={$dogumTarihi}T00:00:00+02:00&tcKimlikNo={$tcKimlikNo}";

            $request = Http::withHeaders([
                'Authorization' => $authorization,
                'Content-Type' => $contentType,
            ])->withBody($body, $contentType);

            if (! $verifySsl) {
                $request = $request->withoutVerifying();
            }

            $response = $request->post($baseUrl);
            $data = $response->json();

            if (isset($data['baseAdresDtoList'][0])) {
                $a = $data['baseAdresDtoList'][0];

                return [
                    'success' => true,
                    'ikametEdiyor' => ($a['ilceAdi'] ?? '') === 'BEYOĞLU',
                    'adres' => $a['ilceAdi'] ?? '',
                    'ilAdi' => $a['ilAdi'] ?? '',
                    'ilceAdi' => $a['ilceAdi'] ?? '',
                    'mahalleAdi' => $a['mahalleAdi'] ?? '',
                    'kapi' => $a['kapi'] ?? '',
                    'daire' => $a['daire'] ?? '',
                    'sokakAdi' => $a['sokakAdi'] ?? $a['sokak'] ?? '',
                ];
            }
        } catch (Throwable $e) {
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
