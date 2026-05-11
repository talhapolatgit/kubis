<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Yazar extends Model
{
    protected $table = 'yazarlar';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function olusturan(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function guncelleyen(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

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
     *
     * @param  int|null  $createdByUserId  Yeni kayıtta yalnızca `created_by` (ör. katalog kaydeden kullanıcı); `updated_by` güncellemede dolar.
     */
    public static function findOrCreateByAdSoyad(string $ad, string $soyad, ?int $createdByUserId = null): self
    {
        $ad = trim($ad);
        $soyad = trim($soyad);
        $siralama = $soyad !== '' ? ($soyad . ', ' . $ad) : $ad;

        $attributes = ['siralama_adi' => $siralama];
        if ($createdByUserId !== null) {
            $attributes['created_by'] = $createdByUserId;
        }

        return static::firstOrCreate(
            ['ad' => $ad, 'soyad' => $soyad],
            $attributes
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

    /**
     * Eski tek yazar alanı (katalog.yazarId) ile bağlı kitaplar.
     */
    public function kataloglarLegacy()
    {
        return $this->hasMany(Katalog::class, 'yazarId');
    }

    public function getFotografUrlAttribute(): ?string
    {
        if (!$this->fotograf_path) {
            return null;
        }

        if (str_starts_with($this->fotograf_path, 'http://') || str_starts_with($this->fotograf_path, 'https://')) {
            return $this->fotograf_path;
        }

        $path = ltrim((string) $this->fotograf_path, '/');
        if (str_starts_with($path, 'storage/')) {
            return '/' . $path;
        }

        return '/storage/' . $path;
    }
}
