<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `katalog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kunyeDemirbasKN` varchar(50) DEFAULT NULL,
  `kunyeSiniflamaYer` varchar(100) DEFAULT NULL,
  `kunyeYayinTarihi` varchar(50) DEFAULT NULL,
  `kunyeKopya` varchar(50) DEFAULT NULL,
  `kunyeCilt` varchar(50) DEFAULT NULL,
  `kunyeDilKN` varchar(20) DEFAULT NULL,
  `kunyeDil2` varchar(30) DEFAULT NULL,
  `kunyeEserAdi` text DEFAULT NULL,
  `kunyeEserAdiAlt` varchar(250) DEFAULT NULL,
  `kunyeYazar` varchar(255) DEFAULT NULL,
  `kunyeSorumlular` text DEFAULT NULL,
  `kunyeYayinYeri` varchar(255) DEFAULT NULL,
  `kunyeYayinlayan` varchar(255) DEFAULT NULL,
  `kunyeFizikselTanim` text DEFAULT NULL,
  `kunyeSayfaSayisi` int(11) DEFAULT NULL,
  `kunyeISBNISSN` varchar(100) DEFAULT NULL,
  `kunyeBasimKaydi` text DEFAULT NULL,
  `kunyeDiziKaydi` text DEFAULT NULL,
  `kunyeKonuBasligi` text DEFAULT NULL,
  `icerik` text DEFAULT NULL COMMENT 'İçindekiler',
  `aciklama` text DEFAULT NULL COMMENT 'Açıklama',
  `ozelNotlar` text DEFAULT NULL COMMENT 'Özel Notlar',
  `ozelNotlar2` varchar(255) DEFAULT NULL,
  `ozelNotlar3` varchar(250) DEFAULT NULL,
  `ustEserKatalogId` int(11) DEFAULT NULL COMMENT 'Üst eser — katalog.id FK',
  `kunyeKategori` int(11) DEFAULT NULL,
  `turId` int(11) unsigned DEFAULT NULL COMMENT 'tur.id FK',
  `altTurId` int(11) unsigned DEFAULT NULL COMMENT 'alttur.id FK',
  `sekilId` int(11) unsigned DEFAULT NULL COMMENT 'sekil.id FK',
  `ortamId` int(11) unsigned DEFAULT NULL COMMENT 'ortam.id FK',
  `kunyeDewey` varchar(50) DEFAULT NULL,
  `kunyeKapakResmi` varchar(250) DEFAULT NULL,
  `kunyeGelisTarihi` date DEFAULT NULL,
  `created_user` varchar(50) DEFAULT NULL,
  `updated_user` int(11) DEFAULT NULL,
  `kunyeDurum` varchar(100) DEFAULT NULL,
  `kutuphaneId` int(11) DEFAULT NULL COMMENT 'kutuphane.id FK',
  `girisTuruId` int(11) DEFAULT NULL COMMENT 'girisTuru.id FK',
  `faturaNo` varchar(100) DEFAULT NULL COMMENT 'Fatura numarası',
  `faturaTarihi` date DEFAULT NULL COMMENT 'Fatura tarihi',
  `tedarikci` varchar(255) DEFAULT NULL COMMENT 'Firma adı / Hibe eden / Bağışlayan (ortak kolon)',
  `tedarikciTelefon` varchar(50) DEFAULT NULL COMMENT 'Tedarikçi / Hibe / Bağış telefonu',
  `tedarikciEposta` varchar(255) DEFAULT NULL COMMENT 'Tedarikçi / Hibe / Bağış e-postası',
  `fiyat` decimal(10,2) DEFAULT NULL COMMENT 'Satın alma fiyatı (₺)',
  `oduncVerilemez` varchar(10) DEFAULT 'false',
  `etiketlendi` int(11) DEFAULT NULL,
  `etiketOlustumu` int(11) DEFAULT 0,
  `updated_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `deleted_at` datetime DEFAULT NULL,
  `yazarId` int(11) unsigned DEFAULT NULL COMMENT 'yazarlar.id FK',
  `yayineviId` int(11) unsigned DEFAULT NULL COMMENT 'yayinevleri.id FK',
  `iade_tarihi_planlanan` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_katalog_yazarId` (`yazarId`),
  KEY `idx_katalog_yayineviId` (`yayineviId`),
  KEY `idx_katalog_durum` (`kunyeDurum`,`deleted_at`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('katalog');
    }
};