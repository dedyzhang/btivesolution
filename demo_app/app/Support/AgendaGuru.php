<?php

namespace App\Support;

use App\Models\Agenda;
use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\Setting;

/**
 * Aturan: guru wajib mengisi agenda untuk semua jam mengajarnya pada hari berjalan
 * sebelum boleh absen pulang. Dipakai oleh presensi wajah & absen QR.
 */
class AgendaGuru
{
    /** Apakah pengecekan agenda saat absen pulang diaktifkan (default: ya). */
    public static function wajibSebelumPulang(): bool
    {
        return Setting::get('agenda_wajib_pulang', '1') === '1';
    }

    /**
     * Daftar slot (kelas + mapel) terjadwal pada $tanggal yang BELUM ada agendanya.
     * @return string[] label "8A Informatika"
     */
    public static function belumDiisi(Guru $guru, ?string $tanggal = null): array
    {
        $tanggal = $tanggal ?: now()->toDateString();
        $hariKe = (int) date('N', strtotime($tanggal));   // 1=Senin..7=Minggu
        if ($hariKe > 6) {
            return [];
        }

        // Hormati kalender: bila agenda tidak diwajibkan pada tanggal ini, tak ada tunggakan.
        if (!KalenderAbsensi::agendaWajib($tanggal)) {
            return [];
        }

        $jadwals = Jadwal::with(['kelas', 'pelajaran'])
            ->where('id_guru', $guru->uuid)
            ->where('hari', $hariKe)
            ->whereNotNull('id_pelajaran')
            ->get();
        if ($jadwals->isEmpty()) {
            return [];
        }

        $sudah = Agenda::where('id_guru', $guru->uuid)
            ->whereDate('tanggal', $tanggal)
            ->get()
            ->keyBy(fn ($a) => $a->id_kelas . '|' . $a->id_pelajaran);

        $belum = [];
        foreach ($jadwals as $j) {
            $key = $j->id_kelas . '|' . $j->id_pelajaran;
            if (isset($belum[$key]) || $sudah->has($key)) {
                continue;
            }
            $belum[$key] = trim(($j->kelas ? $j->kelas->tingkat . $j->kelas->kelas : '-') . ' ' . ($j->pelajaran?->nama ?? ''));
        }

        return array_values($belum);
    }

    /** True bila guru sudah boleh absen pulang (agenda lengkap atau aturan nonaktif). */
    public static function bolehPulang(Guru $guru, ?string $tanggal = null): bool
    {
        if (!self::wajibSebelumPulang()) {
            return true;
        }
        return count(self::belumDiisi($guru, $tanggal)) === 0;
    }

    /** Pesan penolakan standar bila agenda belum lengkap. */
    public static function pesanTolak(array $belum): string
    {
        $n = count($belum);
        $contoh = implode(', ', array_slice($belum, 0, 3));
        $sisa = $n > 3 ? ' (+' . ($n - 3) . ' lagi)' : '';

        return "Isi agenda dulu sebelum absen pulang. Masih ada {$n} jam mengajar belum diisi: {$contoh}{$sisa}.";
    }
}
