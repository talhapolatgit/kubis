<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `uye_rezerve` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `katalog_id` int(11) NOT NULL,
  `uye_id` int(11) NOT NULL,
  `rezerve_baslangic` datetime NOT NULL,
  `rezerve_bitis` datetime NOT NULL,
  `oduncAldiMi` varchar(10) NOT NULL DEFAULT 'false',
  `odunc_id` int(11) DEFAULT NULL,
  `suresiDolduMu` varchar(10) DEFAULT NULL,
  `iptalMi` varchar(10) NOT NULL,
  `iptalEdenUserId` bigint(20) unsigned DEFAULT NULL,
  `catalogStatuReset` varchar(10) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_rezerve_expire` (`rezerve_bitis`,`iptalMi`,`catalogStatuReset`,`deleted_at`),
  KEY `uye_rezerve_iptaledenuserid_foreign` (`iptalEdenUserId`),
  CONSTRAINT `uye_rezerve_iptaledenuserid_foreign` FOREIGN KEY (`iptalEdenUserId`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=135 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('uye_rezerve');
    }
};