<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('uye_rezerve') && ! Schema::hasColumn('uye_rezerve', 'iptalEdenUserId')) {
            Schema::table('uye_rezerve', function (Blueprint $table) {
                $table->foreignId('iptalEdenUserId')
                    ->nullable()
                    ->after('iptalMi')
                    ->constrained('users')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('uye_rezerve') && Schema::hasColumn('uye_rezerve', 'iptalEdenUserId')) {
            Schema::table('uye_rezerve', function (Blueprint $table) {
                $table->dropForeign(['iptalEdenUserId']);
                $table->dropColumn('iptalEdenUserId');
            });
        }
    }
};
