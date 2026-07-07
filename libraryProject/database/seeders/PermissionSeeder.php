<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        foreach (Permission::definitionsFromConfig() as $row) {
            DB::table('permissions')->updateOrInsert(
                ['legacy_no' => $row['legacy_no']],
                [
                    'slug'       => $row['slug'],
                    'label'      => $row['label'],
                    'sort_order' => $row['sort_order'],
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }

        $this->command?->info('✓ Yetki tanımları yüklendi.');
    }
}
