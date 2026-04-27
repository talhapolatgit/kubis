<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'tc_kimlik',
        'dogum_tarihi',
        'ad',
        'soyad',
        'cinsiyet',
        'email',
        'telefon',
        'il',
        'ilce',
        'mahalle',
        'acik_adres',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'dogum_tarihi'      => 'date',
            'password'          => 'hashed',
        ];
    }

    /**
     * Rol etiket yardımcısı.
     */
    public function getRoleLabel(): string
    {
        return match ($this->role) {
            'admin'    => 'Yönetici',
            'personel' => 'Personel',
            'okuyucu'  => 'Okuyucu',
            default    => ucfirst($this->role ?? '—'),
        };
    }

    /**
     * Yönetici mi?
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Kullanıcı yetkileri (user_yetkiler tablosu).
     *
     * Not: Admin kullanıcılar varsayılan olarak tüm yetkilere sahip kabul edilir.
     */
    public function hasYetki(int $yetkiNo): bool
    {
        if ($this->isAdmin()) return true;
        if ($yetkiNo < 1 || $yetkiNo > 21) return false;

        $row = $this->yetkilerRow();
        if (!$row) return false;

        $col = 'y' . str_pad((string) $yetkiNo, 2, '0', STR_PAD_LEFT);
        return (bool) ($row->{$col} ?? false);
    }

    /**
     * Yetki satırını getirir (request süresince cache).
     */
    public function yetkilerRow(): ?object
    {
        static $cache = [];

        $uid = (int) $this->id;
        if (array_key_exists($uid, $cache)) {
            return $cache[$uid];
        }

        $cache[$uid] = DB::table('user_yetkiler')->where('user_id', $uid)->first();
        return $cache[$uid];
    }

    /**
     * Kullanıcının aktif yetkili olduğu kütüphane id listesi (kutuphane_yetkili).
     */
    public function yetkiliKutuphaneIds(): array
    {
        static $cache = [];

        $uid = (int) $this->id;
        if (array_key_exists($uid, $cache)) {
            return $cache[$uid];
        }

        $cache[$uid] = DB::table('kutuphane_yetkili')
            ->where('user_id', $uid)
            ->whereNull('deleted_at')
            ->pluck('kutuphane_id')
            ->map(fn($v) => (int) $v)
            ->values()
            ->all();

        return $cache[$uid];
    }
}
