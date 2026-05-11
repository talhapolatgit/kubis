<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Yayinevi extends Model
{
    protected $table  = 'yayinevleri';
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
     * Ad'a göre bul veya oluştur (boşlukları normalize eder).
     *
     * @param  int|null  $createdByUserId  Yeni kayıtta yalnızca `created_by`.
     */
    public static function findOrCreateByAd(string $ad, ?int $createdByUserId = null): self
    {
        $ad = trim($ad);
        $attributes = [];
        if ($createdByUserId !== null) {
            $attributes['created_by'] = $createdByUserId;
        }

        return static::firstOrCreate(['ad' => $ad], $attributes);
    }

    /**
     * Bu yayınevine ait katalog kayıtları.
     */
    public function kataloglar()
    {
        return $this->hasMany(Katalog::class, 'yayineviId');
    }
}
