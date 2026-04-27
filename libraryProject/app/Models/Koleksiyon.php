<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Koleksiyon extends Model
{
    use SoftDeletes;

    protected $table = 'koleksiyon';

    protected $guarded = [];

    public const CREATED_AT = 'created_date';

    public const UPDATED_AT = 'updated_at';

    protected $casts = [
        'created_date' => 'datetime',
        'updated_at'   => 'datetime',
        'deleted_at'   => 'datetime',
    ];

    public function scopeAktif($query)
    {
        return $query->where('statu', 'aktif');
    }
}
