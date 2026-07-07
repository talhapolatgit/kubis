<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ZiyaretKaydi extends Model
{
    protected $table = 'ziyaretci_kayitlari';

    protected $fillable = [
        'uye_id',
        'kutuphane_id',
        'giris_saati',
        'cikis_saati',
        'notlar',
        'created_user',
        'updated_user',
    ];

    protected $casts = [
        'giris_saati' => 'datetime',
        'cikis_saati' => 'datetime',
    ];

    public function uye(): BelongsTo
    {
        return $this->belongsTo(Uye::class, 'uye_id');
    }

    public function kutuphane(): BelongsTo
    {
        return $this->belongsTo(Kutuphane::class, 'kutuphane_id');
    }

    public function kaydeden(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_user');
    }

    public function getIcindeMiAttribute(): bool
    {
        return $this->cikis_saati === null;
    }

    public function getSureDakikaAttribute(): ?int
    {
        if (!$this->cikis_saati) {
            return null;
        }

        return (int) $this->giris_saati->diffInMinutes($this->cikis_saati);
    }

    public function getSureLabelAttribute(): string
    {
        if (!$this->cikis_saati) {
            return '—';
        }

        $dk = $this->sure_dakika;
        if ($dk === null) {
            return '—';
        }
        if ($dk < 60) {
            return $dk . ' dk';
        }

        $saat = intdiv($dk, 60);
        $kalan = $dk % 60;

        return $kalan > 0 ? ($saat . ' sa ' . $kalan . ' dk') : ($saat . ' sa');
    }
}
