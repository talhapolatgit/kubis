<?php

namespace App\Services\Sms;

use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

class SmsService
{
    public function __construct(
        private readonly SmsGatewayFactory $factory,
    ) {}

    /**
     * SMS gönderir, sonucu sms_logs tablosuna yazar.
     *
     * @return mixed Gateway payload (geriye dönük uyumluluk)
     */
    public function send(string $gsm, string $message, ?string $source = null): mixed
    {
        $httpStatus = null;
        $responseBody = null;
        $isSuccess = false;
        $result = null;

        try {
            $gatewayResult = $this->factory->make()->send((string) $gsm, (string) $message);

            $httpStatus = $gatewayResult->httpStatus;
            $responseBody = $gatewayResult->responseBody;
            $isSuccess = $gatewayResult->success;

            $payload = is_array($gatewayResult->payload) ? $gatewayResult->payload : [];
            $result = array_merge($payload, [
                'success' => $gatewayResult->success,
                'message' => $gatewayResult->success
                    ? ($payload['message'] ?? null)
                    : ($gatewayResult->errorMessage
                        ?? $payload['message']
                        ?? $payload['Message']
                        ?? 'SMS gönderilemedi. Lütfen tekrar deneyin.'),
            ]);
            // Başarılı yanıtta boş message alanını taşıma
            if ($result['message'] === null) {
                unset($result['message']);
            }
        } catch (Throwable $e) {
            $responseBody = $e->getMessage();
            $result = [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        } finally {
            DB::table('sms_logs')->insert([
                'gsm' => (string) $gsm,
                'message' => (string) $message,
                'is_success' => $isSuccess ? 1 : 0,
                'http_status' => $httpStatus,
                'response_body' => $responseBody,
                'source' => $source,
                'created_user' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return $result;
    }

    /**
     * Aktif SMS entegrasyonu yapılandırılmış mı?
     */
    public function isConfigured(): bool
    {
        try {
            $this->factory->make();

            return true;
        } catch (RuntimeException|Throwable) {
            return false;
        }
    }
}
