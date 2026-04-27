<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('katalog_yazarlar', function (Blueprint $table) {
            $table->id();
            // katalog.id imzalı INT — FK ile aynı işaret/dokuş
            $table->integer('katalog_id');
            $table->unsignedInteger('yazar_id');
            $table->unsignedSmallInteger('sira')->default(0);
            $table->timestamps();

            $table->unique(['katalog_id', 'yazar_id'], 'uq_katalog_yazarlar_katalog_yazar');
            $table->index('yazar_id', 'idx_katalog_yazarlar_yazar_id');

            $table->foreign('katalog_id')
                ->references('id')
                ->on('katalog')
                ->cascadeOnDelete();

            $table->foreign('yazar_id')
                ->references('id')
                ->on('yazarlar')
                ->cascadeOnDelete();
        });

        DB::statement('
            INSERT INTO katalog_yazarlar (katalog_id, yazar_id, sira, created_at, updated_at)
            SELECT id, yazarId, 0, NOW(), NOW()
            FROM katalog
            WHERE yazarId IS NOT NULL
        ');
    }

    public function down(): void
    {
        Schema::dropIfExists('katalog_yazarlar');
    }
};
