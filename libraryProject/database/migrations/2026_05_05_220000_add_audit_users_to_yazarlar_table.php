<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('yazarlar', 'created_by')) {
            Schema::table('yazarlar', function (Blueprint $table) {
                $table->foreignId('created_by')->nullable()->after('updated_at')->constrained('users')->nullOnDelete();
            });
        }
        if (! Schema::hasColumn('yazarlar', 'updated_by')) {
            Schema::table('yazarlar', function (Blueprint $table) {
                $table->foreignId('updated_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::table('yazarlar', function (Blueprint $table) {
            if (Schema::hasColumn('yazarlar', 'updated_by')) {
                $table->dropConstrainedForeignId('updated_by');
            }
            if (Schema::hasColumn('yazarlar', 'created_by')) {
                $table->dropConstrainedForeignId('created_by');
            }
        });
    }
};
