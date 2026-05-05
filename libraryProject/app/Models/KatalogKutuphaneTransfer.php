<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KatalogKutuphaneTransfer extends Model
{
    protected $table = 'katalog_kutuphane_transferleri';

    protected $fillable = [
        'katalog_id',
        'from_kutuphane_id',
        'to_kutuphane_id',
        'user_id',
        'aciklama',
    ];

    public function katalog(): BelongsTo
    {
        return $this->belongsTo(Katalog::class, 'katalog_id');
    }

    public function fromKutuphane(): BelongsTo
    {
        return $this->belongsTo(Kutuphane::class, 'from_kutuphane_id');
    }

    public function toKutuphane(): BelongsTo
    {
        return $this->belongsTo(Kutuphane::class, 'to_kutuphane_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
