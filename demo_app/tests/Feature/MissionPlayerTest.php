<?php

namespace Tests\Feature;

use App\Models\Classroom;
use App\Models\ClassroomMember;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Mission;
use App\Models\MissionAssignment;
use App\Models\MissionReflectionPrompt;
use App\Models\MissionStep;
use App\Models\Ngajar;
use App\Models\Pelajaran;
use App\Models\Semester;
use App\Models\Setting;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MissionPlayerTest extends TestCase
{
    use RefreshDatabase;

    public function test_siswa_can_complete_recall_quiz_mission(): void
    {
        [$mission, $user, $assignment] = $this->createPlayerFixture();

        $response = $this->actingAs($user)->postJson(route('jagat-misi.api.player.attempts', $mission), [
            'assignment_id' => $assignment->uuid,
            'duration_seconds' => 90,
            'responses' => [
                'recall_quiz' => [
                    'answers' => ['Produsen', 'Katak', 'Mengurai sisa makhluk hidup'],
                ],
                'matching' => [
                    'matches' => [
                        'Produsen' => 'Padi',
                        'Konsumen' => 'Belalang',
                        'Pengurai' => 'Jamur',
                    ],
                ],
            ],
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.status', 'awaiting_reflection');
        $this->assertGreaterThanOrEqual(95, $response->json('data.score'));

        $this->assertDatabaseCount('mission_attempts', 1);
    }

    private function createPlayerFixture(): array
    {
        Setting::create(['key' => 'nama_sekolah', 'value' => 'Test School']);

        $guruUser = User::create([
            'username' => 'guru_player',
            'password' => Hash::make('password'),
            'access' => 'guru',
        ]);
        $guru = Guru::create([
            'id_login' => $guruUser->uuid,
            'nama' => 'Guru Player',
            'nik' => '3201',
            'jk' => 'L',
            'face_descriptor' => [0.1],
        ]);

        $user = User::create([
            'username' => 'siswa_player',
            'password' => Hash::make('password'),
            'access' => 'siswa',
        ]);

        $semester = Semester::create(['semester' => 1, 'tahun' => '2025/2026', 'aktif' => true]);
        $kelas = Kelas::create(['tingkat' => 5, 'kelas' => 'A']);
        $pelajaran = Pelajaran::create(['nama' => 'IPA', 'ringkasan' => 'IPA', 'kkm' => 75]);
        Ngajar::create([
            'id_guru' => $guru->uuid,
            'id_kelas' => $kelas->uuid,
            'id_pelajaran' => $pelajaran->uuid,
        ]);

        Siswa::create([
            'id_login' => $user->uuid,
            'id_kelas' => $kelas->uuid,
            'nama' => 'Siswa Player',
            'nis' => '9003',
            'jk' => 'L',
            'face_descriptor' => [0.1, 0.2],
        ]);

        $classroom = Classroom::create([
            'id_semester' => $semester->id,
            'id_kelas' => $kelas->uuid,
            'id_pelajaran' => $pelajaran->uuid,
            'title' => 'IPA 5A Player',
            'status' => 'published',
            'class_code' => 'PLY001',
            'created_by' => $guruUser->uuid,
            'cover_color' => '#111',
        ]);
        ClassroomMember::create([
            'classroom_id' => $classroom->uuid,
            'user_id' => $user->uuid,
            'role_in_class' => 'siswa',
            'joined_at' => now(),
        ]);

        $mission = Mission::factory()->recallQuiz()->create();
        MissionStep::factory()->recallQuiz()->create(['mission_id' => $mission->uuid]);
        MissionStep::factory()->matching()->create(['mission_id' => $mission->uuid]);
        MissionReflectionPrompt::create([
            'mission_id' => $mission->uuid,
            'position' => 1,
            'prompt_text' => 'Apa yang kamu pahami?',
            'is_required' => true,
        ]);

        $assignment = MissionAssignment::create([
            'mission_id' => $mission->uuid,
            'classroom_id' => $classroom->uuid,
            'assigned_by' => $guruUser->uuid,
            'status' => 'open',
        ]);

        return [$mission, $user, $assignment];
    }
}
