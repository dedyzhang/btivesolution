<?php

namespace App\Support;

/**
 * Normalisasi jenjang & katalog rekomendasi permainan Arena Belajar.
 */
class ArenaJenjang
{
    public const SD = 'sd';

    public const SMP = 'smp';

    public const SMA = 'sma';

    /** @return list<string> */
    public static function keys(): array
    {
        return [self::SD, self::SMP, self::SMA];
    }

    public static function label(?string $key): string
    {
        return match ($key) {
            self::SD => 'SD',
            self::SMP => 'SMP',
            self::SMA => 'SMA/SMK',
            default => 'Umum',
        };
    }

    /**
     * Infer jenjang dari grade_level / meta.
     */
    public static function fromGradeLevel(?string $gradeLevel, mixed $meta = null): string
    {
        if (is_array($meta) && isset($meta['jenjang']) && is_string($meta['jenjang'])) {
            $metaKey = strtolower($meta['jenjang']);
            if (in_array($metaKey, self::keys(), true)) {
                return $metaKey;
            }
            if (in_array($metaKey, ['sma', 'smk', 'sma/smk', 'sma-smk'], true)) {
                return self::SMA;
            }
        }

        $g = strtolower((string) $gradeLevel);

        if (str_contains($g, 'sma') || str_contains($g, 'smk') || str_contains($g, 'ma ')) {
            return self::SMA;
        }
        if (str_contains($g, 'smp') || str_contains($g, 'mts')) {
            return self::SMP;
        }
        if (str_contains($g, 'sd') || str_contains($g, 'mi ') || str_starts_with($g, 'mi')) {
            return self::SD;
        }

        return 'umum';
    }

    /**
     * Rekomendasi permainan edukatif per jenjang (mark katalog).
     *
     * @return array<string, list<array{title: string, mechanic: string, subject: string, why: string}>>
     */
    public static function rekomendasi(): array
    {
        return [
            self::SD => [
                [
                    'title' => 'Menjodohkan — Angka & Operasi',
                    'mechanic' => 'Menjodohkan',
                    'subject' => 'Matematika',
                    'why' => 'Latihan operasi dasar lewat pasangan cepat.',
                ],
                [
                    'title' => 'Recall Quiz — Hewan di Sekitarku',
                    'mechanic' => 'Kuis recall',
                    'subject' => 'IPA',
                    'why' => 'Mengenal hewan & habitat dengan soal singkat.',
                ],
                [
                    'title' => 'Puzzle — Cuci Tangan yang Benar',
                    'mechanic' => 'Puzzle',
                    'subject' => 'PJOK',
                    'why' => 'Urutkan langkah kebiasaan sehat.',
                ],
            ],
            self::SMP => [
                [
                    'title' => 'Recall Quiz — Gaya & Gerak',
                    'mechanic' => 'Kuis recall',
                    'subject' => 'IPA',
                    'why' => 'Konsep gaya, gerak, dan satuan secara ringkas.',
                ],
                [
                    'title' => 'Menjodohkan — Unsur & Simbol',
                    'mechanic' => 'Menjodohkan',
                    'subject' => 'IPA',
                    'why' => 'Hafalan simbol unsur jadi permainan pasangan.',
                ],
                [
                    'title' => 'Keputusan — Sampah Sekolahku',
                    'mechanic' => 'Keputusan',
                    'subject' => 'IPS',
                    'why' => 'Simulasi pilihan kebijakan lingkungan sekolah.',
                ],
            ],
            self::SMA => [
                [
                    'title' => 'Recall Quiz — Persamaan Linear',
                    'mechanic' => 'Kuis recall',
                    'subject' => 'Matematika',
                    'why' => 'Drill konsep persamaan linear satu variabel.',
                ],
                [
                    'title' => 'Narasi — Etika Digital di Dunia Kerja',
                    'mechanic' => 'Narasi',
                    'subject' => 'Informatika',
                    'why' => 'Pilih alur keputusan etika digital (cocok SMK/SMA).',
                ],
                [
                    'title' => 'Keputusan — Modal Usaha Siswa',
                    'mechanic' => 'Keputusan',
                    'subject' => 'PKWU',
                    'why' => 'Simulasi alokasi modal & risiko usaha kecil.',
                ],
            ],
        ];
    }

    /**
     * Rekomendasi tren 2025–2026 (AI, media, iklim, green computing, wellbeing).
     *
     * @return array<string, list<array{title: string, mechanic: string, subject: string, why: string, tren_tag: string}>>
     */
    public static function trenRekomendasi(): array
    {
        return [
            self::SD => [
                [
                    'title' => '[Tren] Puzzle — Jeda Layar Sehat',
                    'mechanic' => 'Puzzle',
                    'subject' => 'PJOK',
                    'tren_tag' => 'Digital wellbeing',
                    'why' => 'Tren wellbeing: urutan jeda layar sehat setelah belajar online.',
                ],
                [
                    'title' => '[Tren] Recall — Bumi Sehat, Kita Sehat',
                    'mechanic' => 'Kuis recall',
                    'subject' => 'IPA',
                    'tren_tag' => 'Literasi iklim',
                    'why' => 'Pengenalan iklim & aksi sederhana untuk anak SD.',
                ],
                [
                    'title' => '[Tren] Menjodohkan — Fakta vs Dongeng Online',
                    'mechanic' => 'Menjodohkan',
                    'subject' => 'Bahasa Indonesia',
                    'tren_tag' => 'Literasi media',
                    'why' => 'Dasar bedakan fakta dan dongeng di internet.',
                ],
            ],
            self::SMP => [
                [
                    'title' => '[Tren] Keputusan — Cek Fakta Sebelum Share',
                    'mechanic' => 'Keputusan',
                    'subject' => 'PPKn',
                    'tren_tag' => 'Literasi media',
                    'why' => 'Simulasi anti-hoaks sebelum membagikan berita viral.',
                ],
                [
                    'title' => '[Tren] Recall — Kenalan dengan AI',
                    'mechanic' => 'Kuis recall',
                    'subject' => 'Informatika',
                    'tren_tag' => 'Literasi AI',
                    'why' => 'Dasar AI: kemampuan, batas, dan penggunaan bertanggung jawab.',
                ],
                [
                    'title' => '[Tren] Keputusan — Gelombang Panas di Sekolah',
                    'mechanic' => 'Keputusan',
                    'subject' => 'IPS',
                    'tren_tag' => 'Literasi iklim',
                    'why' => 'Adaptasi iklim lokal: kebijakan sekolah saat suhu ekstrem.',
                ],
            ],
            self::SMA => [
                [
                    'title' => '[Tren] Recall — Prompt Cerdas, Bukan Nyolong',
                    'mechanic' => 'Kuis recall',
                    'subject' => 'Informatika',
                    'tren_tag' => 'Literasi AI',
                    'why' => 'Etika prompt & integritas akademik di era AI generatif.',
                ],
                [
                    'title' => '[Tren] Keputusan — Green Computing di Lab',
                    'mechanic' => 'Keputusan',
                    'subject' => 'Informatika',
                    'tren_tag' => 'Green computing',
                    'why' => 'Hemat energi & jejak karbon digital di lab komputer.',
                ],
                [
                    'title' => '[Tren] Narasi — Deepfake di Dunia Kerja',
                    'mechanic' => 'Narasi',
                    'subject' => 'Informatika',
                    'tren_tag' => 'AI & deepfake',
                    'why' => 'Respons aman saat video deepfake muncul di magang/kerja.',
                ],
            ],
        ];
    }
}
