<?php

namespace Database\Seeders\Concerns;

use App\Models\Classroom;

/**
 * Pemilihan kelas tujuan untuk seeder demo (Arena Belajar & Jagat Misi).
 * Dipakai bersama agar heuristiknya tidak kembali menyimpang antar-seeder —
 * perbedaan seperti itu pernah membuat demo ter-seed ke kelas yang salah.
 */
trait ResolvesDemoClassroom
{
    /** Kelas contoh bawaan; dipakai lebih dulu bila memang ada. */
    private const DEMO_CLASS_CODE = '2N3-ICS0';

    /**
     * Utamakan kelas yang benar-benar punya siswa: sejak penugasan mengajar
     * auto-provision ruang mapel (NgajarObserver), "published pertama" sering
     * berupa ruang kosong tanpa anggota sehingga demo tak bisa dimainkan siswa.
     */
    private function resolveDemoClassroom(): ?Classroom
    {
        $preferred = Classroom::where('class_code', self::DEMO_CLASS_CODE)->first()
            ?? Classroom::where('status', 'published')
                ->whereHas('members', fn ($q) => $q->where('role_in_class', 'siswa'))
                ->first();

        if ($preferred) {
            return $preferred;
        }

        $fallback = Classroom::where('status', 'published')->first();

        if ($fallback) {
            // Jangan diam-diam: demo tetap dibuat, tapi belum tentu bisa dicoba siswa.
            $this->command?->warn(
                'Tidak ada kelas published yang punya siswa — memakai "'.$fallback->title.'". '
                .'Demo ter-seed, tetapi belum tentu bisa dimainkan siswa.'
            );
        }

        return $fallback;
    }
}
