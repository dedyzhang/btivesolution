<?php

namespace Database\Factories;

use App\Models\Mission;
use App\Models\MissionStep;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<MissionStep> */
class MissionStepFactory extends Factory
{
    protected $model = MissionStep::class;

    public function definition(): array
    {
        return [
            'mission_id' => Mission::factory(),
            'module_key' => 'interactive_narrative',
            'position' => 1,
            'title' => 'Narasi Interaktif',
            'prompt' => 'Pilih langkah awal yang paling masuk akal.',
            'body' => 'Misi narasi dimulai dari petunjuk yang paling kuat.',
            'payload' => [],
            'max_points' => 40,
        ];
    }

    public function narrative(): static
    {
        return $this->state(fn () => [
            'module_key' => 'interactive_narrative',
            'position' => 1,
            'title' => 'Narasi Interaktif',
            'prompt' => 'Seorang penjaga taman menemukan jejak air yang hilang.',
            'body' => 'Pilih alur tindakan yang paling logis dari bukti yang ada.',
            'payload' => [
                'start_node' => 'pagi',
                'expected_path' => ['Periksa sumber air', 'Bersihkan aliran', 'Selesaikan laporan'],
                'accepted_end_nodes' => ['finish', 'lapor'],
                'max_points' => 40,
            ],
            'max_points' => 40,
        ]);
    }

    public function decision(): static
    {
        return $this->state(fn () => [
            'module_key' => 'strategic_decision',
            'position' => 2,
            'title' => 'Keputusan Strategis',
            'prompt' => 'Kota kecil menghadapi risiko banjir.',
            'body' => 'Pilih kebijakan yang menyeimbangkan stabilitas, kepercayaan, dan biaya.',
            'payload' => [
                'rounds' => [
                    ['ideal_choice' => 'Bersihkan drainase', 'weight' => 14],
                    ['ideal_choice' => 'Buka forum warga', 'weight' => 13],
                    ['ideal_choice' => 'Buka jalur tambahan', 'weight' => 13],
                ],
                'thresholds' => [
                    'stability' => 75,
                    'trust' => 65,
                    'budget' => 35,
                ],
                'bonus_points' => 10,
                'max_points' => 40,
            ],
            'max_points' => 40,
        ]);
    }

    public function puzzle(): static
    {
        return $this->state(fn () => [
            'module_key' => 'puzzle_sequencing',
            'position' => 3,
            'title' => 'Puzzle Sequencing',
            'prompt' => 'Urutkan pembangunan jembatan dengan logika yang benar.',
            'body' => 'Kartu dapat diurutkan ulang sampai alurnya cocok.',
            'payload' => [
                'correct_order' => ['survey', 'materials', 'foundation', 'bridge', 'test'],
                'max_points' => 20,
            ],
            'max_points' => 20,
        ]);
    }

    public function recallQuiz(): static
    {
        return $this->state(fn () => [
            'module_key' => 'recall_quiz',
            'position' => 1,
            'title' => 'Recall Quiz',
            'prompt' => 'Jawab pertanyaan tentang rantai makanan.',
            'body' => null,
            'payload' => [
                'questions' => [
                    ['question' => 'Makhluk hidup yang bisa membuat makanan sendiri disebut apa?', 'options' => ['Produsen', 'Konsumen', 'Pengurai', 'Predator'], 'answer' => 'Produsen'],
                    ['question' => 'Jika jumlah belalang turun drastis, siapa yang paling cepat terdampak?', 'options' => ['Katak', 'Matahari', 'Tanah', 'Batu'], 'answer' => 'Katak'],
                    ['question' => 'Pengurai membantu ekosistem dengan cara...', 'options' => ['Mengurai sisa makhluk hidup', 'Memburu semua hewan', 'Menghilangkan produsen', 'Menghentikan fotosintesis'], 'answer' => 'Mengurai sisa makhluk hidup'],
                ],
            ],
            'max_points' => 60,
        ]);
    }

    public function matching(): static
    {
        return $this->state(fn () => [
            'module_key' => 'matching',
            'position' => 2,
            'title' => 'Menjodohkan',
            'prompt' => 'Pasangkan konsep dengan contoh yang benar.',
            'body' => null,
            'payload' => [
                'pairs' => [
                    ['term' => 'Produsen', 'answer' => 'Padi'],
                    ['term' => 'Konsumen', 'answer' => 'Belalang'],
                    ['term' => 'Pengurai', 'answer' => 'Jamur'],
                ],
                'options' => ['Padi', 'Belalang', 'Jamur', 'Batu'],
            ],
            'max_points' => 40,
        ]);
    }
}
