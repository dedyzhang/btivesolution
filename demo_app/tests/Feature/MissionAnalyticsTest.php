<?php

namespace Tests\Feature;

use App\Models\Mission;
use App\Models\MissionAttempt;
use App\Models\MissionConceptMastery;
use App\Models\MissionStep;
use App\Models\Setting;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MissionAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_guru_can_view_analytics_matrix(): void
    {
        [$student, $guru] = $this->createFixtures();

        MissionAttempt::create([
            'mission_id' => Mission::factory()->recallQuiz()->create()->uuid,
            'user_id' => $student->uuid,
            'status' => 'completed',
            'completed_at' => now(),
            'score' => 85,
            'duration_seconds' => 600,
            'result_meta' => [],
        ]);

        $response = $this->actingAs($guru)->get(route('jagat-misi.analytics'));
        $response->assertOk();

        $json = $this->actingAs($guru)->getJson(route('jagat-misi.api.analytics'));
        $json->assertOk();
    }

    public function test_analytics_syncs_concept_mastery(): void
    {
        [$student, $guru] = $this->createFixtures();
        $mission = Mission::factory()->recallQuiz()->create();
        MissionStep::factory()->recallQuiz()->create(['mission_id' => $mission->uuid]);

        MissionAttempt::create([
            'mission_id' => $mission->uuid,
            'user_id' => $student->uuid,
            'status' => 'completed',
            'completed_at' => now(),
            'score' => 92,
            'duration_seconds' => 600,
            'result_meta' => [],
        ]);

        $this->actingAs($guru)->getJson(route('jagat-misi.api.analytics'));

        $this->assertGreaterThan(0, MissionConceptMastery::where('user_id', $student->uuid)->count());
    }

    private function createFixtures(): array
    {
        Setting::create(['key' => 'nama_sekolah', 'value' => 'Test School']);

        $student = User::create([
            'username' => 'siswa_analytics',
            'password' => Hash::make('password'),
            'access' => 'siswa',
        ]);
        Siswa::create([
            'id_login' => $student->uuid,
            'nama' => 'Siswa Analytics',
            'nis' => '9005',
            'jk' => 'L',
            'face_descriptor' => [0.1, 0.2],
        ]);

        $guru = User::create([
            'username' => 'guru_analytics',
            'password' => Hash::make('password'),
            'access' => 'guru',
        ]);

        return [$student, $guru];
    }
}
