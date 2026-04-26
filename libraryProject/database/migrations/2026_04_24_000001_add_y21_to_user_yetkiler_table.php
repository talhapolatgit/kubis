<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('user_yetkiler') && !Schema::hasColumn('user_yetkiler', 'y21')) {
            Schema::table('user_yetkiler', function (Blueprint $table) {
                $table->boolean('y21')->default(false)->after('y20');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('user_yetkiler') && Schema::hasColumn('user_yetkiler', 'y21')) {
            Schema::table('user_yetkiler', function (Blueprint $table) {
                $table->dropColumn('y21');
            });
        }
    }
};
