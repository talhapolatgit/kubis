<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kategori extends Model
{
    use HasFactory;

    protected $table = 'kategori';

    protected $guarded = [];

    /**
     * Sadece aktif kategorileri getirir.
     */
    public function scopeAktif($query)
    {
        return $query->where('statu', 'aktif');
    }
}
