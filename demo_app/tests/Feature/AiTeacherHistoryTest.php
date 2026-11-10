<?php

namespace Tests\Feature;

use App\Http\Controllers\AiTeacherController;
use App\Models\AiTeacherHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use ReflectionMethod;
use Tests\TestCase;

/**
 * Regresi bug SQLSTATE 22001 "Data too long for column 'excerpt'": storeHistory()
 * memakai Str::limit(..., 500/180) yang MENAMBAH '...' (3 char) sehingga hasilnya
 * bisa 503/183 dan melampaui lebar kolom (excerpt VARCHAR(500), title VARCHAR(180)).
 * MySQL strict menolaknya; SQLite dev tidak menegakkan panjang VARCHAR sehingga bug
 * lolos di lokal. Test ini menegakkan invariant panjang secara langsung (tak
 * bergantung penegakan DB) agar bug tertangkap di lokal juga.
 */
class AiTeacherHistoryTest extends TestCase
{
    use RefreshDatabase;

    private function invokeStoreHistory(string $userId, array $data, string $answer): array
    {
        $controller = app(AiTeacherController::class);
        $method = new ReflectionMethod($controller, 'storeHistory');
        $method->setAccessible(true);

        return $method->invoke($controller, $userId, $data, $answer);
    }

    public function test_excerpt_dan_title_panjang_tidak_melebihi_lebar_kolom(): void
    {
        $user = User::create([
            'username' => 'ai_hist_guru',
            'password' => Hash::make('password'),
            'access'   => 'guru',
            'gemini_account' => 'guru@belajar.id',
        ]);

        // Jawaban & judul jauh lebih panjang dari kolom untuk memaksa pemangkasan.
        $longAnswer = str_repeat('Contoh ilustrasi materi pembelajaran. ', 100); // ~3.700 char
        $longTitle = str_repeat('Judul Panjang ', 50); // ~700 char

        $result = $this->invokeStoreHistory($user->uuid, [
            'type'       => 'summary',
            'type_label' => 'Rangkuman Materi',
            'title'      => $longTitle,
            'metadata'   => ['panjang_materi' => 6],
        ], $longAnswer);

        // Nilai yang dikembalikan controller harus muat di kolom.
        $this->assertLessThanOrEqual(500, mb_strlen($result['excerpt']));
        $this->assertLessThanOrEqual(180, mb_strlen($result['title']));

        // Dan yang benar-benar tersimpan di DB juga.
        $row = AiTeacherHistory::firstWhere('user_uuid', $user->uuid);
        $this->assertNotNull($row);
        $this->assertLessThanOrEqual(500, mb_strlen((string) $row->excerpt));
        $this->assertLessThanOrEqual(180, mb_strlen((string) $row->title));

        // Jawaban penuh tetap tersimpan utuh (kolom mediumText, tak dipangkas).
        $this->assertSame($longAnswer, $row->answer);
    }

    public function test_excerpt_pendek_tidak_ditambah_elipsis_berlebih(): void
    {
        $user = User::create([
            'username' => 'ai_hist_guru2',
            'password' => Hash::make('password'),
            'access'   => 'guru',
            'gemini_account' => 'guru@belajar.id',
        ]);

        $result = $this->invokeStoreHistory($user->uuid, [
            'type'       => 'summary',
            'type_label' => 'Rangkuman Materi',
            'title'      => 'Fotosintesis',
            'metadata'   => [],
        ], 'Ringkasan singkat materi.');

        // Konten pendek: tetap utuh, tak ada pemangkasan/elipsis.
        $this->assertSame('Fotosintesis', $result['title']);
        $this->assertSame('Ringkasan singkat materi.', $result['excerpt']);
    }
}
