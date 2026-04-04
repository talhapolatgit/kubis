<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Yayinevi extends Model
{
    protected $table  = 'yayinevleri';
    protected $guarded = [];

    /**
     * Ad'a göre bul veya oluştur (boşlukları normalize eder).
     */
    public static function findOrCreateByAd(string $ad): self
    {
        $ad = trim($ad);
        return static::firstOrCreate(['ad' => $ad]);
    }

    /**
     * Bu yayınevine ait katalog kayıtları.
     */
    public function kataloglar()
    {
        return $this->hasMany(Katalog::class, 'yayineviId');
    }
}
