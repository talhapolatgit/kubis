<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `kutuphane_yetkili` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Birincil anahtar',
  `kutuphane_id` int(11) NOT NULL COMMENT 'kutuphane.id FK',
  `user_id` bigint(20) unsigned NOT NULL COMMENT 'users.id FK — yetkili kullanıcı',
  `created_by` bigint(20) unsigned NOT NULL COMMENT 'users.id FK — kaydı ekleyen kullanıcı',
  `deleted_by` bigint(20) unsigned DEFAULT NULL COMMENT 'users.id FK — kaydı silen kullanıcı',
  `created_at` datetime NOT NULL DEFAULT current_timestamp() COMMENT 'Ekleme tarihi',
  `deleted_at` datetime DEFAULT NULL COMMENT 'Soft-delete tarihi (NULL = aktif)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_kutuphane_user_active` (`kutuphane_id`,`user_id`,`deleted_at`),
  KEY `idx_ky_kutuphane` (`kutuphane_id`),
  KEY `idx_ky_user` (`user_id`),
  KEY `idx_ky_created_by` (`created_by`),
  KEY `idx_ky_deleted_by` (`deleted_by`),
  KEY `idx_ky_deleted_at` (`deleted_at`),
  CONSTRAINT `fk_ky_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_ky_deleted_by` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_ky_kutuphane` FOREIGN KEY (`kutuphane_id`) REFERENCES `kutuphane` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ky_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Kütüphane bazında yetkili kullanıcı ilişkisi (soft-delete)'
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('kutuphane_yetkili');
    }
};