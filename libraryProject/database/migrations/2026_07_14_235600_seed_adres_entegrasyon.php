<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::table('entegrasyonlar')->where('tip', 'adres')->exists()) {
            return;
        }

        $ayarlar = [
            'base_url' => 'https://10.40.8.16/FlexCityUi/rest/json/nvi/FindAllBaseAdresDto',
            'authorization' => 'applicationkey=BRIDGE,requestdate=2022-07-21T15:55:51+03:00,md5hashcode=9278682f6caad7c8fa5ba3f330a3bfb3',
            'content_type' => 'application/json',
            'verify_ssl' => false,
        ];

        DB::table('entegrasyonlar')->insert([
            'tip' => 'adres',
            'saglayici' => 'flexcity',
            'aktif' => true,
            'ayarlar' => Crypt::encryptString(json_encode($ayarlar, JSON_UNESCAPED_UNICODE)),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('entegrasyonlar')->where('tip', 'adres')->delete();
    }
};
