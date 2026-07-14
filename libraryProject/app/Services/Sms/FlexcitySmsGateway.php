<?php

namespace App\Services\Sms;

use Illuminate\Support\Facades\Http;
use Throwable;

class FlexcitySmsGateway implements SmsGatewayInterface
{
    /**
     * @param  array<string, mixed>  $ayarlar
     */
    public function __construct(
        private readonly array $ayarlar,
    ) {}

    public function send(string $gsm, string $message): SmsResult
    {
        $baseUrl = trim((string) ($this->ayarlar['base_url'] ?? ''));
        $authorization = (string) ($this->ayarlar['authorization'] ?? '');
        $contentType = (string) ($this->ayarlar['content_type'] ?? 'application/json');
        $verifySsl = (bool) ($this->ayarlar['verify_ssl'] ?? false);

        if ($baseUrl === '' || $authorization === '') {
            return SmsResult::failure('Flexcity SMS ayarları eksik (base_url / authorization).');
        }

        $body = 'muhatapIdList=[]&hizliGonder=true&gsmList=[' . $gsm . ']&content=' . $message;

        try {
            $request = Http::withHeaders([
                'Authorization' => $authorization,
                'Content-Type' => $contentType,
            ])->withBody($body, $contentType);

            if (! $verifySsl) {
                $request = $request->withoutVerifying();
            }

            $response = $request->post($baseUrl);

            return new SmsResult(
                success: $response->successful(),
                httpStatus: $response->status(),
                responseBody: $response->body(),
                payload: $response->json() ?? [
                    'success' => $response->successful(),
                    'body' => $response->body(),
                ],
            );
        } catch (Throwable $e) {
            return SmsResult::failure($e->getMessage());
        }
    }
}
