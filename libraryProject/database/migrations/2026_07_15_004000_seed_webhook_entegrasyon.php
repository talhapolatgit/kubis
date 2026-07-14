<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::table('entegrasyonlar')->where('tip', 'webhook')->exists()) {
            return;
        }

        $webhookUrl = (string) (
            config('services.webhook.url')
            ?: env('WEBHOOK_URL')
            ?: env('BEYOGLU_WEBHOOK_URL')
            ?: 'https://api.example.com/api/webhook/kutuphane/bildirim'
        );
        $secret = (string) (
            config('services.webhook.secret')
            ?: env('WEBHOOK_SECRET')
            ?: env('BEYOGLU_WEBHOOK_SECRET')
            ?: ''
        );

        $ayarlar = [
            'webhook_url' => $webhookUrl,
            'secret' => $secret,
        ];

        DB::table('entegrasyonlar')->insert([
            'tip' => 'webhook',
            'saglayici' => 'hmac',
            'aktif' => $secret !== '',
            'ayarlar' => Crypt::encryptString(json_encode($ayarlar, JSON_UNESCAPED_UNICODE)),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('entegrasyonlar')->where('tip', 'webhook')->delete();
    }
};
