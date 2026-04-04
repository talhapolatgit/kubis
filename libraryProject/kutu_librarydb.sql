-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Anamakine: 127.0.0.1
-- Üretim Zamanı: 17 Mar 2026, 20:38:35
-- Sunucu sürümü: 10.4.32-MariaDB
-- PHP Sürümü: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Veritabanı: `kutu_librarydb`
--

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `alttur`
--

CREATE TABLE `alttur` (
  `id` int(11) UNSIGNED NOT NULL,
  `ad` varchar(100) NOT NULL,
  `sira` int(11) NOT NULL DEFAULT 0,
  `aktif` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `giristuru`
--

CREATE TABLE `giristuru` (
  `id` int(11) NOT NULL,
  `ad` varchar(100) NOT NULL COMMENT 'Giriş türü adı (Satın alma, Hibe, Bağış, Protokol, Diğer)',
  `sira` int(11) NOT NULL DEFAULT 0 COMMENT 'Sıralama önceliği',
  `aktif` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1 = Aktif, 0 = Pasif',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Kütüphaneye eser giriş türleri';

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `katalog`
--

CREATE TABLE `katalog` (
  `id` int(11) NOT NULL,
  `kunyeDemirbasKN` varchar(50) DEFAULT NULL,
  `kunyeSiniflamaYer` varchar(100) DEFAULT NULL,
  `kunyeYayinTarihi` varchar(50) DEFAULT NULL,
  `kunyeKopya` varchar(50) DEFAULT NULL,
  `kunyeCilt` varchar(50) DEFAULT NULL,
  `kunyeDilKN` varchar(20) DEFAULT NULL,
  `kunyeEserAdi` text DEFAULT NULL,
  `kunyeEserAdiAlt` varchar(250) DEFAULT NULL,
  `kunyeYazar` varchar(255) DEFAULT NULL,
  `kunyeSorumlular` text DEFAULT NULL,
  `kunyeYayinYeri` varchar(255) DEFAULT NULL,
  `kunyeYayinlayan` varchar(255) DEFAULT NULL,
  `kunyeFizikselTanim` text DEFAULT NULL,
  `kunyeISBNISSN` varchar(100) DEFAULT NULL,
  `kunyeBasimKaydi` text DEFAULT NULL,
  `kunyeDiziKaydi` text DEFAULT NULL,
  `kunyeKonuBasligi` text DEFAULT NULL,
  `icerik` text DEFAULT NULL COMMENT 'İçindekiler',
  `aciklama` text DEFAULT NULL COMMENT 'Açıklama',
  `ozelNotlar` text DEFAULT NULL COMMENT 'Özel Notlar',
  `ustEserKatalogId` int(11) DEFAULT NULL COMMENT 'Üst eser — katalog.id FK',
  `kunyeKategori` int(11) DEFAULT NULL,
  `turId` int(11) UNSIGNED DEFAULT NULL COMMENT 'tur.id FK',
  `altTurId` int(11) UNSIGNED DEFAULT NULL COMMENT 'alttur.id FK',
  `sekilId` int(11) UNSIGNED DEFAULT NULL COMMENT 'sekil.id FK',
  `ortamId` int(11) UNSIGNED DEFAULT NULL COMMENT 'ortam.id FK',
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
  `updated_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `deleted_at` datetime DEFAULT NULL,
  `yazarId` int(11) UNSIGNED DEFAULT NULL COMMENT 'yazarlar.id FK',
  `yayineviId` int(11) UNSIGNED DEFAULT NULL COMMENT 'yayinevleri.id FK'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `kategori`
--

CREATE TABLE `kategori` (
  `id` int(11) NOT NULL,
  `title` varchar(250) NOT NULL,
  `statu` varchar(30) NOT NULL DEFAULT 'aktif',
  `created_user` int(11) DEFAULT NULL,
  `updated_user` int(11) DEFAULT NULL,
  `deleted_user` int(11) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `kutuphane`
--

CREATE TABLE `kutuphane` (
  `id` int(11) NOT NULL,
  `title` varchar(250) NOT NULL,
  `address` varchar(250) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `email` varchar(250) DEFAULT NULL,
  `kutuphanePOIId` int(11) DEFAULT NULL,
  `uyelikBasvurusundaBulunulabilirMi` tinyint(4) DEFAULT NULL,
  `statu` enum('aktif','pasif') NOT NULL DEFAULT 'aktif' COMMENT 'Kütüphane durumu: aktif / pasif',
  `updated_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `kutuphane_yetkili`
--

CREATE TABLE `kutuphane_yetkili` (
  `id` int(11) UNSIGNED NOT NULL COMMENT 'Birincil anahtar',
  `kutuphane_id` int(11) NOT NULL COMMENT 'kutuphane.id FK',
  `user_id` bigint(20) UNSIGNED NOT NULL COMMENT 'users.id FK — yetkili kullanıcı',
  `created_by` bigint(20) UNSIGNED NOT NULL COMMENT 'users.id FK — kaydı ekleyen kullanıcı',
  `deleted_by` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'users.id FK — kaydı silen kullanıcı',
  `created_at` datetime NOT NULL DEFAULT current_timestamp() COMMENT 'Ekleme tarihi',
  `deleted_at` datetime DEFAULT NULL COMMENT 'Soft-delete tarihi (NULL = aktif)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Kütüphane bazında yetkili kullanıcı ilişkisi (soft-delete)';

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `odunc_islemleri`
--

CREATE TABLE `odunc_islemleri` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uye_id` bigint(20) UNSIGNED NOT NULL,
  `katalog_id` bigint(20) UNSIGNED NOT NULL,
  `kutuphane_id` bigint(20) UNSIGNED DEFAULT NULL,
  `odunc_tarihi` date NOT NULL,
  `iade_tarihi_planlanan` date NOT NULL,
  `iade_tarihi_gercek` date DEFAULT NULL,
  `statu` enum('aktif','iade_edildi','kayip') NOT NULL DEFAULT 'aktif',
  `odunc_veren_id` bigint(20) UNSIGNED DEFAULT NULL,
  `iade_alan_id` bigint(20) UNSIGNED DEFAULT NULL,
  `notlar` text DEFAULT NULL,
  `iade_notu` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `ortam`
--

CREATE TABLE `ortam` (
  `id` int(11) UNSIGNED NOT NULL,
  `ad` varchar(100) NOT NULL,
  `sira` int(11) NOT NULL DEFAULT 0,
  `aktif` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `sekil`
--

CREATE TABLE `sekil` (
  `id` int(11) UNSIGNED NOT NULL,
  `ad` varchar(100) NOT NULL,
  `sira` int(11) NOT NULL DEFAULT 0,
  `aktif` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `tur`
--

CREATE TABLE `tur` (
  `id` int(11) UNSIGNED NOT NULL,
  `ad` varchar(100) NOT NULL,
  `sira` int(11) NOT NULL DEFAULT 0,
  `aktif` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `role` enum('admin','personel','okuyucu') NOT NULL DEFAULT 'personel' COMMENT 'Kullanıcı rolü: admin / personel / okuyucu',
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `uyeler`
--

CREATE TABLE `uyeler` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tc_kimlik` varchar(11) NOT NULL,
  `dogum_tarihi` date NOT NULL,
  `ad` varchar(100) NOT NULL,
  `soyad` varchar(100) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `telefon` varchar(20) NOT NULL,
  `telefon_dogrulandi` tinyint(1) NOT NULL DEFAULT 0,
  `il` varchar(100) DEFAULT NULL,
  `ilce` varchar(100) DEFAULT NULL,
  `mahalle` varchar(150) DEFAULT NULL,
  `acik_adres` text DEFAULT NULL,
  `statu` enum('aktif','pasif') NOT NULL DEFAULT 'aktif',
  `uyelik_baslangic` date DEFAULT NULL,
  `uyelik_bitis` date DEFAULT NULL,
  `notlar` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `yayinevleri`
--

CREATE TABLE `yayinevleri` (
  `id` int(11) UNSIGNED NOT NULL,
  `ad` varchar(255) NOT NULL COMMENT 'Yayınevi adı',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Yayınevi kaydı — katalog.yayineviId FK';

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `yazarlar`
--

CREATE TABLE `yazarlar` (
  `id` int(11) UNSIGNED NOT NULL,
  `ad` varchar(255) NOT NULL COMMENT 'Yazar adı soyadı',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Yazar kaydı — katalog.yazarId FK';

--
-- Dökümü yapılmış tablolar için indeksler
--

--
-- Tablo için indeksler `alttur`
--
ALTER TABLE `alttur`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Tablo için indeksler `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Tablo için indeksler `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Tablo için indeksler `giristuru`
--
ALTER TABLE `giristuru`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Tablo için indeksler `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `katalog`
--
ALTER TABLE `katalog`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_katalog_yazarId` (`yazarId`),
  ADD KEY `idx_katalog_yayineviId` (`yayineviId`);

--
-- Tablo için indeksler `kategori`
--
ALTER TABLE `kategori`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `kutuphane`
--
ALTER TABLE `kutuphane`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `kutuphane_yetkili`
--
ALTER TABLE `kutuphane_yetkili`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_kutuphane_user_active` (`kutuphane_id`,`user_id`,`deleted_at`),
  ADD KEY `idx_ky_kutuphane` (`kutuphane_id`),
  ADD KEY `idx_ky_user` (`user_id`),
  ADD KEY `idx_ky_created_by` (`created_by`),
  ADD KEY `idx_ky_deleted_by` (`deleted_by`),
  ADD KEY `idx_ky_deleted_at` (`deleted_at`);

--
-- Tablo için indeksler `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `odunc_islemleri`
--
ALTER TABLE `odunc_islemleri`
  ADD PRIMARY KEY (`id`),
  ADD KEY `odunc_islemleri_uye_id_foreign` (`uye_id`);

--
-- Tablo için indeksler `ortam`
--
ALTER TABLE `ortam`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Tablo için indeksler `sekil`
--
ALTER TABLE `sekil`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Tablo için indeksler `tur`
--
ALTER TABLE `tur`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- Tablo için indeksler `uyeler`
--
ALTER TABLE `uyeler`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uyeler_tc_kimlik_unique` (`tc_kimlik`);

--
-- Tablo için indeksler `yayinevleri`
--
ALTER TABLE `yayinevleri`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_yayinevleri_ad` (`ad`);

--
-- Tablo için indeksler `yazarlar`
--
ALTER TABLE `yazarlar`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_yazarlar_ad` (`ad`);

--
-- Dökümü yapılmış tablolar için AUTO_INCREMENT değeri
--

--
-- Tablo için AUTO_INCREMENT değeri `alttur`
--
ALTER TABLE `alttur`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `giristuru`
--
ALTER TABLE `giristuru`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `katalog`
--
ALTER TABLE `katalog`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `kutuphane`
--
ALTER TABLE `kutuphane`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `kutuphane_yetkili`
--
ALTER TABLE `kutuphane_yetkili`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Birincil anahtar';

--
-- Tablo için AUTO_INCREMENT değeri `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `odunc_islemleri`
--
ALTER TABLE `odunc_islemleri`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `ortam`
--
ALTER TABLE `ortam`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `sekil`
--
ALTER TABLE `sekil`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `tur`
--
ALTER TABLE `tur`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `uyeler`
--
ALTER TABLE `uyeler`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `yayinevleri`
--
ALTER TABLE `yayinevleri`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Tablo için AUTO_INCREMENT değeri `yazarlar`
--
ALTER TABLE `yazarlar`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Dökümü yapılmış tablolar için kısıtlamalar
--

--
-- Tablo kısıtlamaları `kutuphane_yetkili`
--
ALTER TABLE `kutuphane_yetkili`
  ADD CONSTRAINT `fk_ky_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_ky_deleted_by` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_ky_kutuphane` FOREIGN KEY (`kutuphane_id`) REFERENCES `kutuphane` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_ky_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Tablo kısıtlamaları `odunc_islemleri`
--
ALTER TABLE `odunc_islemleri`
  ADD CONSTRAINT `odunc_islemleri_uye_id_foreign` FOREIGN KEY (`uye_id`) REFERENCES `uyeler` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
