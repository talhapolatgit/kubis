<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('yazarlar', function (Blueprint $table) {
            $table->string('fotograf_path', 500)->nullable()->after('siralama_adi');
        });
    }

    public function down(): void
    {
        Schema::table('yazarlar', function (Blueprint $table) {
            $table->dropColumn('fotograf_path');
        });
    }
};

