<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Ortam extends Model {
    protected $table  = 'ortam';
    protected $guarded = [];
    public function scopeAktif($q) { return $q->where('aktif', 1); }
}
