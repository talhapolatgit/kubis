<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('katalog', 'ilkKayit')) {
            Schema::table('katalog', function (Blueprint $table) {
                $table->boolean('ilkKayit')->default(false)->after('kunyeISBNISSN');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('katalog', 'ilkKayit')) {
            Schema::table('katalog', function (Blueprint $table) {
                $table->dropColumn('ilkKayit');
            });
        }
    }
};
