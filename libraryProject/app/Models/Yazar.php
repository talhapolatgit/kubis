<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Yazar extends Model
{
    protected $table  = 'yazarlar';
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
     * Bu yazara ait katalog kayıtları.
     */
    public function kataloglar()
    {
        return $this->hasMany(Katalog::class, 'yazarId');
    }
}
