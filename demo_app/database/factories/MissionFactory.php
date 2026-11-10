<?php

namespace Database\Factories;

use App\Models\Mission;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Mission> */
class MissionFactory extends Factory
{
    protected $model = Mission::class;

    public function definition(): array
    {
        $title = fake()->sentence(3);

        return [
            'slug' => Str::slug($title) . '-' . fake()->unique()->numberBetween(100, 999),
            'title' => $title,
            'subject' => fake()->randomElement(['IPA', 'Matematika', 'Bahasa Indonesia', 'IPS']),
            'grade_level' => fake()->randomElement(['SD 4', 'SD 5', 'SD 6']),
            'mechanic_type' => fake()->randomElement(['interactive_narrative', 'strategic_decision', 'puzzle_sequencing']),
            'summary' => fake()->sentence(12),
            'duration_minutes' => fake()->numberBetween(15, 35),
            'max_score' => 100,
            'is_published' => true,
            'meta' => [],
        ];
    }

    public function nalar(): static
    {
        return $this->state(fn () => [
            'slug' => 'jejak-pagi-di-hutan',
            'subject' => 'IPA',
            'grade_level' => 'SD 5',
            'mechanic_type' => 'nalar_bundle',
            'title' => 'Jejak Pagi di Hutan',
            'summary' => 'Mekanik nalar gabungan untuk narasi, keputusan strategis, dan sequencing.',
            'duration_minutes' => 30,
            'max_score' => 100,
            'requires_reflection' => false,
            'meta' => [
                'bundle' => ['interactive_narrative', 'strategic_decision', 'puzzle_sequencing'],
                'concept_key' => 'ekosistem',
                'concept_label' => 'Rantai Makanan',
            ],
        ]);
    }

    public function recallQuiz(): static
    {
        return $this->state(fn () => [
            'slug' => 'ekspedisi-rantai-makanan',
            'subject' => 'IPA',
            'grade_level' => 'SD 5',
            'mechanic_type' => 'recall_quiz_bundle',
            'title' => 'Ekspedisi Rantai Makanan',
            'summary' => 'Recall quiz dan menjodohkan konsep ekosistem.',
            'duration_minutes' => 20,
            'max_score' => 100,
            'requires_reflection' => true,
            'status' => 'published',
            'is_published' => true,
            'meta' => [
                'concept_key' => 'ekosistem',
                'concept_label' => 'Rantai Makanan',
            ],
        ]);
    }
}
