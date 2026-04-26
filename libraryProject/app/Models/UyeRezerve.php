<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class UyeRezerve extends Model
{
    use HasFactory;

    protected $table = 'uye_rezerve';

    protected $guarded = [];

    protected $casts = [
        'rezerve_baslangic' => 'datetime',
        'rezerve_bitis'     => 'datetime',
    ];

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

    public function oduncIslem(): BelongsTo
    {
        return $this->belongsTo(OduncIslem::class, 'odunc_id');
    }

    public function iptalEdenUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'iptalEdenUserId');
    }

}
