<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class OduncIslem extends Model
{
    protected $table = 'odunc_islemleri';

    protected $fillable = [
        'uye_id',
        'katalog_id',
        'kutuphane_id',
        'odunc_tarihi',
        'iade_tarihi_planlanan',
        'iade_tarihi_gercek',
        'sure_uzatimi',
        'sure_uzatan_id',
        'sure_uzatma_tarihi',
        'statu',
        'odunc_veren_id',
        'iade_alan_id',
        'notlar',
        'iade_notu',
    ];

    protected $casts = [
        'odunc_tarihi'           => 'date',
        'iade_tarihi_planlanan'  => 'date',
        'iade_tarihi_gercek'     => 'date',
    ];

    // ─── İlişkiler ───────────────────────────────────────────────────────────────

    public function uye(): BelongsTo
    {
        return $this->belongsTo(Uye::class, 'uye_id');
    }

    public function katalog(): BelongsTo
    {
        return $this->belongsTo(Katalog::class, 'katalog_id');
    }

    public function kutuphane(): BelongsTo
    {
        return $this->belongsTo(Kutuphane::class, 'kutuphane_id');
    }

    public function oduncVeren(): BelongsTo
    {
        return $this->belongsTo(User::class, 'odunc_veren_id');
    }

    public function iadeAlan(): BelongsTo
    {
        return $this->belongsTo(User::class, 'iade_alan_id');
    }

    // ─── Yardımcı özellikler ─────────────────────────────────────────────────────

    /**
     * Gecikme var mı?
     */
    public function getGecikiyorMuAttribute(): bool
    {
        if ($this->statu !== 'aktif') {
            return false;
        }
        return Carbon::today()->gt($this->iade_tarihi_planlanan);
    }

    /**
     * Gecikme gün sayısı (aktif + gecikmeli)
     */
    public function getGecikmeGunAttribute(): int
    {
        if (!$this->gecikiyor_mu) {
            return 0;
        }
        return (int) Carbon::today()->diffInDays($this->iade_tarihi_planlanan);
    }

    /**
     * Kalan gün (negatifse gecikmiş)
     */
    public function getKalanGunAttribute(): int
    {
        if ($this->statu !== 'aktif') {
            return 0;
        }
        return (int) Carbon::today()->diffInDays($this->iade_tarihi_planlanan, false);
    }

    /**
     * Durum etiketi
     */
    public function getStatuLabelAttribute(): string
    {
        return match($this->statu) {
            'aktif'       => $this->gecikiyor_mu ? 'Gecikmiş' : 'Aktif',
            'iade_edildi' => 'İade Edildi',
            'kayip'       => 'Kayıp',
            default       => $this->statu,
        };
    }

    // ─── Scope'lar ───────────────────────────────────────────────────────────────

    public function scopeAktif($query)
    {
        return $query->where('statu', 'aktif');
    }

    public function scopeGecikti($query)
    {
        return $query->where('statu', 'aktif')
            ->where('iade_tarihi_planlanan', '<', now()->toDateString());
    }

    public function scopeBugünIade($query)
    {
        return $query->where('statu', 'iade_edildi')
            ->whereDate('iade_tarihi_gercek', today());
    }

    public function sureUzatan()
    {
        return $this->belongsTo(\App\Models\User::class, 'sure_uzatan_id');
    }
}
