<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Uye extends Model
{
    use SoftDeletes;

    protected $table = 'uyeler';

    protected $fillable = [
        'tc_kimlik',
        'dogum_tarihi',
        'ad',
        'soyad',
        'cinsiyet',
        'email',
        'telefon',
        'telefon2',
        'telefon_dogrulandi',
        'il',
        'ilce',
        'mahalle',
        'acik_adres',
        'ogretim_durumu',
        'okul_adi',
        'bolum_adi',
        'statu',
        'uyelik_baslangic',
        'uyelik_bitis',
        'notlar',
        'created_user',
        'updated_user',
        'veli_ad',
        'veli_soyad',
        'veli_tc_kimlik',
        'veli_dogum_tarihi',
        'veli_telefon',
    ];

    protected $casts = [
        'dogum_tarihi'       => 'date',
        'uyelik_baslangic'   => 'date',
        'uyelik_bitis'       => 'date',
        'veli_dogum_tarihi'  => 'date',
        'telefon_dogrulandi' => 'boolean',
    ];

    // ─── Helpers ────────────────────────────────────────────────────────────────

    public function getAdSoyadAttribute(): string
    {
        return $this->ad . ' ' . $this->soyad;
    }

    public function getStatuLabelAttribute(): string
    {
        return match($this->statu) {
            'aktif' => 'Aktif',
            'pasif' => 'Pasif',
            default => $this->statu,
        };
    }

    public function getInitialsAttribute(): string
    {
        return strtoupper(mb_substr($this->ad, 0, 1, 'UTF-8') . mb_substr($this->soyad, 0, 1, 'UTF-8'));
    }

    // ─── Scopes ─────────────────────────────────────────────────────────────────

    public function scopeAktif($query)
    {
        return $query->where('statu', 'aktif');
    }

    public function oduncIslemleri(): HasMany
    {
        return $this->hasMany(OduncIslem::class, 'uye_id');
    }

    public function rezervasyonlar(): HasMany
    {
        return $this->hasMany(UyeRezerve::class, 'uye_id');
    }

    public function ziyaretKayitlari(): HasMany
    {
        return $this->hasMany(ZiyaretKaydi::class, 'uye_id');
    }
}
