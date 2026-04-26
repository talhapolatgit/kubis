<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `uyeler` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tc_kimlik` varchar(11) NOT NULL,
  `dogum_tarihi` date NOT NULL,
  `ad` varchar(100) NOT NULL,
  `soyad` varchar(100) NOT NULL,
  `cinsiyet` varchar(16) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `telefon` varchar(20) NOT NULL,
  `telefon2` varchar(30) DEFAULT NULL,
  `telefon_dogrulandi` tinyint(1) NOT NULL DEFAULT 0,
  `il` varchar(100) DEFAULT NULL,
  `ilce` varchar(100) DEFAULT NULL,
  `mahalle` varchar(150) DEFAULT NULL,
  `acik_adres` text DEFAULT NULL,
  `ogretim_durumu` varchar(50) DEFAULT NULL,
  `okul_adi` varchar(200) DEFAULT NULL,
  `bolum_adi` varchar(200) DEFAULT NULL,
  `veli_ad` varchar(200) DEFAULT NULL,
  `veli_soyad` varchar(200) DEFAULT NULL,
  `veli_tc_kimlik` varchar(15) DEFAULT NULL,
  `veli_dogum_tarihi` date DEFAULT NULL,
  `veli_telefon` varchar(50) DEFAULT NULL,
  `statu` enum('aktif','pasif') NOT NULL DEFAULT 'aktif',
  `uyelik_baslangic` date DEFAULT NULL,
  `uyelik_bitis` date DEFAULT NULL,
  `notlar` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uyeler_tc_kimlik_unique` (`tc_kimlik`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('uyeler');
    }
};