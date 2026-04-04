<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Sekil extends Model {
    protected $table  = 'sekil';
    protected $guarded = [];
    public function scopeAktif($q) { return $q->where('aktif', 1); }
}
