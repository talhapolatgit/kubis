<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('entegrasyonlar', function (Blueprint $table) {
            $table->id();
            $table->string('tip', 50)->unique();
            $table->string('saglayici', 50);
            $table->boolean('aktif')->default(true);
            $table->text('ayarlar')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });

        $ayarlar = [
            'base_url' => 'https://servis.beyoglu.bel.tr/FlexCityUi/rest/json/sms/SendSms',
            'authorization' => 'applicationkey=BRIDGE,requestdate=2022-07-21T15:55:51+03:00,md5hashcode=9278682f6caad7c8fa5ba3f330a3bfb3',
            'content_type' => 'application/json',
            'verify_ssl' => false,
        ];

        DB::table('entegrasyonlar')->insert([
            'tip' => 'sms',
            'saglayici' => 'flexcity',
            'aktif' => true,
            'ayarlar' => Crypt::encryptString(json_encode($ayarlar, JSON_UNESCAPED_UNICODE)),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('entegrasyonlar');
    }
};
