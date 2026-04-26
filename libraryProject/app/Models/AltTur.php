<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class AltTur extends Model {
    protected $table  = 'alttur';
    protected $guarded = [];
    public function scopeAktif($q) { return $q->where('aktif', 1); }
}
