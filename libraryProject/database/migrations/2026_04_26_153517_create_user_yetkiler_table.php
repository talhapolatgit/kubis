<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
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
  `y21` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  CONSTRAINT `fk_user_yetkiler_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('user_yetkiler');
    }
};