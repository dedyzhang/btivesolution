<?php

namespace App\Support;

use App\Models\KaihJawaban;
use App\Models\Setting;

/**
 * Aturan: siswa wajib mengisi kuesioner 7 KAIH (7 Kebiasaan Anak Indonesia Hebat)
 * pada hari berjalan sebelum boleh absen (QR maupun scan wajah).
 *
 * Penegakan bisa dibatasi kalender (Kalender Absensi → Batasi Wajib 7 KAIH).
 */
class KaihSiswa
{
    /** Apakah fitur KAIH sebelum absen diaktifkan secara global (default: ya). */
    public static function wajibSebelumAbsen(): bool
    {
        return Setting::get('kaih_wajib_sebelum_absen', '1') === '1';
    }

    /**
     * Apakah KAIH wajib diisi sebelum absen pada tanggal ini.
     * - Fitur global OFF → tidak wajib.
     * - Batasi kalender OFF → wajib setiap hari (jika fitur global ON).
     * - Batasi kalender ON → hanya tanggal bertanda chip 7 KAIH.
     */
    public static function wajibPadaTanggal(?string $tanggal = null): bool
    {
        if (! self::wajibSebelumAbsen()) {
            return false;
        }

        $tanggal = $tanggal ?: now()->toDateString();

        return KalenderAbsensi::kaihWajib($tanggal);
    }

    /** Sudah adakah jawaban (diisi ATAU dilewati) untuk siswa pada tanggal ini. */
    public static function sudahDiisi(string $idSiswa, ?string $tanggal = null): bool
    {
        $tanggal = $tanggal ?: now()->toDateString();

        return KaihJawaban::where('id_siswa', $idSiswa)->whereDate('tanggal', $tanggal)->exists();
    }

    /** True bila siswa sudah boleh absen (KAIH terisi/dilewati, atau tidak wajib hari ini). */
    public static function bolehAbsen(string $idSiswa, ?string $tanggal = null): bool
    {
        if (! self::wajibPadaTanggal($tanggal)) {
            return true;
        }

        return self::sudahDiisi($idSiswa, $tanggal);
    }

    /** Pesan penolakan standar bila KAIH belum diisi. */
    public static function pesanTolak(): string
    {
        return 'Isi kuesioner 7 KAIH hari ini dulu sebelum absen (menu "Isi 7 KAIH" di dashboard).';
    }
}
