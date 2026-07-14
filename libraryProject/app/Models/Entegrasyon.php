<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Entegrasyon extends Model
{
    protected $table = 'entegrasyonlar';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'aktif' => 'boolean',
            'ayarlar' => 'encrypted:array',
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

    public function scopeTip($query, string $tip)
    {
        return $query->where('tip', $tip);
    }

    public function scopeAktif($query)
    {
        return $query->where('aktif', true);
    }

    /**
     * Belirtilen tipteki aktif entegrasyonu döndürür.
     */
    public static function aktifTip(string $tip): ?self
    {
        return static::query()
            ->tip($tip)
            ->aktif()
            ->first();
    }

    /**
     * Aktif SMS entegrasyonunu döndürür.
     */
    public static function sms(): ?self
    {
        return static::aktifTip('sms');
    }

    /**
     * Aktif kimlik sorgulama entegrasyonunu döndürür.
     */
    public static function kimlik(): ?self
    {
        return static::aktifTip('kimlik');
    }

    /**
     * Aktif adres sorgulama entegrasyonunu döndürür.
     */
    public static function adres(): ?self
    {
        return static::aktifTip('adres');
    }

    /**
     * Aktif LDAP entegrasyonunu döndürür.
     */
    public static function ldap(): ?self
    {
        return static::aktifTip('ldap');
    }

    /**
     * Aktif webhook entegrasyonunu döndürür.
     */
    public static function webhook(): ?self
    {
        return static::aktifTip('webhook');
    }
}
