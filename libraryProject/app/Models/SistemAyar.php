<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SistemAyar extends Model
{
    protected $table = 'sistem_ayarlari';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function olusturan(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function guncelleyen(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Tekil sistem ayarı kaydını döndürür; yoksa boş bir kayıt oluşturur.
     */
    public static function current(): self
    {
        $ayar = static::query()->first();

        if ($ayar) {
            return $ayar;
        }

        return static::create([]);
    }

    public function getLogoUrlAttribute(): ?string
    {
        if (! $this->logo_path) {
            return null;
        }

        if (str_starts_with($this->logo_path, 'http://') || str_starts_with($this->logo_path, 'https://')) {
            return $this->logo_path;
        }

        $path = ltrim((string) $this->logo_path, '/');
        if (str_starts_with($path, 'storage/')) {
            return '/' . $path;
        }

        return '/storage/' . $path;
    }

    /**
     * İzinli IP listesini satır dizisine çevirir.
     *
     * @return list<string>
     */
    public function izinliIpListesi(): array
    {
        $raw = trim((string) ($this->izinli_ip_adresleri ?? ''));
        if ($raw === '') {
            return [];
        }

        $parts = preg_split('/[\s,;]+/', $raw, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        return array_values(array_unique(array_map('trim', $parts)));
    }
}
