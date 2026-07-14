<?php

namespace App\Services;

use App\Services\Webhook\WebhookSettings;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class WebhookService
{
    /**
     * Bekleme listesindeki kullanıcılara push bildirim gönderir.
     *
     * @param  string[]  $tcList   TC kimlik numaraları
     * @param  string    $title    Bildirim başlığı
     * @param  string    $message  Bildirim mesajı
     * @return array{sentCount: int, failedCount: int, failedTcList: string[]}
     *
     * @throws \Exception  Webhook isteği başarısız olursa
     */
    public function sendBildirim(array $tcList, string $title, string $message): array
    {
        $settings = WebhookSettings::current();
        if (! $settings) {
            throw new RuntimeException('Webhook entegrasyonu aktif değil veya tanımlı değil.');
        }

        $webhookUrl = $settings['webhook_url'];
        $secretKey = $settings['secret'];

        // 1. Body'yi bir kez encode et — aynı string hem imzada hem HTTP gövdesinde kullanılacak
        $body = json_encode(
            ['tc_list' => $tcList, 'title' => $title, 'message' => $message],
            JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
        );

        // 2. Timestamp (saniye)
        $timestamp = (string) time();

        // 3. HMAC-SHA256 imza
        $signature = hash_hmac('sha256', "{$timestamp}.{$body}", $secretKey);

        // 4. İsteği gönder
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'x-signature' => $signature,
            'x-timestamp' => $timestamp,
        ])->withBody($body, 'application/json')->post($webhookUrl);

        if ($response->successful()) {
            Log::info('Webhook başarılı', ['sentCount' => $response->json('data.sentCount')]);

            return $response->json('data') ?? $response->json() ?? [];
        }

        Log::error('Webhook hatası', [
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        throw new \Exception(
            "Webhook hatası [{$response->status()}]: {$response->body()}"
        );
    }
}
