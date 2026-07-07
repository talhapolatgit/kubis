<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPermissionLog extends Model
{
    protected $table = 'user_permission_log';

    public const ACTION_GRANTED = 'granted';

    public const ACTION_REVOKED = 'revoked';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'permission_id',
        'action',
        'performed_by',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function permission(): BelongsTo
    {
        return $this->belongsTo(Permission::class);
    }

    public function performedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    public static function record(int $userId, int $permissionId, string $action, ?int $performedBy): void
    {
        static::create([
            'user_id'       => $userId,
            'permission_id' => $permissionId,
            'action'        => $action,
            'performed_by'  => $performedBy,
            'created_at'    => now(),
        ]);
    }

    public function actionLabel(): string
    {
        return match ($this->action) {
            self::ACTION_GRANTED => 'Verildi',
            self::ACTION_REVOKED => 'Kaldırıldı',
            default              => $this->action,
        };
    }
}
