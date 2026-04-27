<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('tc_kimlik', 11)->nullable()->unique()->after('name');
            $table->date('dogum_tarihi')->nullable()->after('tc_kimlik');
            $table->string('ad', 100)->nullable()->after('dogum_tarihi');
            $table->string('soyad', 100)->nullable()->after('ad');
            $table->string('cinsiyet', 20)->nullable()->after('soyad');
            $table->string('telefon', 20)->nullable()->after('email');
            $table->string('il', 100)->nullable()->after('telefon');
            $table->string('ilce', 100)->nullable()->after('il');
            $table->string('mahalle', 150)->nullable()->after('ilce');
            $table->text('acik_adres')->nullable()->after('mahalle');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique('users_tc_kimlik_unique');
            $table->dropColumn([
                'tc_kimlik',
                'dogum_tarihi',
                'ad',
                'soyad',
                'cinsiyet',
                'telefon',
                'il',
                'ilce',
                'mahalle',
                'acik_adres',
            ]);
        });
    }
};
