<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
        'ldap_username',
        'telefon',
        'il',
        'ilce',
        'mahalle',
        'acik_adres',
        'password',
        'role',
        'statu',
        'twofactor',
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
            'twofactor'         => 'boolean',
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
     * Kullanıcının atanmış yetkileri (permissions + user_permission).
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'user_permission')
            ->using(UserPermission::class)
            ->withPivot(['granted_by', 'created_at', 'updated_at']);
    }

    /**
     * Kullanıcı yetkileri.
     *
     * Not: Admin kullanıcılar varsayılan olarak tüm yetkilere sahip kabul edilir.
     */
    public function hasYetki(int $yetkiNo): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        if ($yetkiNo < 1 || ! in_array($yetkiNo, self::definedPermissionLegacyNos(), true)) {
            return false;
        }

        return in_array($yetkiNo, $this->permissionLegacyNos(), true);
    }

    /**
     * Sistemde tanımlı yetki numaraları (request süresince cache).
     *
     * @return list<int>
     */
    protected static function definedPermissionLegacyNos(): array
    {
        static $cache;

        if ($cache !== null) {
            return $cache;
        }

        $cache = Permission::query()
            ->pluck('legacy_no')
            ->map(fn ($v) => (int) $v)
            ->values()
            ->all();

        return $cache;
    }

    /**
     * Kullanıcının sahip olduğu yetki numaraları (request süresince cache).
     *
     * @return list<int>
     */
    public function permissionLegacyNos(): array
    {
        static $cache = [];

        $uid = (int) $this->id;
        if (array_key_exists($uid, $cache)) {
            return $cache[$uid];
        }

        $cache[$uid] = $this->permissions()
            ->pluck('permissions.legacy_no')
            ->map(fn ($v) => (int) $v)
            ->values()
            ->all();

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
