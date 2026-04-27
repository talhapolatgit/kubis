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
     * İlk yazar (geriye dönük uyumluluk — katalog.yazarId).
     */
    public function yazar()
    {
        return $this->belongsTo(Yazar::class, 'yazarId');
    }

    /**
     * Kitaba bağlı tüm yazarlar (sıra: pivot.sira).
     */
    public function yazarlar()
    {
        return $this->belongsToMany(Yazar::class, 'katalog_yazarlar', 'katalog_id', 'yazar_id')
            ->withPivot('sira')
            ->orderByPivot('sira')
            ->withTimestamps();
    }

    /**
     * Arayüz / API için birleşik yazar metni (çoklu yazar varsa virgülle).
     */
    public function formattedYazarlarAdi(): string
    {
        $list = $this->relationLoaded('yazarlar')
            ? $this->yazarlar
            : $this->yazarlar()->get();

        if ($list->isNotEmpty()) {
            return $list->pluck('tam_ad')->filter()->implode(', ');
        }

        return optional($this->yazar)->tam_ad ?? (string) ($this->kunyeYazar ?? '');
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

    public function koleksiyon()
    {
        return $this->belongsTo(Koleksiyon::class, 'koleksiyon_id');
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
