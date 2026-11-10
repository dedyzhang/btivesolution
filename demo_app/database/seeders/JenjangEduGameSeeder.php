<?php

namespace Database\Seeders;

use App\Models\Classroom;
use App\Models\Mission;
use App\Models\MissionAssignment;
use App\Models\MissionStep;
use App\Support\ArenaJenjang;
use Illuminate\Database\Seeder;

/**
 * Permainan edukatif demo per jenjang SD / SMP / SMA-SMK untuk Arena Belajar.
 * Jalankan: php artisan db:seed --class=JenjangEduGameSeeder
 */
class JenjangEduGameSeeder extends Seeder
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
                        'kind' => 'jenjang_catalog',
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

        $this->command?->info('Katalog permainan per jenjang SD/SMP/SMA-SMK siap (9 misi).');
    }

    /** @return list<array<string, mixed>> */
    private function catalog(): array
    {
        return [
            // ── SD ──
            [
                'slug' => 'jenjang-sd-menjodohkan-angka',
                'title' => 'Menjodohkan — Angka & Operasi',
                'subject' => 'Matematika',
                'grade_level' => 'SD 3–4',
                'jenjang' => ArenaJenjang::SD,
                'mechanic_type' => 'quiz_matching',
                'summary' => '[SD] Pasangkan operasi hitung dengan hasilnya. Cocok kelas 3–4.',
                'duration_minutes' => 8,
                'steps' => [[
                    'module_key' => 'matching',
                    'position' => 1,
                    'title' => 'Pasangkan Hasil',
                    'prompt' => 'Pasangkan operasi dengan hasil yang benar.',
                    'body' => null,
                    'payload' => [
                        'pairs' => [
                            ['term' => '7 + 5', 'answer' => '12'],
                            ['term' => '9 − 4', 'answer' => '5'],
                            ['term' => '3 × 4', 'answer' => '12'],
                            ['term' => '16 ÷ 4', 'answer' => '4'],
                        ],
                        'options' => ['12', '5', '4', '8', '11'],
                    ],
                    'max_points' => 100,
                ]],
            ],
            [
                'slug' => 'jenjang-sd-recall-hewan',
                'title' => 'Recall Quiz — Hewan di Sekitarku',
                'subject' => 'IPA',
                'grade_level' => 'SD 3–4',
                'jenjang' => ArenaJenjang::SD,
                'mechanic_type' => 'recall_quiz',
                'summary' => '[SD] Kuis singkat tentang hewan, makanan, dan tempat hidup.',
                'duration_minutes' => 10,
                'steps' => [[
                    'module_key' => 'recall_quiz',
                    'position' => 1,
                    'title' => 'Kuis Hewan',
                    'prompt' => 'Jawab pertanyaan berikut.',
                    'body' => null,
                    'payload' => [
                        'questions' => [
                            [
                                'question' => 'Hewan yang makan tumbuhan disebut...',
                                'options' => ['Herbivora', 'Karnivora', 'Omnivora', 'Predator'],
                                'answer' => 'Herbivora',
                            ],
                            [
                                'question' => 'Ikan bernapas dengan...',
                                'options' => ['Insang', 'Paru-paru', 'Kulit', 'Hidung'],
                                'answer' => 'Insang',
                            ],
                            [
                                'question' => 'Burung membangun rumah yang disebut...',
                                'options' => ['Sarang', 'Gua', 'Lubang', 'Kandang'],
                                'answer' => 'Sarang',
                            ],
                        ],
                    ],
                    'max_points' => 100,
                ]],
            ],
            [
                'slug' => 'jenjang-sd-puzzle-cuci-tangan',
                'title' => 'Puzzle — Cuci Tangan yang Benar',
                'subject' => 'PJOK',
                'grade_level' => 'SD 3–4',
                'jenjang' => ArenaJenjang::SD,
                'mechanic_type' => 'puzzle_sequencing',
                'summary' => '[SD] Urutkan langkah cuci tangan yang benar.',
                'duration_minutes' => 8,
                'steps' => [[
                    'module_key' => 'puzzle_sequencing',
                    'position' => 1,
                    'title' => 'Susun Langkah',
                    'prompt' => 'Urutkan langkah cuci tangan dari awal sampai kering.',
                    'body' => 'Geser kartu sampai urutannya benar.',
                    'payload' => [
                        'correct_order' => ['basahi', 'sabun', 'gosok', 'bilas', 'kering'],
                        'labels' => [
                            'basahi' => 'Basahi tangan dengan air',
                            'sabun' => 'Pakai sabun',
                            'gosok' => 'Gosok 20 detik',
                            'bilas' => 'Bilas sampai bersih',
                            'kering' => 'Keringkan dengan lap bersih',
                        ],
                        'max_points' => 100,
                    ],
                    'max_points' => 100,
                ]],
            ],

            // ── SMP ──
            [
                'slug' => 'jenjang-smp-recall-gaya',
                'title' => 'Recall Quiz — Gaya & Gerak',
                'subject' => 'IPA',
                'grade_level' => 'SMP 7–8',
                'jenjang' => ArenaJenjang::SMP,
                'mechanic_type' => 'recall_quiz',
                'summary' => '[SMP] Kuis konsep gaya, gerak, dan satuan SI.',
                'duration_minutes' => 12,
                'steps' => [[
                    'module_key' => 'recall_quiz',
                    'position' => 1,
                    'title' => 'Kuis Gaya & Gerak',
                    'prompt' => 'Jawab pertanyaan berikut.',
                    'body' => null,
                    'payload' => [
                        'questions' => [
                            [
                                'question' => 'Satuan gaya dalam SI adalah...',
                                'options' => ['Newton', 'Joule', 'Watt', 'Pascal'],
                                'answer' => 'Newton',
                            ],
                            [
                                'question' => 'Benda diam tetap diam jika resultan gayanya...',
                                'options' => ['Nol', 'Besar', 'Bertambah', 'Berubah arah'],
                                'answer' => 'Nol',
                            ],
                            [
                                'question' => 'Percepatan gravitasi Bumi kira-kira...',
                                'options' => ['10 m/s²', '10 km/jam', '10 N', '10 kg'],
                                'answer' => '10 m/s²',
                            ],
                        ],
                    ],
                    'max_points' => 100,
                ]],
            ],
            [
                'slug' => 'jenjang-smp-matching-unsur',
                'title' => 'Menjodohkan — Unsur & Simbol',
                'subject' => 'IPA',
                'grade_level' => 'SMP 8–9',
                'jenjang' => ArenaJenjang::SMP,
                'mechanic_type' => 'quiz_matching',
                'summary' => '[SMP] Pasangkan nama unsur dengan simbol kimianya.',
                'duration_minutes' => 10,
                'steps' => [[
                    'module_key' => 'matching',
                    'position' => 1,
                    'title' => 'Pasangkan Simbol',
                    'prompt' => 'Pasangkan unsur dengan simbol yang benar.',
                    'body' => null,
                    'payload' => [
                        'pairs' => [
                            ['term' => 'Oksigen', 'answer' => 'O'],
                            ['term' => 'Natrium', 'answer' => 'Na'],
                            ['term' => 'Besi', 'answer' => 'Fe'],
                            ['term' => 'Emas', 'answer' => 'Au'],
                        ],
                        'options' => ['O', 'Na', 'Fe', 'Au', 'Ag'],
                    ],
                    'max_points' => 100,
                ]],
            ],
            [
                'slug' => 'jenjang-smp-keputusan-sampah',
                'title' => 'Keputusan — Sampah Sekolahku',
                'subject' => 'IPS',
                'grade_level' => 'SMP 7–8',
                'jenjang' => ArenaJenjang::SMP,
                'mechanic_type' => 'strategic_decision',
                'summary' => '[SMP] Simulasi keputusan mengelola sampah di sekolah.',
                'duration_minutes' => 12,
                'steps' => [[
                    'module_key' => 'strategic_decision',
                    'position' => 1,
                    'title' => 'Rapat OSIS',
                    'prompt' => 'Sekolahmu kewalahan sampah. Pilih kebijakan tiap putaran.',
                    'body' => 'Jaga kebersihan, partisipasi siswa, dan anggaran OSIS.',
                    'payload' => [
                        'rounds' => [
                            ['ideal_choice' => 'Pisah organik & anorganik', 'weight' => 34],
                            ['ideal_choice' => 'Kampanye 3R ke kelas', 'weight' => 33],
                            ['ideal_choice' => 'Bank sampah mingguan', 'weight' => 33],
                        ],
                        'thresholds' => ['stability' => 70, 'trust' => 65, 'budget' => 40],
                        'bonus_points' => 10,
                        'max_points' => 100,
                    ],
                    'max_points' => 100,
                ]],
            ],

            // ── SMA / SMK ──
            [
                'slug' => 'jenjang-sma-recall-linear',
                'title' => 'Recall Quiz — Persamaan Linear',
                'subject' => 'Matematika',
                'grade_level' => 'SMA/SMK 10–11',
                'jenjang' => ArenaJenjang::SMA,
                'mechanic_type' => 'recall_quiz',
                'summary' => '[SMA/SMK] Drill konsep persamaan linear satu variabel.',
                'duration_minutes' => 12,
                'steps' => [[
                    'module_key' => 'recall_quiz',
                    'position' => 1,
                    'title' => 'Kuis Persamaan Linear',
                    'prompt' => 'Jawab pertanyaan berikut.',
                    'body' => null,
                    'payload' => [
                        'questions' => [
                            [
                                'question' => 'Bentuk umum persamaan linear satu variabel adalah...',
                                'options' => ['ax + b = 0', 'ax² + bx + c = 0', 'y = mx + c saja', 'a/x = b'],
                                'answer' => 'ax + b = 0',
                            ],
                            [
                                'question' => 'Penyelesaian dari 2x + 6 = 0 adalah...',
                                'options' => ['x = −3', 'x = 3', 'x = −6', 'x = 2'],
                                'answer' => 'x = −3',
                            ],
                            [
                                'question' => 'Grafik persamaan linear digambarkan sebagai...',
                                'options' => ['Garis lurus', 'Parabola', 'Lingkaran', 'Hiperbola'],
                                'answer' => 'Garis lurus',
                            ],
                        ],
                    ],
                    'max_points' => 100,
                ]],
            ],
            [
                'slug' => 'jenjang-sma-narasi-etika-digital',
                'title' => 'Narasi — Etika Digital di Dunia Kerja',
                'subject' => 'Informatika',
                'grade_level' => 'SMA/SMK 10–11',
                'jenjang' => ArenaJenjang::SMA,
                'mechanic_type' => 'interactive_narrative',
                'summary' => '[SMA/SMK] Pilih alur keputusan etika digital di tempat magang/kerja.',
                'duration_minutes' => 15,
                'steps' => [[
                    'module_key' => 'interactive_narrative',
                    'position' => 1,
                    'title' => 'Hari Pertama Magang',
                    'prompt' => 'Kamu menemukan data pelanggan di laptop bersama. Apa langkah terbaik?',
                    'body' => 'Pilih 3 langkah berurutan yang paling etis dan aman.',
                    'payload' => [
                        'start_node' => 'masuk',
                        'expected_path' => ['Laporkan ke mentor', 'Kunci layar bersama', 'Ikuti SOP privasi'],
                        'accepted_end_nodes' => ['finish', 'sop'],
                        'max_points' => 100,
                    ],
                    'max_points' => 100,
                ]],
            ],
            [
                'slug' => 'jenjang-sma-keputusan-modal-usaha',
                'title' => 'Keputusan — Modal Usaha Siswa',
                'subject' => 'PKWU',
                'grade_level' => 'SMA/SMK 11–12',
                'jenjang' => ArenaJenjang::SMA,
                'mechanic_type' => 'strategic_decision',
                'summary' => '[SMA/SMK] Simulasi alokasi modal usaha kecil & manajemen risiko.',
                'duration_minutes' => 14,
                'steps' => [[
                    'module_key' => 'strategic_decision',
                    'position' => 1,
                    'title' => 'Rapat Tim Usaha',
                    'prompt' => 'Modal terbatas. Putuskan alokasi tiap putaran.',
                    'body' => 'Jaga stok, kepercayaan pelanggan, dan sisa kas.',
                    'payload' => [
                        'rounds' => [
                            ['ideal_choice' => 'Beli bahan inti dulu', 'weight' => 34],
                            ['ideal_choice' => 'Uji produk ke 10 pembeli', 'weight' => 33],
                            ['ideal_choice' => 'Sisihkan dana darurat', 'weight' => 33],
                        ],
                        'thresholds' => ['stability' => 72, 'trust' => 68, 'budget' => 35],
                        'bonus_points' => 10,
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
