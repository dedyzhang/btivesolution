<?php

namespace App\Support;

/**
 * Tanda tangan HMAC yang disisipkan ke custom document properties file Excel,
 * dipakai untuk membuktikan sebuah file benar-benar hasil export aplikasi ini
 * (bukan Excel buatan sendiri) sebelum diproses saat import.
 */
class ExcelWatermark
{
    public static function sign(string $tag): string
    {
        return hash_hmac('sha256', $tag, (string) config('app.key'));
    }

    public static function verify(string $tag, ?string $signature): bool
    {
        if (!$signature) {
            return false;
        }
        return hash_equals(self::sign($tag), $signature);
    }
}
