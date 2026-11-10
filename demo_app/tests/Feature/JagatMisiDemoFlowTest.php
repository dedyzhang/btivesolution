<?php

namespace Tests\Feature;

use App\Models\Classroom;
use App\Models\ClassroomMember;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Mission;
use App\Models\MissionAssignment;
use App\Models\MissionAttempt;
use App\Models\Ngajar;
use App\Models\Pelajaran;
use App\Models\Semester;
use App\Models\Setting;
use App\Models\Siswa;
use App\Models\User;
use Database\Seeders\JagatMisiDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class JagatMisiDemoFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_missions_can_be_played_and_scored_end_to_end(): void
    {
        Setting::create(['key' => 'nama_sekolah', 'value' => 'Test School']);

        $guruUser = User::create([
            'username' => 'guru_demo_jm',
            'password' => Hash::make('password'),
            'access' => 'guru',
        ]);
        $guru = Guru::create([
            'id_login' => $guruUser->uuid,
            'nama' => 'Guru Demo',
            'nik' => '4001',
            'jk' => 'L',
            'face_descriptor' => [0.1],
        ]);

        $semester = Semester::create(['semester' => 1, 'tahun' => '2025/2026', 'aktif' => true]);
        $kelas = Kelas::create(['tingkat' => 7, 'kelas' => 'A']);
        $pelajaran = Pelajaran::create(['nama' => 'Matematika', 'ringkasan' => 'MTK', 'kkm' => 75]);
        Ngajar::create([
            'id_guru' => $guru->uuid,
            'id_kelas' => $kelas->uuid,
            'id_pelajaran' => $pelajaran->uuid,
        ]);

        $classroom = Classroom::create([
            'id_semester' => $semester->id,
            'id_kelas' => $kelas->uuid,
            'id_pelajaran' => $pelajaran->uuid,
            'title' => 'Matematika 7A Demo',
            'status' => 'published',
            'class_code' => 'DEMO-JM',
            'created_by' => $guruUser->uuid,
            'cover_color' => '#111',
        ]);

        $siswaUser = User::create([
            'username' => 'siswa_demo_jm',
            'password' => Hash::make('password'),
            'access' => 'siswa',
        ]);
        Siswa::create([
            'id_login' => $siswaUser->uuid,
            'id_kelas' => $kelas->uuid,
            'nama' => 'Siswa Demo JM',
            'nis' => '7001',
            'jk' => 'L',
            'face_descriptor' => [0.1],
        ]);
        ClassroomMember::create([
            'classroom_id' => $classroom->uuid,
            'user_id' => $siswaUser->uuid,
            'role_in_class' => 'siswa',
            'joined_at' => now(),
        ]);

        $this->seed(JagatMisiDemoSeeder::class);

        $quiz = Mission::where('slug', 'demo-ekspedisi-rantai-makanan')->firstOrFail();
        $assignment = MissionAssignment::where('mission_id', $quiz->uuid)
            ->where('classroom_id', $classroom->uuid)
            ->first();

        $this->assertNotNull($assignment, 'Demo seeder harus menugaskan misi ke classroom published pertama.');

        // Re-assign ke classroom fixture kita (seeder pakai classroom pertama di DB kosong = fixture kita)
        $this->actingAs($siswaUser)
            ->get(route('classroom.arena.index', $classroom))
            ->assertOk()
            ->assertSee('[DEMO] Ekspedisi Rantai Makanan');

        $play = $this->actingAs($siswaUser)
            ->get(route('classroom.jagat.play', [$classroom, $quiz]));
        $play->assertOk();

        $submit = $this->actingAs($siswaUser)->postJson(route('jagat-misi.api.player.attempts', $quiz), [
            'assignment_id' => $assignment->uuid,
            'duration_seconds' => 120,
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

        $submit->assertCreated();
        $this->assertGreaterThanOrEqual(95, $submit->json('data.score'));

        $attempt = MissionAttempt::query()->first();
        $this->assertSame($assignment->uuid, $attempt->assignment_id);
        $this->assertSame('completed', $attempt->status);

        $this->actingAs($guruUser)
            ->get(route('classroom.jagat.results', [$classroom, $quiz]))
            ->assertOk()
            ->assertSee('100%')
            ->assertSee('Siswa Demo JM');
    }
}
