<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('user_yetkiler', 'y26')) {
            Schema::table('user_yetkiler', function (Blueprint $table) {
                $table->boolean('y26')->default(false)->after('y25');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('user_yetkiler', 'y26')) {
            Schema::table('user_yetkiler', function (Blueprint $table) {
                $table->dropColumn('y26');
            });
        }
    }
};
