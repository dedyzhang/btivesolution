<?php

namespace Database\Seeders;

use App\Models\Classroom;
use App\Models\Mission;
use App\Models\MissionAssignment;
use App\Models\MissionStep;
use Illuminate\Database\Seeder;

/**
 * Masukkan SEMUA permainan JagatMISI ke Arena Belajar:
 * - pastikan katalog misi lengkap (bundle + mekanik tunggal)
 * - terbitkan & tugaskan semua misi published ke classroom target
 *
 * Jalankan: php artisan db:seed --class=SyncJagatMisiToArenaSeeder
 */
class SyncJagatMisiToArenaSeeder extends Seeder
{
    public function run(): void
    {
        $classroom = Classroom::where('class_code', '2N3-ICS0')->first()
            ?? Classroom::where('status', 'published')->first();

        if (! $classroom) {
            $this->command?->warn('Tidak ada classroom published — skip SyncJagatMisiToArenaSeeder.');

            return;
        }

        // Demo bundle utama (nalar + recall)
        $this->call(JagatMisiDemoSeeder::class);

        // Mekanik tunggal JagatMISI agar semua tipe permainan tampil di Arena
        $this->seedStandaloneCatalog();

        // Katalog permainan edukatif bertanda jenjang SD / SMP / SMA-SMK
        $this->call(JenjangEduGameSeeder::class);

        // Katalog tren 2025–2026 (AI, media, iklim, green computing, wellbeing)
        $this->call(TrenEduGameSeeder::class);

        // Rapikan duplikat lama factory (draft / slug tanpa demo)
        Mission::query()
            ->where('slug', 'jejak-pagi-di-hutan')
            ->where('title', 'Jejak Pagi di Hutan')
            ->update(['is_published' => true, 'status' => 'published', 'visible_to_teachers' => true]);

        $missions = Mission::query()
            ->where('is_published', true)
            ->where(function ($q) {
                $q->where('status', 'published')->orWhereNull('status');
            })
            ->get();

        $assigned = 0;
        foreach ($missions as $mission) {
            // Pastikan status published konsisten
            if ($mission->status !== 'published') {
                $mission->update(['status' => 'published']);
            }

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
            $assigned++;
        }

        $this->command?->newLine();
        $this->command?->info('═══ Semua permainan JagatMISI masuk Arena Belajar ═══');
        $this->command?->info('Kelas: '.$classroom->title.' ('.$classroom->class_code.')');
        $this->command?->info('Misi ditugaskan: '.$assigned);
        foreach ($missions->sortBy('title') as $m) {
            $this->command?->line(' • '.$m->title.' ['.$m->mechanic_type.']');
        }
        $this->command?->warn('Siswa: Ruang Kelas → Arena Belajar → tab Misi');
        $this->command?->info('Hub: /ruang-kelas/'.$classroom->class_code.'/arena-belajar?mode=misi');
    }

    private function seedStandaloneCatalog(): void
    {
        // 1) Narasi interaktif saja
        $narasi = Mission::updateOrCreate(
            ['slug' => 'arena-narasi-surat-hutan'],
            [
                'title' => 'Narasi — Surat dari Hutan',
                'subject' => 'Bahasa Indonesia',
                'grade_level' => 'SD 5',
                'mechanic_type' => 'interactive_narrative',
                'summary' => 'Permainan narasi interaktif: pilih alur cerita yang paling logis.',
                'duration_minutes' => 12,
                'max_score' => 100,
                'is_published' => true,
                'status' => 'published',
                'visible_to_teachers' => true,
                'requires_reflection' => false,
                'meta' => ['demo' => true, 'kind' => 'standalone'],
            ]
        );
        $this->replaceSteps($narasi, [[
            'module_key' => 'interactive_narrative',
            'position' => 1,
            'title' => 'Surat Misterius',
            'prompt' => 'Kamu menemukan surat basah di tepi sungai. Apa yang kamu lakukan?',
            'body' => 'Pilih 3 langkah berurutan yang paling masuk akal.',
            'payload' => [
                'start_node' => 'pagi',
                'expected_path' => ['Periksa sumber air', 'Bersihkan aliran', 'Selesaikan laporan'],
                'accepted_end_nodes' => ['finish', 'lapor'],
                'max_points' => 100,
            ],
            'max_points' => 100,
        ]]);

        // 2) Keputusan strategis saja
        $keputusan = Mission::updateOrCreate(
            ['slug' => 'arena-keputusan-desa-banjir'],
            [
                'title' => 'Keputusan — Desa Rawan Banjir',
                'subject' => 'IPS',
                'grade_level' => 'SD 5',
                'mechanic_type' => 'strategic_decision',
                'summary' => 'Permainan keputusan strategis: seimbangkan stabilitas, kepercayaan, dan anggaran.',
                'duration_minutes' => 12,
                'max_score' => 100,
                'is_published' => true,
                'status' => 'published',
                'visible_to_teachers' => true,
                'requires_reflection' => false,
                'meta' => ['demo' => true, 'kind' => 'standalone'],
            ]
        );
        $this->replaceSteps($keputusan, [[
            'module_key' => 'strategic_decision',
            'position' => 1,
            'title' => 'Rapat Desa',
            'prompt' => 'Desa rawan banjir saat musim hujan. Pilih kebijakan terbaik tiap putaran.',
            'body' => 'Perhatikan indikator stabilitas, kepercayaan, dan anggaran.',
            'payload' => [
                'rounds' => [
                    ['ideal_choice' => 'Bersihkan drainase', 'weight' => 34],
                    ['ideal_choice' => 'Buka forum warga', 'weight' => 33],
                    ['ideal_choice' => 'Buka jalur tambahan', 'weight' => 33],
                ],
                'thresholds' => ['stability' => 75, 'trust' => 65, 'budget' => 35],
                'bonus_points' => 10,
                'max_points' => 100,
            ],
            'max_points' => 100,
        ]]);

        // 3) Puzzle sequencing saja
        $puzzle = Mission::updateOrCreate(
            ['slug' => 'arena-puzzle-jembatan'],
            [
                'title' => 'Puzzle — Urutan Bangun Jembatan',
                'subject' => 'Matematika',
                'grade_level' => 'SD 5',
                'mechanic_type' => 'puzzle_sequencing',
                'summary' => 'Permainan puzzle sequencing: urutkan langkah secara logis.',
                'duration_minutes' => 10,
                'max_score' => 100,
                'is_published' => true,
                'status' => 'published',
                'visible_to_teachers' => true,
                'requires_reflection' => false,
                'meta' => ['demo' => true, 'kind' => 'standalone'],
            ]
        );
        $this->replaceSteps($puzzle, [[
            'module_key' => 'puzzle_sequencing',
            'position' => 1,
            'title' => 'Susun Langkah',
            'prompt' => 'Urutkan langkah pembangunan jembatan dari awal sampai uji coba.',
            'body' => 'Geser kartu sampai urutannya benar.',
            'payload' => [
                'correct_order' => ['survey', 'materials', 'foundation', 'bridge', 'test'],
                'max_points' => 100,
            ],
            'max_points' => 100,
        ]]);

        // 4) Recall quiz saja (pakai player engine)
        $recall = Mission::updateOrCreate(
            ['slug' => 'arena-recall-fotosintesis'],
            [
                'title' => 'Recall Quiz — Fotosintesis',
                'subject' => 'IPA',
                'grade_level' => 'SD 5',
                'mechanic_type' => 'recall_quiz',
                'summary' => 'Permainan recall quiz: jawab soal pilihan ganda singkat.',
                'duration_minutes' => 10,
                'max_score' => 100,
                'is_published' => true,
                'status' => 'published',
                'visible_to_teachers' => true,
                'requires_reflection' => false,
                'meta' => ['demo' => true, 'kind' => 'standalone'],
            ]
        );
        $this->replaceSteps($recall, [[
            'module_key' => 'recall_quiz',
            'position' => 1,
            'title' => 'Kuis Fotosintesis',
            'prompt' => 'Jawab 3 pertanyaan berikut.',
            'body' => null,
            'payload' => [
                'questions' => [
                    [
                        'question' => 'Proses tumbuhan membuat makanan dengan bantuan cahaya disebut...',
                        'options' => ['Fotosintesis', 'Respirasi', 'Transpirasi', 'Fermentasi'],
                        'answer' => 'Fotosintesis',
                    ],
                    [
                        'question' => 'Gas yang dihasilkan fotosintesis dan dibutuhkan makhluk hidup adalah...',
                        'options' => ['Oksigen', 'Karbon dioksida', 'Nitrogen', 'Uap air'],
                        'answer' => 'Oksigen',
                    ],
                    [
                        'question' => 'Bagian tumbuhan tempat fotosintesis utama terjadi adalah...',
                        'options' => ['Daun', 'Akar', 'Batang', 'Bunga'],
                        'answer' => 'Daun',
                    ],
                ],
            ],
            'max_points' => 100,
        ]]);

        // 5) Matching saja (pakai player engine — mechanic harus mengandung quiz/recall)
        $match = Mission::updateOrCreate(
            ['slug' => 'arena-matching-operasi'],
            [
                'title' => 'Menjodohkan — Operasi Matematika',
                'subject' => 'Matematika',
                'grade_level' => 'SD 5',
                'mechanic_type' => 'quiz_matching',
                'summary' => 'Permainan menjodohkan: pasangkan operasi dengan hasilnya.',
                'duration_minutes' => 8,
                'max_score' => 100,
                'is_published' => true,
                'status' => 'published',
                'visible_to_teachers' => true,
                'requires_reflection' => false,
                'meta' => ['demo' => true, 'kind' => 'standalone'],
            ]
        );
        $this->replaceSteps($match, [[
            'module_key' => 'matching',
            'position' => 1,
            'title' => 'Pasangkan Hasil',
            'prompt' => 'Pasangkan operasi dengan hasil yang benar.',
            'body' => null,
            'payload' => [
                'pairs' => [
                    ['term' => '12 × 3', 'answer' => '36'],
                    ['term' => '45 − 18', 'answer' => '27'],
                    ['term' => '56 ÷ 7', 'answer' => '8'],
                ],
                'options' => ['36', '27', '8', '15', '42'],
            ],
            'max_points' => 100,
        ]]);
    }

    /** @param list<array<string,mixed>> $steps */
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
