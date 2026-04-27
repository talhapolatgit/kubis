<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('uyeler', function (Blueprint $table) {
            $table->unsignedBigInteger('created_user')->nullable()->after('notlar');
            $table->unsignedBigInteger('updated_user')->nullable()->after('created_user');
        });
    }

    public function down(): void
    {
        Schema::table('uyeler', function (Blueprint $table) {
            $table->dropColumn(['created_user', 'updated_user']);
        });
    }
};
