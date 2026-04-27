<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class AltTur extends Model {
    protected $table  = 'alttur';
    protected $guarded = [];
    public function scopeAktif($q) { return $q->where('aktif', 1); }

    /**
     * Ada göre bul veya oluştur (boşlukları normalize eder).
     */
    public static function findOrCreateByAd(string $ad): self
    {
        $ad = trim($ad);
        return static::firstOrCreate(['ad' => $ad]);
    }
}
