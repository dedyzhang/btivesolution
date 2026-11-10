<?php

namespace Database\Seeders;

use App\Models\Mission;
use App\Models\MissionBadge;
use App\Models\MissionReflectionPrompt;
use App\Models\MissionStep;
use Illuminate\Database\Seeder;

class JagatMisiSeeder extends Seeder
{
    public function run(): void
    {
        MissionBadge::factory()->firstMission()->create();
        MissionBadge::factory()->streakThree()->create();

        $nalar = Mission::factory()->nalar()->create(['is_published' => true, 'status' => 'published']);
        MissionStep::factory()->narrative()->create(['mission_id' => $nalar->uuid]);
        MissionStep::factory()->decision()->create(['mission_id' => $nalar->uuid]);
        MissionStep::factory()->puzzle()->create(['mission_id' => $nalar->uuid]);

        $player = Mission::factory()->recallQuiz()->create();
        MissionStep::factory()->recallQuiz()->create(['mission_id' => $player->uuid]);
        MissionStep::factory()->matching()->create(['mission_id' => $player->uuid]);
        MissionReflectionPrompt::create([
            'mission_id' => $player->uuid,
            'position' => 1,
            'prompt_text' => 'Apa yang paling kamu pahami dari misi ini?',
            'is_required' => true,
        ]);
        MissionReflectionPrompt::create([
            'mission_id' => $player->uuid,
            'position' => 2,
            'prompt_text' => 'Apa kendala terbesar yang kamu hadapi?',
            'is_required' => false,
        ]);
    }
}
