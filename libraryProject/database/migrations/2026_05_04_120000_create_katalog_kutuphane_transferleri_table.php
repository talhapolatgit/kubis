<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('katalog_kutuphane_transferleri', function (Blueprint $table) {
            $table->id();
            // katalog.id ve kutuphane.id mevcut şemada int(11)
            $table->integer('katalog_id');
            $table->integer('from_kutuphane_id')->nullable();
            $table->integer('to_kutuphane_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();

            $table->foreign('katalog_id')->references('id')->on('katalog')->cascadeOnDelete();
            $table->foreign('from_kutuphane_id')->references('id')->on('kutuphane')->nullOnDelete();
            $table->foreign('to_kutuphane_id')->references('id')->on('kutuphane')->restrictOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->index(['katalog_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('katalog_kutuphane_transferleri');
    }
};
