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

}
