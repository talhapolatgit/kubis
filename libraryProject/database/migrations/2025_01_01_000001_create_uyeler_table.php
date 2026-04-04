<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('uyeler', function (Blueprint $table) {
            $table->id();

            // Kimlik
            $table->string('tc_kimlik', 11)->unique();
            $table->date('dogum_tarihi');

            // Kişisel
            $table->string('ad', 100);
            $table->string('soyad', 100);
            $table->string('email', 255)->nullable();
            $table->string('telefon', 20);
            $table->boolean('telefon_dogrulandi')->default(false);

            // Adres
            $table->string('il', 100)->nullable();
            $table->string('ilce', 100)->nullable();
            $table->string('mahalle', 150)->nullable();
            $table->text('acik_adres')->nullable();

            // Üyelik
            $table->enum('statu', ['aktif', 'pasif'])->default('aktif');
            $table->date('uyelik_baslangic')->nullable();
            $table->date('uyelik_bitis')->nullable();
            $table->text('notlar')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('uyeler');
    }
};
