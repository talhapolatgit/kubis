<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `girisTuru` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ad` varchar(100) NOT NULL COMMENT 'Giriş türü adı (Satın alma, Hibe, Bağış, Protokol, Diğer)',
  `sira` int(11) NOT NULL DEFAULT 0 COMMENT 'Sıralama önceliği',
  `aktif` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1 = Aktif, 0 = Pasif',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Kütüphaneye eser giriş türleri'
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('giristuru');
    }
};