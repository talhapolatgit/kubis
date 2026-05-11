<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `katalog_dil` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ad` varchar(100) NOT NULL,
  `sira` int(11) NOT NULL DEFAULT 0,
  `aktif` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_katalog_dil_ad` (`ad`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `katalog_dil` (`ad`, `sira`, `aktif`) VALUES
('Türkçe', 10, 1),
('İngilizce', 20, 1),
('Almanca', 30, 1),
('Fransızca', 40, 1),
('Arapça', 50, 1),
('İspanyolca', 60, 1),
('Rusça', 70, 1),
('Farsça', 80, 1),
('İtalyanca', 90, 1),
('Diğer', 100, 1);
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('katalog_dil');
    }
};
