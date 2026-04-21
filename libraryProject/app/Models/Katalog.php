<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Katalog extends Model
{
    use HasFactory;

    protected $table = 'katalog';

    protected $guarded = [];

    /**
     * İlişkili yazar kaydı.
     */
    public function yazar()
    {
        return $this->belongsTo(Yazar::class, 'yazarId');
    }

    /**
     * İlişkili yayınevi kaydı.
     */
    public function yayinevi()
    {
        return $this->belongsTo(Yayinevi::class, 'yayineviId');
    }

    /**
     * İlişkili kütüphane kaydı.
     */
    public function kutuphane()
    {
        return $this->belongsTo(Kutuphane::class, 'kutuphaneId');
    }

    public function tahminiMusaitlik()
    {
        return $this->hasOne(OduncIslem::class, 'katalog_id')
                ->where('statu', 'aktif');
    }

    public function favorimi($uyeid)
    {
        $check = $this->hasOne(UyeFavori::class, 'katalog_id')
                ->where('uye_id', $uyeid)
                ->count();
                
        if ($check > 0) {
            return true;
        } else {
            return false;
        }
    }
}
