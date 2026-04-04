<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Tur extends Model {
    protected $table  = 'tur';
    protected $guarded = [];
    public function scopeAktif($q) { return $q->where('aktif', 1); }
}
