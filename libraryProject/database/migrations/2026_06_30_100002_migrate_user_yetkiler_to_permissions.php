<?php

use App\Models\Permission;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        foreach (Permission::definitionsFromConfig() as $row) {
            DB::table('permissions')->insert([
                'legacy_no'  => $row['legacy_no'],
                'slug'       => $row['slug'],
                'label'      => $row['label'],
                'sort_order' => $row['sort_order'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        if (! Schema::hasTable('user_yetkiler')) {
            return;
        }

        $permissionIds = DB::table('permissions')->pluck('id', 'legacy_no');
        $rows = DB::table('user_yetkiler')->get();

        foreach ($rows as $row) {
            for ($i = 1; $i <= 26; $i++) {
                $col = 'y' . str_pad((string) $i, 2, '0', STR_PAD_LEFT);
                if (! property_exists($row, $col) || ! (bool) $row->{$col}) {
                    continue;
                }

                $permissionId = $permissionIds[$i] ?? null;
                if (! $permissionId) {
                    continue;
                }

                DB::table('user_permission')->insert([
                    'user_id'       => $row->user_id,
                    'permission_id' => $permissionId,
                    'granted_by'    => null,
                    'created_at'    => $row->updated_at ?? $row->created_at ?? $now,
                    'updated_at'    => $row->updated_at ?? $now,
                ]);
            }
        }

        Schema::dropIfExists('user_yetkiler');
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `user_yetkiler` (
  `user_id` bigint(20) unsigned NOT NULL,
  `y01` tinyint(1) NOT NULL DEFAULT 0,
  `y02` tinyint(1) NOT NULL DEFAULT 0,
  `y03` tinyint(1) NOT NULL DEFAULT 0,
  `y04` tinyint(1) NOT NULL DEFAULT 0,
  `y05` tinyint(1) NOT NULL DEFAULT 0,
  `y06` tinyint(1) NOT NULL DEFAULT 0,
  `y07` tinyint(1) NOT NULL DEFAULT 0,
  `y08` tinyint(1) NOT NULL DEFAULT 0,
  `y09` tinyint(1) NOT NULL DEFAULT 0,
  `y10` tinyint(1) NOT NULL DEFAULT 0,
  `y11` tinyint(1) NOT NULL DEFAULT 0,
  `y12` tinyint(1) NOT NULL DEFAULT 0,
  `y13` tinyint(1) NOT NULL DEFAULT 0,
  `y14` tinyint(1) NOT NULL DEFAULT 0,
  `y15` tinyint(1) NOT NULL DEFAULT 0,
  `y16` tinyint(1) NOT NULL DEFAULT 0,
  `y17` tinyint(1) NOT NULL DEFAULT 0,
  `y18` tinyint(1) NOT NULL DEFAULT 0,
  `y19` tinyint(1) NOT NULL DEFAULT 0,
  `y20` tinyint(1) NOT NULL DEFAULT 0,
  `y21` tinyint(1) NOT NULL DEFAULT 0,
  `y22` tinyint(1) NOT NULL DEFAULT 0,
  `y23` tinyint(1) NOT NULL DEFAULT 0,
  `y24` tinyint(1) NOT NULL DEFAULT 0,
  `y25` tinyint(1) NOT NULL DEFAULT 0,
  `y26` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  CONSTRAINT `fk_user_yetkiler_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);

        $permissionMap = DB::table('permissions')->pluck('legacy_no', 'id');
        $userPermissions = DB::table('user_permission')->get()->groupBy('user_id');

        foreach ($userPermissions as $userId => $items) {
            $data = [
                'user_id'    => $userId,
                'created_at' => $items->min('created_at'),
                'updated_at' => $items->max('updated_at'),
            ];

            for ($i = 1; $i <= 26; $i++) {
                $col = 'y' . str_pad((string) $i, 2, '0', STR_PAD_LEFT);
                $data[$col] = 0;
            }

            foreach ($items as $item) {
                $legacyNo = $permissionMap[$item->permission_id] ?? null;
                if ($legacyNo) {
                    $col = 'y' . str_pad((string) $legacyNo, 2, '0', STR_PAD_LEFT);
                    $data[$col] = 1;
                }
            }

            DB::table('user_yetkiler')->insert($data);
        }

        DB::table('user_permission')->delete();
        DB::table('permissions')->delete();
    }
};
