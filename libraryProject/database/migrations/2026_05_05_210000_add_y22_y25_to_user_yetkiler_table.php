<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('user_yetkiler', 'y22')) {
            Schema::table('user_yetkiler', function (Blueprint $table) {
                $table->boolean('y22')->default(false)->after('y21');
            });
        }
        if (! Schema::hasColumn('user_yetkiler', 'y23')) {
            Schema::table('user_yetkiler', function (Blueprint $table) {
                $table->boolean('y23')->default(false)->after('y22');
            });
        }
        if (! Schema::hasColumn('user_yetkiler', 'y24')) {
            Schema::table('user_yetkiler', function (Blueprint $table) {
                $table->boolean('y24')->default(false)->after('y23');
            });
        }
        if (! Schema::hasColumn('user_yetkiler', 'y25')) {
            Schema::table('user_yetkiler', function (Blueprint $table) {
                $table->boolean('y25')->default(false)->after('y24');
            });
        }
    }

    public function down(): void
    {
        foreach (['y25', 'y24', 'y23', 'y22'] as $col) {
            if (Schema::hasColumn('user_yetkiler', $col)) {
                Schema::table('user_yetkiler', function (Blueprint $table) use ($col) {
                    $table->dropColumn($col);
                });
            }
        }
    }
};
