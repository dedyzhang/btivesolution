<?php

namespace App\Support;

/**
 * Pembuat password default/reset yang RAMAH PENGGUNA (permintaan sekolah):
 * - 6 karakter saja (yang lama 8 campur huruf besar dianggap terlalu sulit),
 * - huruf kecil + angka saja,
 * - TANPA karakter yang mirip dan membingungkan: i, l, 1 (tampak sama di banyak font),
 *   serta o dan 0 (alasan yang sama).
 * Keamanan tetap terjaga lewat must_change_password: pengguna dipaksa ganti
 * password sendiri saat login pertama, jadi password sederhana ini berumur pendek.
 */
class PasswordSederhana
{
    public const CHARSET = 'abcdefghjkmnpqrstuvwxyz23456789';

    public static function buat(int $panjang = 6): string
    {
        $max = strlen(self::CHARSET) - 1;
        $hasil = '';
        for ($i = 0; $i < $panjang; $i++) {
            $hasil .= self::CHARSET[random_int(0, $max)];
        }

        return $hasil;
    }
}
