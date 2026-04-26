<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `yayinevleri` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ad` varchar(255) NOT NULL COMMENT 'Yayınevi adı',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_yayinevleri_ad` (`ad`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Yayınevi kaydı — katalog.yayineviId FK'
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('yayinevleri');
    }
};