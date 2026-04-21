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
        if($uyeid == null) {
            return false;
        }
        $check = $this->hasOne(UyeFavori::class, 'katalog_id')
                ->where('uye_id', $uyeid)
                ->count();
                
        if ($check > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function rezervemi($uyeid)
    {
        if($uyeid == null) {
            return false;
        }
        $check = $this->hasOne(UyeRezerve::class, 'katalog_id')
                ->where('uye_id', $uyeid)
                ->where('iptalMi', "false")
                ->where('deleted_at', null)
                ->where('rezerve_bitis', '>', now())
                ->count();
                
        if ($check > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function beklememi($uyeid)
    {
        if($uyeid == null) {
            return false;
        }
        $check = $this->hasOne(UyeBekleme::class, 'katalog_id')
                ->where('uye_id', $uyeid)
                ->count();
                
        if ($check > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function oduncmu($uyeid)
    {
        if($uyeid == null) {
            return false;
        }
        $check = $this->hasOne(OduncIslem::class, 'katalog_id')
                ->where('uye_id', $uyeid)
                ->where('statu', 'aktif')
                ->where('iade_tarihi_Gercek', null)
                ->count();
                
        if ($check > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function getKapakResimPathAttribute(): ?string
    {
        if (!$this->kunyeKapakResmi) {
            return null;
        }

        return $this->normalizeKapakPath($this->kunyeKapakResmi);
    }

    private function normalizeKapakPath(string $path): string
    {
        $path = trim($path);

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $path = ltrim($path, '/');

        if (str_starts_with($path, 'storage/')) {
            return '/' . $path;
        }

        return '/storage/' . $path;
    }
}
