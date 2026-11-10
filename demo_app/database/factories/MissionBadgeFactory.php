<?php

namespace Database\Factories;

use App\Models\MissionBadge;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<MissionBadge> */
class MissionBadgeFactory extends Factory
{
    protected $model = MissionBadge::class;

    public function definition(): array
    {
        return [
            'code' => fake()->unique()->slug(),
            'name' => fake()->words(2, true),
            'description' => fake()->sentence(),
            'icon' => fake()->randomElement(['star', 'flame', 'leaf', 'spark']),
            'threshold_xp' => fake()->numberBetween(0, 300),
            'threshold_streak' => null,
            'threshold_missions' => null,
            'is_active' => true,
            'meta' => [],
        ];
    }

    public function firstMission(): static
    {
        return $this->state(fn () => [
            'code' => 'first-mission',
            'name' => 'Langkah Pertama',
            'description' => 'Selesaikan misi pertama untuk membuka koleksi awal.',
            'icon' => 'flag',
            'threshold_missions' => 1,
            'threshold_xp' => 50,
            'meta' => ['kind' => 'mission'],
        ]);
    }

    public function streakThree(): static
    {
        return $this->state(fn () => [
            'code' => 'streak-three',
            'name' => 'Api 3 Hari',
            'description' => 'Belajar tiga hari beruntun.',
            'icon' => 'flame',
            'threshold_streak' => 3,
            'threshold_xp' => 0,
            'meta' => ['kind' => 'streak'],
        ]);
    }
}
