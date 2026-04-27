<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('katalog', function (Blueprint $table) {
            $table->unsignedBigInteger('koleksiyon_id')->nullable()->after('kunyeSiniflamaYer');
        });
    }

    public function down(): void
    {
        Schema::table('katalog', function (Blueprint $table) {
            $table->dropColumn('koleksiyon_id');
        });
    }
};
