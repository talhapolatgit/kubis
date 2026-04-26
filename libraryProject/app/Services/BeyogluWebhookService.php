<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BeyogluWebhookService
{
    private ?string $webhookUrl;
    private ?string $secretKey;

    public function __construct()
    {
        $this->webhookUrl = config('services.beyoglu.webhook_url');
        $this->secretKey  = config('services.beyoglu.webhook_secret');
    }

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
        // 1. Body'yi bir kez encode et — aynı string hem imzada hem HTTP gövdesinde kullanılacak
        $body = json_encode(
            ['tc_list' => $tcList, 'title' => $title, 'message' => $message],
            JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
        );

        // 2. Timestamp (saniye)
        $timestamp = (string) time();

        // 3. HMAC-SHA256 imza
        $signature = hash_hmac('sha256', "{$timestamp}.{$body}", $this->secretKey);

        // 4. İsteği gönder
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'x-signature'  => $signature,
            'x-timestamp'  => $timestamp,
        ])->withBody($body, 'application/json')->post($this->webhookUrl);

        if ($response->successful()) {
            Log::info('Beyoglu webhook başarılı', ['sentCount' => $response->json('data.sentCount')]);
            return $response->json('data') ?? $response->json() ?? [];
        }

        Log::error('Beyoglu webhook hatası', [
            'status' => $response->status(),
            'body'   => $response->body(),
        ]);

        throw new \Exception(
            "Beyoglu webhook hatası [{$response->status()}]: {$response->body()}"
        );
    }
}
