<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::table('entegrasyonlar')->where('tip', 'ldap')->exists()) {
            return;
        }

        $ayarlar = [
            'host' => (string) config('services.ldap.host', 'ldap://128.0.0.4:389'),
            'base_dn' => (string) config('services.ldap.base_dn', 'DC=beyoglu,DC=bel,DC=tr'),
            'protocol_version' => 3,
            'referrals' => false,
        ];

        DB::table('entegrasyonlar')->insert([
            'tip' => 'ldap',
            'saglayici' => 'active_directory',
            'aktif' => true,
            'ayarlar' => Crypt::encryptString(json_encode($ayarlar, JSON_UNESCAPED_UNICODE)),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('entegrasyonlar')->where('tip', 'ldap')->delete();
    }
};
