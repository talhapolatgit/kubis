<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('odunc_islemleri', function (Blueprint $table) {
            $table->id();

            // İlişkiler
            $table->foreignId('uye_id')->constrained('uyeler')->cascadeOnDelete();
            $table->foreignId('katalog_id')->constrained('katalog')->cascadeOnDelete();
            $table->foreignId('kutuphane_id')->nullable()->constrained('kutuphane')->nullOnDelete();

            // Tarihler
            $table->date('odunc_tarihi');
            $table->date('iade_tarihi_planlanan');       // Planlanan iade
            $table->date('iade_tarihi_gercek')->nullable(); // Gerçekleşen iade

            // Durum: aktif | iade_edildi | kayip
            $table->enum('statu', ['aktif', 'iade_edildi', 'kayip'])->default('aktif');

            // İşlemi yapan personel
            $table->foreignId('odunc_veren_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('iade_alan_id')->nullable()->constrained('users')->nullOnDelete();

            $table->text('notlar')->nullable();
            $table->text('iade_notu')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('odunc_islemleri');
    }
};
