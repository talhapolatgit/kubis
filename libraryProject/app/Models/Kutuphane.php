<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Kutuphane extends Model
{
    use SoftDeletes;

    protected $table = 'kutuphane';

    protected $guarded = [];

    /**
     * Sadece aktif kütüphaneleri döndürür.
     */
    public function scopeAktif($query)
    {
        return $query->where('statu', 'aktif');
    }
}
