<?php

namespace Database\Seeders;

use App\Models\Classroom;
use App\Models\Mission;
use App\Models\MissionAssignment;
use App\Models\MissionReflectionPrompt;
use App\Models\MissionStep;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Misi demo Jagat Misi + penugasan ke Ruang Kelas untuk uji coba manual.
 * Jalankan: php artisan db:seed --class=JagatMisiDemoSeeder
 */
class JagatMisiDemoSeeder extends Seeder
{
    use \Database\Seeders\Concerns\ResolvesDemoClassroom;

    public function run(): void
    {
        $classroom = $this->resolveDemoClassroom();
        if (! $classroom) {
            $this->command?->warn('Tidak ada classroom published — skip JagatMisiDemoSeeder.');

            return;
        }

        $nalar = Mission::updateOrCreate(
            ['slug' => 'demo-jejak-pagi-di-hutan'],
            [
                'title' => '[DEMO] Jejak Pagi di Hutan',
                'subject' => 'IPA',
                'grade_level' => 'SD 5',
                'mechanic_type' => 'nalar_bundle',
                'summary' => 'Misi demo narasi + keputusan + puzzle tentang ekosistem hutan.',
                'duration_minutes' => 20,
                'max_score' => 100,
                'is_published' => true,
                'status' => 'published',
                'visible_to_teachers' => true,
                'requires_reflection' => false,
                'meta' => [
                    'bundle' => ['interactive_narrative', 'strategic_decision', 'puzzle_sequencing'],
                    'concept_key' => 'ekosistem',
                    'concept_label' => 'Rantai Makanan',
                    'demo' => true,
                ],
            ]
        );

        $this->syncNalarSteps($nalar);

        $quiz = Mission::updateOrCreate(
            ['slug' => 'demo-ekspedisi-rantai-makanan'],
            [
                'title' => '[DEMO] Ekspedisi Rantai Makanan',
                'subject' => 'IPA',
                'grade_level' => 'SD 5',
                'mechanic_type' => 'recall_quiz_bundle',
                'summary' => 'Misi demo recall quiz + menjodohkan — 3 soal pilihan ganda + 3 pasangan.',
                'duration_minutes' => 15,
                'max_score' => 100,
                'is_published' => true,
                'status' => 'published',
                'visible_to_teachers' => true,
                'requires_reflection' => false,
                'meta' => [
                    'concept_key' => 'ekosistem',
                    'concept_label' => 'Rantai Makanan',
                    'demo' => true,
                ],
            ]
        );

        $this->syncQuizSteps($quiz);

        foreach ([$nalar, $quiz] as $mission) {
            MissionAssignment::firstOrCreate(
                ['mission_id' => $mission->uuid, 'classroom_id' => $classroom->uuid],
                [
                    'assigned_by' => $classroom->created_by,
                    'status' => 'open',
                    'opens_at' => now()->subHour(),
                    'due_at' => now()->addDays(14),
                ]
            );
        }

        $base = '/ruang-kelas/'.$classroom->class_code.'/arena-belajar?mode=misi';
        $this->command?->newLine();
        $this->command?->info('═══ Misi DEMO Arena Belajar siap dicoba ═══');
        $this->command?->info('Kelas: '.$classroom->title.' (kode: '.$classroom->class_code.')');
        $this->command?->info('Hub Arena (tab Misi): '.$base);
        $this->command?->info('Misi nalar: '.$nalar->title);
        $this->command?->info('Misi quiz: '.$quiz->title);
        $this->command?->newLine();
        $this->command?->line('<fg=cyan>── Kunci jawaban Recall Quiz ──</>');
        $this->command?->line('1. Produsen');
        $this->command?->line('2. Katak');
        $this->command?->line('3. Mengurai sisa makhluk hidup');
        $this->command?->line('<fg=cyan>── Kunci Menjodohkan ──</>');
        $this->command?->line('Produsen → Padi | Konsumen → Belalang | Pengurai → Jamur');
        $this->command?->newLine();
        $this->command?->line('<fg=cyan>── Kunci Misi Nalar (payload ideal) ──</>');
        $this->command?->line('Narasi: Periksa sumber air → Bersihkan aliran → Selesaikan laporan');
        $this->command?->line('Keputusan: Bersihkan drainase, Buka forum warga, Buka jalur tambahan');
        $this->command?->line('Puzzle: survey → materials → foundation → bridge → test');
        $this->command?->newLine();
        $this->command?->warn('Login siswa di SIMS, buka Ruang Kelas → tab Arena Belajar → Mulai misi.');
    }

    private function syncNalarSteps(Mission $mission): void
    {
        $mission->steps()->delete();

        MissionStep::create([
            'mission_id' => $mission->uuid,
            'module_key' => 'interactive_narrative',
            'position' => 1,
            'title' => 'Narasi: Jejak Air di Hutan',
            'prompt' => 'Kamu menemukan aliran sungai kecil yang keruh. Apa langkah pertamamu?',
            'body' => 'Pilih 3 langkah berurutan yang paling logis.',
            'payload' => [
                'start_node' => 'pagi',
                'expected_path' => ['Periksa sumber air', 'Bersihkan aliran', 'Selesaikan laporan'],
                'accepted_end_nodes' => ['finish', 'lapor'],
                'max_points' => 40,
            ],
            'max_points' => 40,
        ]);

        MissionStep::create([
            'mission_id' => $mission->uuid,
            'module_key' => 'strategic_decision',
            'position' => 2,
            'title' => 'Keputusan: Cegah Banjir',
            'prompt' => 'Desa dekat hutan rawan banjir saat hujan deras.',
            'body' => 'Pilih kebijakan terbaik di setiap putaran.',
            'payload' => [
                'rounds' => [
                    ['ideal_choice' => 'Bersihkan drainase', 'weight' => 14],
                    ['ideal_choice' => 'Buka forum warga', 'weight' => 13],
                    ['ideal_choice' => 'Buka jalur tambahan', 'weight' => 13],
                ],
                'thresholds' => ['stability' => 75, 'trust' => 65, 'budget' => 35],
                'bonus_points' => 10,
                'max_points' => 40,
            ],
            'max_points' => 40,
        ]);

        MissionStep::create([
            'mission_id' => $mission->uuid,
            'module_key' => 'puzzle_sequencing',
            'position' => 3,
            'title' => 'Puzzle: Urutkan Pembangunan Jembatan',
            'prompt' => 'Susun langkah pembangunan jembatan penyeberangan sungai.',
            'body' => 'Drag atau urutkan kartu dari awal hingga uji coba.',
            'payload' => [
                'correct_order' => ['survey', 'materials', 'foundation', 'bridge', 'test'],
                'max_points' => 20,
            ],
            'max_points' => 20,
        ]);
    }

    private function syncQuizSteps(Mission $mission): void
    {
        $mission->steps()->delete();
        MissionReflectionPrompt::where('mission_id', $mission->uuid)->delete();

        MissionStep::create([
            'mission_id' => $mission->uuid,
            'module_key' => 'recall_quiz',
            'position' => 1,
            'title' => 'Recall Quiz — Rantai Makanan',
            'prompt' => 'Jawab 3 pertanyaan pilihan ganda berikut.',
            'body' => 'Baca dengan teliti sebelum memilih.',
            'payload' => [
                'questions' => [
                    [
                        'question' => 'Makhluk yang membuat makanan sendiri melalui fotosintesis disebut...',
                        'options' => ['Produsen', 'Konsumen', 'Pengurai', 'Herbivora'],
                        'answer' => 'Produsen',
                    ],
                    [
                        'question' => 'Jika populasi belalang turun drastis, hewan yang paling cepat terdampak adalah...',
                        'options' => ['Katak', 'Pohon', 'Batu', 'Matahari'],
                        'answer' => 'Katak',
                    ],
                    [
                        'question' => 'Fungsi pengurai dalam ekosistem adalah...',
                        'options' => [
                            'Mengurai sisa makhluk hidup',
                            'Memburu hewan lain',
                            'Menghasilkan oksigen',
                            'Menyimpan air tanah',
                        ],
                        'answer' => 'Mengurai sisa makhluk hidup',
                    ],
                ],
            ],
            'max_points' => 60,
        ]);

        MissionStep::create([
            'mission_id' => $mission->uuid,
            'module_key' => 'matching',
            'position' => 2,
            'title' => 'Menjodohkan — Peran dalam Ekosistem',
            'prompt' => 'Pasangkan peran dengan contohnya.',
            'body' => null,
            'payload' => [
                'pairs' => [
                    ['term' => 'Produsen', 'answer' => 'Padi'],
                    ['term' => 'Konsumen', 'answer' => 'Belalang'],
                    ['term' => 'Pengurai', 'answer' => 'Jamur'],
                ],
                'options' => ['Padi', 'Belalang', 'Jamur', 'Batu', 'Air'],
            ],
            'max_points' => 40,
        ]);
    }
}
