<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Yazar extends Model
{
    protected $table = 'yazarlar';

    protected $guarded = [];

    /**
     * Görüntüleme / katalog metni: "Ad Soyad".
     */
    public function getTamAdAttribute(): string
    {
        return trim($this->ad . ' ' . $this->soyad);
    }

    /**
     * Form veya katalogdan gelen tam yazar metnini ayrıştırır.
     *
     * @return array{ad: string, soyad: string, siralama_adi: string}
     */
    public static function parseTamMetin(string $full): array
    {
        $full = preg_replace('/\s+/u', ' ', trim($full));
        if ($full === '') {
            return ['ad' => '', 'soyad' => '', 'siralama_adi' => ''];
        }
        if (preg_match('/^([^,]+),\s*(.+)$/u', $full, $m)) {
            $soyad = trim($m[1]);
            $ad = trim($m[2]);
            if ($ad === '' && $soyad !== '') {
                $ad = $soyad;
                $soyad = '';
            }

            return [
                'ad'           => $ad,
                'soyad'        => $soyad,
                'siralama_adi' => $soyad !== '' ? ($soyad . ', ' . $ad) : $ad,
            ];
        }
        $parts = preg_split('/\s+/u', $full, -1, PREG_SPLIT_NO_EMPTY);
        if (count($parts) >= 2) {
            $soyad = array_pop($parts);
            $ad = implode(' ', $parts);

            return [
                'ad'           => $ad,
                'soyad'        => $soyad,
                'siralama_adi' => $soyad . ', ' . $ad,
            ];
        }

        return [
            'ad'           => $full,
            'soyad'        => '',
            'siralama_adi' => $full,
        ];
    }

    /**
     * Tam metin (tek alan) ile bul veya oluştur; boşlukları normalize eder.
     */
    public static function findOrCreateByAd(string $ad): self
    {
        $p = static::parseTamMetin($ad);

        return static::firstOrCreate(
            ['ad' => $p['ad'], 'soyad' => $p['soyad']],
            ['siralama_adi' => $p['siralama_adi']]
        );
    }

    /**
     * Ad ve soyad alanlarıyla bul veya oluştur (manuel katalog girişi).
     */
    public static function findOrCreateByAdSoyad(string $ad, string $soyad): self
    {
        $ad = trim($ad);
        $soyad = trim($soyad);
        $siralama = $soyad !== '' ? ($soyad . ', ' . $ad) : $ad;

        return static::firstOrCreate(
            ['ad' => $ad, 'soyad' => $soyad],
            ['siralama_adi' => $siralama]
        );
    }

    /**
     * Bu yazara ait katalog kayıtları (ara tablo).
     */
    public function kataloglar()
    {
        return $this->belongsToMany(Katalog::class, 'katalog_yazarlar', 'yazar_id', 'katalog_id')
            ->withPivot('sira')
            ->orderByPivot('sira')
            ->withTimestamps();
    }
}
