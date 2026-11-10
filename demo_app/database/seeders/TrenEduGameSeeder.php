<?php

namespace Database\Seeders;

use App\Models\Classroom;
use App\Models\Mission;
use App\Models\MissionAssignment;
use App\Models\MissionStep;
use App\Support\ArenaJenjang;
use Illuminate\Database\Seeder;

/**
 * Permainan edukatif tren 2025–2026 untuk Arena Belajar
 * (literasi AI, media/deepfake, iklim, green computing, wellbeing digital).
 *
 * Jalankan: php artisan db:seed --class=TrenEduGameSeeder
 */
class TrenEduGameSeeder extends Seeder
{
    public function run(): void
    {
        $classroom = Classroom::where('class_code', '2N3-ICS0')->first()
            ?? Classroom::where('status', 'published')->first();

        foreach ($this->catalog() as $item) {
            $mission = Mission::updateOrCreate(
                ['slug' => $item['slug']],
                [
                    'title' => $item['title'],
                    'subject' => $item['subject'],
                    'grade_level' => $item['grade_level'],
                    'mechanic_type' => $item['mechanic_type'],
                    'summary' => $item['summary'],
                    'duration_minutes' => $item['duration_minutes'],
                    'max_score' => 100,
                    'is_published' => true,
                    'status' => 'published',
                    'visible_to_teachers' => true,
                    'requires_reflection' => false,
                    'meta' => [
                        'demo' => true,
                        'jenjang' => $item['jenjang'],
                        'recommended' => true,
                        'tren' => '2025-2026',
                        'tren_tag' => $item['tren_tag'],
                        'kind' => 'tren_catalog',
                    ],
                ]
            );

            $this->replaceSteps($mission, $item['steps']);

            if ($classroom) {
                MissionAssignment::updateOrCreate(
                    [
                        'mission_id' => $mission->uuid,
                        'classroom_id' => $classroom->uuid,
                    ],
                    [
                        'assigned_by' => $classroom->created_by,
                        'status' => 'open',
                        'opens_at' => now()->subHour(),
                        'due_at' => now()->addDays(30),
                    ]
                );
            }
        }

        $this->command?->info('Katalog tren 2025–2026 siap (9 misi).');
    }

    /** @return list<array<string, mixed>> */
    private function catalog(): array
    {
        return [
            // ── SD — wellbeing + iklim + media ringan ──
            [
                'slug' => 'tren-sd-puzzle-jeda-layar',
                'title' => '[Tren] Puzzle — Jeda Layar Sehat',
                'subject' => 'PJOK',
                'grade_level' => 'SD 3–5',
                'jenjang' => ArenaJenjang::SD,
                'tren_tag' => 'Digital wellbeing',
                'mechanic_type' => 'puzzle_sequencing',
                'summary' => '[Tren 2025–2026 · SD] Urutkan langkah istirahat dari layar (digital wellbeing).',
                'duration_minutes' => 8,
                'steps' => [[
                    'module_key' => 'puzzle_sequencing',
                    'position' => 1,
                    'title' => 'Susun Jeda Sehat',
                    'prompt' => 'Urutkan langkah jeda layar yang sehat setelah belajar online.',
                    'body' => 'Geser kartu sampai urutannya benar.',
                    'payload' => [
                        'correct_order' => ['stop', 'stretch', 'water', 'outdoor', 'back'],
                        'labels' => [
                            'stop' => 'Hentikan layar 20 menit',
                            'stretch' => 'Regangkan leher & bahu',
                            'water' => 'Minum air putih',
                            'outdoor' => 'Lihat jauh / ke luar jendela',
                            'back' => 'Kembali belajar dengan fokus',
                        ],
                        'max_points' => 100,
                    ],
                    'max_points' => 100,
                ]],
            ],
            [
                'slug' => 'tren-sd-recall-bumi-sehat',
                'title' => '[Tren] Recall — Bumi Sehat, Kita Sehat',
                'subject' => 'IPA',
                'grade_level' => 'SD 4–5',
                'jenjang' => ArenaJenjang::SD,
                'tren_tag' => 'Literasi iklim',
                'mechanic_type' => 'recall_quiz',
                'summary' => '[Tren 2025–2026 · SD] Kuis singkat perubahan iklim untuk anak SD.',
                'duration_minutes' => 10,
                'steps' => [[
                    'module_key' => 'recall_quiz',
                    'position' => 1,
                    'title' => 'Kuis Bumi Sehat',
                    'prompt' => 'Jawab pertanyaan tentang bumi dan iklim.',
                    'body' => null,
                    'payload' => [
                        'questions' => [
                            [
                                'question' => 'Gas yang membuat bumi semakin panas disebut gas...',
                                'options' => ['Rumah kaca', 'Oksigen murni', 'Helium', 'Nitrogen saja'],
                                'answer' => 'Rumah kaca',
                            ],
                            [
                                'question' => 'Cara sederhana mengurangi sampah plastik adalah...',
                                'options' => ['Bawa botol minum sendiri', 'Bakar semua plastik', 'Buang ke sungai', 'Beli plastik sekali pakai'],
                                'answer' => 'Bawa botol minum sendiri',
                            ],
                            [
                                'question' => 'Pohon membantu bumi karena...',
                                'options' => ['Menyerap karbon dioksida', 'Membuat hujan asam', 'Menghasilkan plastik', 'Menambah sampah'],
                                'answer' => 'Menyerap karbon dioksida',
                            ],
                        ],
                    ],
                    'max_points' => 100,
                ]],
            ],
            [
                'slug' => 'tren-sd-matching-fakta-dongeng',
                'title' => '[Tren] Menjodohkan — Fakta vs Dongeng Online',
                'subject' => 'Bahasa Indonesia',
                'grade_level' => 'SD 4–5',
                'jenjang' => ArenaJenjang::SD,
                'tren_tag' => 'Literasi media',
                'mechanic_type' => 'quiz_matching',
                'summary' => '[Tren 2025–2026 · SD] Bedakan pernyataan fakta dan dongeng di internet.',
                'duration_minutes' => 8,
                'steps' => [[
                    'module_key' => 'matching',
                    'position' => 1,
                    'title' => 'Pasangkan Jenis Informasi',
                    'prompt' => 'Pasangkan pernyataan dengan jenisnya: Fakta atau Dongeng.',
                    'body' => null,
                    'payload' => [
                        'pairs' => [
                            ['term' => 'Matahari terbit di timur', 'answer' => 'Fakta'],
                            ['term' => 'Kucing bisa terbang ke bulan', 'answer' => 'Dongeng'],
                            ['term' => 'Air mendidih pada 100°C', 'answer' => 'Fakta'],
                            ['term' => 'Pohon bicara setiap malam', 'answer' => 'Dongeng'],
                        ],
                        'options' => ['Fakta', 'Dongeng', 'Iklan', 'Teka-teki'],
                    ],
                    'max_points' => 100,
                ]],
            ],

            // ── SMP — AI literacy, fact-check, iklim ──
            [
                'slug' => 'tren-smp-keputusan-cek-fakta',
                'title' => '[Tren] Keputusan — Cek Fakta Sebelum Share',
                'subject' => 'PPKn',
                'grade_level' => 'SMP 7–9',
                'jenjang' => ArenaJenjang::SMP,
                'tren_tag' => 'Literasi media',
                'mechanic_type' => 'strategic_decision',
                'summary' => '[Tren 2025–2026 · SMP] Simulasi keputusan saat viral berita meragukan di grup kelas.',
                'duration_minutes' => 12,
                'steps' => [[
                    'module_key' => 'strategic_decision',
                    'position' => 1,
                    'title' => 'Grup Kelas Viral',
                    'prompt' => 'Berita meragukan menyebar di grup. Pilih tindakan terbaik tiap putaran.',
                    'body' => 'Jaga kepercayaan teman, akurasi info, dan ketenangan grup.',
                    'payload' => [
                        'rounds' => [
                            ['ideal_choice' => 'Cek sumber resmi dulu', 'weight' => 34],
                            ['ideal_choice' => 'Tanya guru/wali sebelum share', 'weight' => 33],
                            ['ideal_choice' => 'Laporkan jika hoaks terbukti', 'weight' => 33],
                        ],
                        'thresholds' => ['stability' => 70, 'trust' => 70, 'budget' => 40],
                        'bonus_points' => 10,
                        'max_points' => 100,
                    ],
                    'max_points' => 100,
                ]],
            ],
            [
                'slug' => 'tren-smp-recall-kenalan-ai',
                'title' => '[Tren] Recall — Kenalan dengan AI',
                'subject' => 'Informatika',
                'grade_level' => 'SMP 8–9',
                'jenjang' => ArenaJenjang::SMP,
                'tren_tag' => 'Literasi AI',
                'mechanic_type' => 'recall_quiz',
                'summary' => '[Tren 2025–2026 · SMP] Dasar AI: apa itu, bisa apa, dan batasnya.',
                'duration_minutes' => 12,
                'steps' => [[
                    'module_key' => 'recall_quiz',
                    'position' => 1,
                    'title' => 'Kuis Literasi AI',
                    'prompt' => 'Jawab pertanyaan dasar tentang AI.',
                    'body' => null,
                    'payload' => [
                        'questions' => [
                            [
                                'question' => 'AI belajar pola dari data. Proses ini disebut...',
                                'options' => ['Machine learning', 'Fotokopi', 'Browsing saja', 'Format ulang'],
                                'answer' => 'Machine learning',
                            ],
                            [
                                'question' => 'Jawaban AI selalu benar. Pernyataan ini...',
                                'options' => ['Salah — perlu dicek lagi', 'Benar selalu', 'Hanya untuk matematika', 'Hanya untuk bahasa'],
                                'answer' => 'Salah — perlu dicek lagi',
                            ],
                            [
                                'question' => 'Contoh penggunaan AI yang bertanggung jawab di sekolah adalah...',
                                'options' => ['Meminta penjelasan konsep lalu dikerjakan sendiri', 'Menyalin seluruh jawaban AI ke tugas', 'Menghapus sumber', 'Menipu teman'],
                                'answer' => 'Meminta penjelasan konsep lalu dikerjakan sendiri',
                            ],
                        ],
                    ],
                    'max_points' => 100,
                ]],
            ],
            [
                'slug' => 'tren-smp-keputusan-gelombang-panas',
                'title' => '[Tren] Keputusan — Gelombang Panas di Sekolah',
                'subject' => 'IPS',
                'grade_level' => 'SMP 7–9',
                'jenjang' => ArenaJenjang::SMP,
                'tren_tag' => 'Literasi iklim',
                'mechanic_type' => 'strategic_decision',
                'summary' => '[Tren 2025–2026 · SMP] Simulasi adaptasi iklim: gelombang panas di lingkungan sekolah.',
                'duration_minutes' => 12,
                'steps' => [[
                    'module_key' => 'strategic_decision',
                    'position' => 1,
                    'title' => 'Rapat Adaptasi',
                    'prompt' => 'Suhu ekstrem mengancam kegiatan sekolah. Pilih kebijakan tiap putaran.',
                    'body' => 'Jaga kesehatan siswa, kelangsungan belajar, dan anggaran.',
                    'payload' => [
                        'rounds' => [
                            ['ideal_choice' => 'Geser jam olahraga ke pagi', 'weight' => 34],
                            ['ideal_choice' => 'Sediakan titik air minum', 'weight' => 33],
                            ['ideal_choice' => 'Tanam pohon peneduh', 'weight' => 33],
                        ],
                        'thresholds' => ['stability' => 72, 'trust' => 68, 'budget' => 38],
                        'bonus_points' => 10,
                        'max_points' => 100,
                    ],
                    'max_points' => 100,
                ]],
            ],

            // ── SMA/SMK — prompt literacy, green computing, deepfake ──
            [
                'slug' => 'tren-sma-recall-prompt-cerdas',
                'title' => '[Tren] Recall — Prompt Cerdas, Bukan Nyolong',
                'subject' => 'Informatika',
                'grade_level' => 'SMA/SMK 10–12',
                'jenjang' => ArenaJenjang::SMA,
                'tren_tag' => 'Literasi AI',
                'mechanic_type' => 'recall_quiz',
                'summary' => '[Tren 2025–2026 · SMA/SMK] Etika prompt & integritas akademik di era AI generatif.',
                'duration_minutes' => 12,
                'steps' => [[
                    'module_key' => 'recall_quiz',
                    'position' => 1,
                    'title' => 'Kuis Prompt & Integritas',
                    'prompt' => 'Jawab pertanyaan tentang penggunaan AI yang etis.',
                    'body' => null,
                    'payload' => [
                        'questions' => [
                            [
                                'question' => 'Prompt yang baik biasanya...',
                                'options' => ['Jelas, spesifik, dan menyebut konteks', 'Sangat pendek tanpa konteks', 'Meminta kunci ujian', 'Menyalin soal utuh tanpa berpikir'],
                                'answer' => 'Jelas, spesifik, dan menyebut konteks',
                            ],
                            [
                                'question' => 'Mengumpulkan tugas 100% dari AI tanpa deklarasi termasuk...',
                                'options' => ['Pelanggaran integritas akademik', 'Praktik terbaik', 'Wajib sekolah', 'Tidak masalah jika nilai tinggi'],
                                'answer' => 'Pelanggaran integritas akademik',
                            ],
                            [
                                'question' => 'Cara aman memakai AI untuk riset adalah...',
                                'options' => ['Pakai sebagai asisten brainstorm, cek sumber, tulis ulang sendiri', 'Paste langsung ke rapor', 'Hapus sitasi', 'Abaikan fakta'],
                                'answer' => 'Pakai sebagai asisten brainstorm, cek sumber, tulis ulang sendiri',
                            ],
                        ],
                    ],
                    'max_points' => 100,
                ]],
            ],
            [
                'slug' => 'tren-sma-keputusan-green-computing',
                'title' => '[Tren] Keputusan — Green Computing di Lab',
                'subject' => 'Informatika',
                'grade_level' => 'SMA/SMK 10–12',
                'jenjang' => ArenaJenjang::SMA,
                'tren_tag' => 'Green computing',
                'mechanic_type' => 'strategic_decision',
                'summary' => '[Tren 2025–2026 · SMA/SMK] Simulasi hemat energi & jejak karbon digital di lab komputer.',
                'duration_minutes' => 14,
                'steps' => [[
                    'module_key' => 'strategic_decision',
                    'position' => 1,
                    'title' => 'Manajemen Lab Hijau',
                    'prompt' => 'Lab komputer boros listrik. Pilih kebijakan tiap putaran.',
                    'body' => 'Jaga performa belajar, hemat energi, dan anggaran perawatan.',
                    'payload' => [
                        'rounds' => [
                            ['ideal_choice' => 'Matikan PC idle otomatis', 'weight' => 34],
                            ['ideal_choice' => 'Jadwal server malam hemat daya', 'weight' => 33],
                            ['ideal_choice' => 'Kampanye cloud folder rapi', 'weight' => 33],
                        ],
                        'thresholds' => ['stability' => 70, 'trust' => 65, 'budget' => 40],
                        'bonus_points' => 10,
                        'max_points' => 100,
                    ],
                    'max_points' => 100,
                ]],
            ],
            [
                'slug' => 'tren-sma-narasi-deepfake',
                'title' => '[Tren] Narasi — Deepfake di Dunia Kerja',
                'subject' => 'Informatika',
                'grade_level' => 'SMA/SMK 11–12',
                'jenjang' => ArenaJenjang::SMA,
                'tren_tag' => 'AI & deepfake',
                'mechanic_type' => 'interactive_narrative',
                'summary' => '[Tren 2025–2026 · SMA/SMK] Pilih alur respons saat video deepfake muncul di tempat magang.',
                'duration_minutes' => 15,
                'steps' => [[
                    'module_key' => 'interactive_narrative',
                    'position' => 1,
                    'title' => 'Video Viral di Kantor',
                    'prompt' => 'Video “bos” meminta transfer dana via chat. Apa langkah terbaik?',
                    'body' => 'Pilih 3 langkah berurutan yang paling aman dan etis.',
                    'payload' => [
                        'start_node' => 'alert',
                        'expected_path' => ['Verifikasi kanal resmi', 'Laporkan ke IT/security', 'Jangan transfer dulu'],
                        'accepted_end_nodes' => ['finish', 'secure'],
                        'max_points' => 100,
                    ],
                    'max_points' => 100,
                ]],
            ],
        ];
    }

    /** @param list<array<string, mixed>> $steps */
    private function replaceSteps(Mission $mission, array $steps): void
    {
        $mission->steps()->delete();
        foreach ($steps as $step) {
            MissionStep::create(array_merge($step, [
                'mission_id' => $mission->uuid,
            ]));
        }
    }
}
