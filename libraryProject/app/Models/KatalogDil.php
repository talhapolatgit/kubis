<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KatalogDil extends Model
{
    protected $table = 'katalog_dil';
    protected $guarded = [];

    public function scopeAktif($q)
    {
        return $q->where('aktif', 1);
    }
}
