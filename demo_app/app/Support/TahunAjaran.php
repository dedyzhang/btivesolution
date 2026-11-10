<?php

namespace App\Support;

use Carbon\Carbon;

/**
 * Helper Tahun Ajaran untuk modul Keuangan/SPP.
 *
 * Satu tahun ajaran berjalan dari Juli s/d Juni tahun berikutnya, mis.
 * "2025/2026" = Juli 2025 .. Juni 2026.
 *
 * Bulan disimpan sebagai indeks 1..12 dengan 1 = Juli, 12 = Juni. Pemetaan
 * ke bulan kalender & label dilakukan lewat helper di sini agar konsisten.
 */
class TahunAjaran
{
    /** Label bulan (indeks 1..12, 1 = Juli) → [label, bulan kalender 1..12]. */
    public const BULAN = [
        1  => ['Juli', 7],
        2  => ['Agustus', 8],
        3  => ['September', 9],
        4  => ['Oktober', 10],
        5  => ['November', 11],
        6  => ['Desember', 12],
        7  => ['Januari', 1],
        8  => ['Februari', 2],
        9  => ['Maret', 3],
        10 => ['April', 4],
        11 => ['Mei', 5],
        12 => ['Juni', 6],
    ];

    /**
     * Tahun ajaran aktif berdasarkan tanggal. Jika bulan >= Juli memakai
     * tahun berjalan, jika tidak memakai tahun sebelumnya.
     */
    public static function current(?Carbon $date = null): string
    {
        $date ??= Carbon::now();
        $y = (int) $date->year;
        return $date->month >= 7 ? "{$y}/" . ($y + 1) : ($y - 1) . "/{$y}";
    }

    /**
     * Daftar pilihan tahun ajaran untuk dropdown (beberapa tahun ke belakang
     * & 1 ke depan dari tahun ajaran aktif).
     *
     * @return string[]
     */
    public static function options(int $back = 3, int $forward = 1): array
    {
        $cur = self::current();
        $startYear = (int) explode('/', $cur)[0];
        $list = [];
        for ($i = $back; $i >= -$forward; $i--) {
            $y = $startYear - $i;
            $list[] = "{$y}/" . ($y + 1);
        }
        return $list;
    }

    /** Tahun awal (Juli) dari string tahun ajaran "2025/2026" → 2025. */
    public static function tahunAwal(string $tahunAjaran): int
    {
        return (int) explode('/', $tahunAjaran)[0];
    }

    /**
     * Tanggal kalender (awal bulan) untuk indeks bulan 1..12 pada tahun ajaran.
     * Juli–Desember memakai tahun awal, Januari–Juni memakai tahun berikutnya.
     */
    public static function tanggal(string $tahunAjaran, int $bulanIdx): Carbon
    {
        [, $cal] = self::BULAN[$bulanIdx] ?? ['', 7];
        $awal = self::tahunAwal($tahunAjaran);
        $year = $bulanIdx <= 6 ? $awal : $awal + 1;
        return Carbon::create($year, $cal, 1)->startOfDay();
    }

    /** Label lengkap bulan, mis. "Juli 2025". */
    public static function labelBulan(string $tahunAjaran, int $bulanIdx): string
    {
        [$label] = self::BULAN[$bulanIdx] ?? ['-', 7];
        return $label . ' ' . self::tanggal($tahunAjaran, $bulanIdx)->year;
    }

    /**
     * Daftar 12 bulan tahun ajaran beserta meta-nya.
     *
     * @return array<int, array{idx:int,label:string,cal:int,year:int,tanggal:Carbon}>
     */
    public static function bulanList(string $tahunAjaran): array
    {
        $out = [];
        foreach (self::BULAN as $idx => [$label, $cal]) {
            $tgl = self::tanggal($tahunAjaran, $idx);
            $out[] = [
                'idx'     => $idx,
                'label'   => $label,
                'cal'     => $cal,
                'year'    => $tgl->year,
                'tanggal' => $tgl,
            ];
        }
        return $out;
    }
}
