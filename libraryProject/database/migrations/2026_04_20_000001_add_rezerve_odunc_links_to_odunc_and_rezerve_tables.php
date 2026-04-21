<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('odunc_islemleri') && ! Schema::hasColumn('odunc_islemleri', 'rezerve_id')) {
            Schema::table('odunc_islemleri', function (Blueprint $table) {
                $table->foreignId('rezerve_id')
                    ->nullable()
                    ->after('katalog_id')
                    ->constrained('uye_rezerve')
                    ->nullOnDelete();
            });
        }

        if (Schema::hasTable('uye_rezerve') && ! Schema::hasColumn('uye_rezerve', 'odunc_id')) {
            Schema::table('uye_rezerve', function (Blueprint $table) {
                $table->foreignId('odunc_id')
                    ->nullable()
                    ->after('katalog_id')
                    ->constrained('odunc_islemleri')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('uye_rezerve') && Schema::hasColumn('uye_rezerve', 'odunc_id')) {
            Schema::table('uye_rezerve', function (Blueprint $table) {
                $table->dropForeign(['odunc_id']);
                $table->dropColumn('odunc_id');
            });
        }

        if (Schema::hasTable('odunc_islemleri') && Schema::hasColumn('odunc_islemleri', 'rezerve_id')) {
            Schema::table('odunc_islemleri', function (Blueprint $table) {
                $table->dropForeign(['rezerve_id']);
                $table->dropColumn('rezerve_id');
            });
        }
    }
};
