<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GirisTuru extends Model
{
    protected $table = 'girisTuru';

    protected $guarded = [];

    /**
     * Aktif giriş türlerini döndürür.
     */
    public function scopeAktif($query)
    {
        return $query->where('aktif', 1);
    }
}
