<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('koleksiyon', function (Blueprint $table) {
            $dropColumns = [];
            if (Schema::hasColumn('koleksiyon', 'created_user')) {
                $dropColumns[] = 'created_user';
            }
            if (Schema::hasColumn('koleksiyon', 'updated_user')) {
                $dropColumns[] = 'updated_user';
            }
            if ($dropColumns !== []) {
                $table->dropColumn($dropColumns);
            }
        });
    }

    public function down(): void
    {
        Schema::table('koleksiyon', function (Blueprint $table) {
            if (! Schema::hasColumn('koleksiyon', 'created_user')) {
                $table->unsignedBigInteger('created_user')->nullable()->after('statu');
            }
            if (! Schema::hasColumn('koleksiyon', 'updated_user')) {
                $table->unsignedBigInteger('updated_user')->nullable()->after('created_user');
            }
        });
    }
};
