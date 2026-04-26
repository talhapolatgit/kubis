<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `odunc_islemleri` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uye_id` bigint(20) unsigned NOT NULL,
  `katalog_id` bigint(20) unsigned NOT NULL,
  `kutuphane_id` bigint(20) unsigned DEFAULT NULL,
  `odunc_tarihi` date NOT NULL,
  `iade_tarihi_planlanan` date NOT NULL,
  `iade_tarihi_gercek` date DEFAULT NULL,
  `statu` enum('aktif','iade_edildi','kayip') NOT NULL DEFAULT 'aktif',
  `odunc_veren_id` bigint(20) unsigned DEFAULT NULL,
  `iade_alan_id` bigint(20) unsigned DEFAULT NULL,
  `notlar` text DEFAULT NULL,
  `iade_notu` text DEFAULT NULL,
  `sure_uzatimi` int(11) DEFAULT NULL,
  `sure_uzatan_id` int(11) DEFAULT NULL,
  `sure_uzatma_tarihi` datetime DEFAULT NULL,
  `rezerve_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `odunc_islemleri_uye_id_foreign` (`uye_id`),
  CONSTRAINT `odunc_islemleri_uye_id_foreign` FOREIGN KEY (`uye_id`) REFERENCES `uyeler` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('odunc_islemleri');
    }
};