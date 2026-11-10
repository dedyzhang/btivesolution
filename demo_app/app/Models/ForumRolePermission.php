<?php

namespace App\Models;

use App\Support\UserRole;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * Matriks izin forum (access-role × permission). Sumber kebenaran izin forum,
 * dibaca oleh User::canForum() saat runtime.
 */
class ForumRolePermission extends Model
{
    use HasUuids;

    protected $table = 'forum_role_permissions';
    protected $primaryKey = 'uuid';
    protected $fillable = ['access', 'permission', 'allowed'];

    protected function casts(): array
    {
        return ['allowed' => 'boolean'];
    }

    /** Cache per-request: [access][permission] => bool. */
    protected static ?array $cache = null;

    /**
     * Kunci matriks dikanonikalisasi: baris lama bisa tersimpan dengan alias
     * 'sapras' sementara users.access kini selalu 'sarpras' — tanpa ini, izin
     * yang sudah diatur admin tidak pernah cocok dan staf sarpras kehilangan
     * seluruh akses forum.
     */
    public static function matrix(): array
    {
        if (static::$cache === null) {
            static::$cache = [];

            // Baris beralias lama diterapkan lebih dulu agar, bila keduanya ada,
            // konfigurasi dengan ejaan kanonik yang menang (bukan urutan acak DB).
            $rows = static::all()->sortBy(
                fn ($row) => UserRole::canonicalize((string) $row->access) === $row->access ? 1 : 0
            );

            foreach ($rows as $row) {
                $access = UserRole::canonicalize((string) $row->access);
                static::$cache[$access][$row->permission] = (bool) $row->allowed;
            }
        }
        return static::$cache;
    }

    public static function granted(string $access, string $permission): bool
    {
        return static::matrix()[UserRole::canonicalize($access)][$permission] ?? false;
    }

    public static function clearCache(): void
    {
        static::$cache = null;
    }
}
