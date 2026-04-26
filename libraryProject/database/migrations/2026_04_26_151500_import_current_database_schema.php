<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    public function up(): void
    {
        $dumpPath = base_path('kutu_librarydb.sql');

        if (! is_file($dumpPath)) {
            throw new RuntimeException("Schema dump file not found: {$dumpPath}");
        }

        $rawSql = file_get_contents($dumpPath);

        if ($rawSql === false) {
            throw new RuntimeException("Schema dump file could not be read: {$dumpPath}");
        }

        // Remove line comments, then execute statements one-by-one for stability.
        $rawSql = preg_replace('/^\s*--.*$/m', '', $rawSql) ?? $rawSql;
        $statements = preg_split('/;\s*\R/', $rawSql) ?: [];

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        foreach ($statements as $statement) {
            $statement = trim($statement);

            if ($statement === '') {
                continue;
            }

            $normalized = strtolower($statement);

            if (
                str_contains($normalized, '`migrations`') ||
                str_starts_with($normalized, 'set ') ||
                str_starts_with($normalized, 'start transaction') ||
                $normalized === 'commit' ||
                str_starts_with($normalized, '/*!')
            ) {
                continue;
            }

            DB::unprepared($statement.';');
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        if (! DB::table('users')->where('email', 'admin@xyz.com')->exists()) {
            DB::table('users')->insert([
                'name' => 'Admin',
                'email' => 'admin@xyz.com',
                'role' => 'admin',
                'password' => Hash::make('12345678'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        $databaseName = DB::getDatabaseName();
        $columnName = 'Tables_in_'.$databaseName;
        $tables = DB::select('SHOW TABLES');

        foreach ($tables as $row) {
            $table = (array) $row;
            $tableName = $table[$columnName] ?? reset($table);

            if ($tableName === 'migrations') {
                continue;
            }

            $safeTableName = str_replace('`', '``', (string) $tableName);
            DB::statement("DROP TABLE IF EXISTS `{$safeTableName}`");
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
};
