<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * @var string[]
     */
    private array $tables = ['tur', 'alttur', 'sekil', 'ortam', 'katalog_dil', 'koleksiyon'];

    public function up(): void
    {
        foreach ($this->tables as $tableName) {
            if (! Schema::hasColumn($tableName, 'created_by')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->foreignId('created_by')->nullable()->after('updated_at')->constrained('users')->nullOnDelete();
                });
            }
            if (! Schema::hasColumn($tableName, 'updated_by')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->foreignId('updated_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
                });
            }
        }

        // Koleksiyon tablosunda eskiden created_user/updated_user kullanılmış olabilir.
        if (Schema::hasColumn('koleksiyon', 'created_user') && Schema::hasColumn('koleksiyon', 'created_by')) {
            DB::table('koleksiyon')
                ->whereNull('created_by')
                ->whereNotNull('created_user')
                ->update(['created_by' => DB::raw('created_user')]);
        }
        if (Schema::hasColumn('koleksiyon', 'updated_user') && Schema::hasColumn('koleksiyon', 'updated_by')) {
            DB::table('koleksiyon')
                ->whereNull('updated_by')
                ->whereNotNull('updated_user')
                ->update(['updated_by' => DB::raw('updated_user')]);
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (Schema::hasColumn($tableName, 'updated_by')) {
                    $table->dropConstrainedForeignId('updated_by');
                }
                if (Schema::hasColumn($tableName, 'created_by')) {
                    $table->dropConstrainedForeignId('created_by');
                }
            });
        }
    }
};
