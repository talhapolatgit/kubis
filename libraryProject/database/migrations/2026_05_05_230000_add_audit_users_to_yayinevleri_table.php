<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('yayinevleri', 'created_by')) {
            Schema::table('yayinevleri', function (Blueprint $table) {
                $table->foreignId('created_by')->nullable()->after('updated_at')->constrained('users')->nullOnDelete();
            });
        }
        if (! Schema::hasColumn('yayinevleri', 'updated_by')) {
            Schema::table('yayinevleri', function (Blueprint $table) {
                $table->foreignId('updated_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::table('yayinevleri', function (Blueprint $table) {
            if (Schema::hasColumn('yayinevleri', 'updated_by')) {
                $table->dropConstrainedForeignId('updated_by');
            }
            if (Schema::hasColumn('yayinevleri', 'created_by')) {
                $table->dropConstrainedForeignId('created_by');
            }
        });
    }
};
