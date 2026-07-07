<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ziyaretci_kayitlari', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('uye_id');
            $table->unsignedInteger('kutuphane_id');
            $table->dateTime('giris_saati');
            $table->dateTime('cikis_saati')->nullable();
            $table->text('notlar')->nullable();
            $table->unsignedBigInteger('created_user')->nullable();
            $table->unsignedBigInteger('updated_user')->nullable();
            $table->timestamps();

            $table->index('uye_id');
            $table->index('kutuphane_id');
            $table->index('giris_saati');
            $table->index('cikis_saati');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ziyaretci_kayitlari');
    }
};
