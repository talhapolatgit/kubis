<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('uyeler', function (Blueprint $table) {
            if (! Schema::hasColumn('uyeler', 'veli_ad')) {
                $table->string('veli_ad', 100)->nullable();
            }
            if (! Schema::hasColumn('uyeler', 'veli_soyad')) {
                $table->string('veli_soyad', 100)->nullable();
            }
            if (! Schema::hasColumn('uyeler', 'veli_tc_kimlik')) {
                $table->string('veli_tc_kimlik', 11)->nullable();
            }
            if (! Schema::hasColumn('uyeler', 'veli_dogum_tarihi')) {
                $table->date('veli_dogum_tarihi')->nullable();
            }
            if (! Schema::hasColumn('uyeler', 'veli_telefon')) {
                $table->string('veli_telefon', 20)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('uyeler', function (Blueprint $table) {
            $columns = ['veli_ad', 'veli_soyad', 'veli_tc_kimlik', 'veli_dogum_tarihi', 'veli_telefon'];
            foreach ($columns as $col) {
                if (Schema::hasColumn('uyeler', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
