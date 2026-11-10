<?php

namespace Tests\Feature;

use App\Models\Mission;
use App\Models\MissionAttempt;
use App\Models\MissionReflectionPrompt;
use App\Models\MissionStep;
use App\Models\Setting;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MissionDebriefTest extends TestCase
{
    use RefreshDatabase;

    public function test_reflection_completes_attempt_with_gate(): void
    {
        [$attempt, $user] = $this->createAwaitingAttempt();

        $response = $this->actingAs($user)->postJson(route('jagat-misi.api.debrief', $attempt), [
            'understand' => 'Saya paham produsen dan konsumen.',
            'barrier' => 'Menjodohkan masih agak sulit.',
            'next_step' => 'Latihan lagi.',
            'mood' => 'siap',
            'confirmed' => true,
        ]);

        $response->assertOk();
        $this->assertSame('completed', $attempt->fresh()->status);
        $this->assertDatabaseHas('mission_reflections', [
            'mission_attempt_id' => $attempt->uuid,
            'confirmed' => true,
        ]);
    }

    public function test_guru_can_view_teacher_panel(): void
    {
        $guru = User::create([
            'username' => 'guru_jm',
            'password' => Hash::make('password'),
            'access' => 'guru',
        ]);

        $response = $this->actingAs($guru)->get(route('jagat-misi.debrief.teacher'));
        $response->assertOk();
    }

    private function createAwaitingAttempt(): array
    {
        Setting::create(['key' => 'nama_sekolah', 'value' => 'Test School']);

        $user = User::create([
            'username' => 'siswa_debrief',
            'password' => Hash::make('password'),
            'access' => 'siswa',
        ]);
        Siswa::create([
            'id_login' => $user->uuid,
            'nama' => 'Siswa Debrief',
            'nis' => '9004',
            'jk' => 'P',
            'face_descriptor' => [0.1, 0.2],
        ]);

        $mission = Mission::factory()->recallQuiz()->create();
        MissionStep::factory()->recallQuiz()->create(['mission_id' => $mission->uuid]);
        MissionReflectionPrompt::create([
            'mission_id' => $mission->uuid,
            'position' => 1,
            'prompt_text' => 'Refleksi wajib',
            'is_required' => true,
        ]);

        $attempt = MissionAttempt::create([
            'mission_id' => $mission->uuid,
            'user_id' => $user->uuid,
            'status' => 'awaiting_reflection',
            'score' => 88,
            'duration_seconds' => 100,
            'result_meta' => [],
        ]);

        return [$attempt, $user];
    }
}
