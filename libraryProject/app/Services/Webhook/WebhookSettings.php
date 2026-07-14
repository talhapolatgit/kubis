<?php

namespace App\Services\Webhook;

use App\Models\Entegrasyon;

class WebhookSettings
{
    /**
     * Aktif webhook entegrasyon ayarlarını döndürür.
     *
     * @return array{webhook_url: string, secret: string}|null
     */
    public static function current(): ?array
    {
        $entegrasyon = Entegrasyon::webhook();
        if (! $entegrasyon) {
            return null;
        }

        $ayarlar = is_array($entegrasyon->ayarlar) ? $entegrasyon->ayarlar : [];
        $webhookUrl = trim((string) ($ayarlar['webhook_url'] ?? ''));
        $secret = trim((string) ($ayarlar['secret'] ?? ''));

        if ($webhookUrl === '' || $secret === '') {
            return null;
        }

        return [
            'webhook_url' => $webhookUrl,
            'secret' => $secret,
        ];
    }
}
