<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReferenceTablesSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('tur')->upsert([
            ['id' => 1, 'ad' => 'Kitap', 'sira' => 1, 'aktif' => 1],
            ['id' => 2, 'ad' => 'Kitapdışı', 'sira' => 2, 'aktif' => 1],
            ['id' => 3, 'ad' => 'Süreli', 'sira' => 3, 'aktif' => 1],
            ['id' => 4, 'ad' => 'Süreli Sayı', 'sira' => 4, 'aktif' => 1],
            ['id' => 5, 'ad' => 'Tez', 'sira' => 5, 'aktif' => 1],
        ], ['id'], ['ad', 'sira', 'aktif']);

        DB::table('alttur')->upsert([
            ['id' => 1, 'ad' => 'Askeri', 'sira' => 1, 'aktif' => 1],
            ['id' => 2, 'ad' => 'Astroloji', 'sira' => 2, 'aktif' => 1],
            ['id' => 3, 'ad' => 'Astronomi', 'sira' => 3, 'aktif' => 1],
            ['id' => 4, 'ad' => 'Atlas', 'sira' => 4, 'aktif' => 1],
            ['id' => 5, 'ad' => 'Balıkçılık', 'sira' => 5, 'aktif' => 1],
            ['id' => 6, 'ad' => 'Belgesel', 'sira' => 6, 'aktif' => 1],
            ['id' => 7, 'ad' => 'Bilgisayar', 'sira' => 7, 'aktif' => 1],
            ['id' => 8, 'ad' => 'Bilim Kurgu', 'sira' => 8, 'aktif' => 1],
            ['id' => 9, 'ad' => 'Bilim', 'sira' => 9, 'aktif' => 1],
        ], ['id'], ['ad', 'sira', 'aktif']);

        DB::table('girisTuru')->upsert([
            ['id' => 1, 'ad' => 'Satın Alma', 'sira' => 1, 'aktif' => 1],
            ['id' => 2, 'ad' => 'Hibe', 'sira' => 2, 'aktif' => 1],
            ['id' => 3, 'ad' => 'Bağış', 'sira' => 3, 'aktif' => 1],
        ], ['id'], ['ad', 'sira', 'aktif']);

        DB::table('kategori')->upsert([
            ['id' => 1, 'title' => 'Genel Konular (000)', 'statu' => 'aktif', 'created_user' => null, 'updated_user' => null, 'deleted_user' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => 2, 'title' => 'Felsefe & Psikoloji (100)', 'statu' => 'aktif', 'created_user' => null, 'updated_user' => null, 'deleted_user' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => 3, 'title' => 'Din (200)', 'statu' => 'aktif', 'created_user' => null, 'updated_user' => null, 'deleted_user' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => 4, 'title' => 'Toplum Bilimleri (300)', 'statu' => 'aktif', 'created_user' => null, 'updated_user' => null, 'deleted_user' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => 5, 'title' => 'Dil ve Dil Bilim (400)', 'statu' => 'aktif', 'created_user' => null, 'updated_user' => null, 'deleted_user' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => 6, 'title' => 'Doğa Bilimleri & Matematik (500)', 'statu' => 'aktif', 'created_user' => null, 'updated_user' => null, 'deleted_user' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => 7, 'title' => 'Teknoloji - Uygulamalı Bilimler (600)', 'statu' => 'aktif', 'created_user' => null, 'updated_user' => null, 'deleted_user' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => 8, 'title' => 'Sanat (700)', 'statu' => 'aktif', 'created_user' => null, 'updated_user' => null, 'deleted_user' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => 9, 'title' => 'Edebiyat & Retorik (800)', 'statu' => 'aktif', 'created_user' => null, 'updated_user' => null, 'deleted_user' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => 10, 'title' => 'Coğrafya & Tarih (900)', 'statu' => 'aktif', 'created_user' => null, 'updated_user' => null, 'deleted_user' => null, 'updated_at' => null, 'deleted_at' => null],
        ], ['id'], ['title', 'statu', 'created_user', 'updated_user', 'deleted_user', 'updated_at', 'deleted_at']);

        DB::table('ortam')->upsert([
            ['id' => 1, 'ad' => 'Bilgisayar', 'sira' => 1, 'aktif' => 1],
            ['id' => 2, 'ad' => 'CD', 'sira' => 2, 'aktif' => 1],
            ['id' => 3, 'ad' => 'Disket', 'sira' => 3, 'aktif' => 1],
            ['id' => 4, 'ad' => 'Kağıt', 'sira' => 4, 'aktif' => 1],
            ['id' => 5, 'ad' => 'Karton', 'sira' => 5, 'aktif' => 1],
            ['id' => 6, 'ad' => 'Oyun', 'sira' => 6, 'aktif' => 1],
            ['id' => 7, 'ad' => 'Video', 'sira' => 7, 'aktif' => 1],
        ], ['id'], ['ad', 'sira', 'aktif']);

        DB::table('sekil')->upsert([
            ['id' => 1, 'ad' => 'Basılı', 'sira' => 1, 'aktif' => 1],
            ['id' => 2, 'ad' => 'Bilgisayar Diski', 'sira' => 2, 'aktif' => 1],
            ['id' => 3, 'ad' => 'CD', 'sira' => 3, 'aktif' => 1],
            ['id' => 4, 'ad' => 'Diğer', 'sira' => 4, 'aktif' => 1],
        ], ['id'], ['ad', 'sira', 'aktif']);
    }
}
