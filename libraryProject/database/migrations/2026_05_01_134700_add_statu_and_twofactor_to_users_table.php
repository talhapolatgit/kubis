<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'statu')) {
                $table->enum('statu', ['aktif', 'pasif'])
                    ->default('aktif')
                    ->after('role');
            }

            if (!Schema::hasColumn('users', 'twofactor')) {
                $table->boolean('twofactor')
                    ->default(false)
                    ->after('statu');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'twofactor')) {
                $table->dropColumn('twofactor');
            }

            if (Schema::hasColumn('users', 'statu')) {
                $table->dropColumn('statu');
            }
        });
    }
};
