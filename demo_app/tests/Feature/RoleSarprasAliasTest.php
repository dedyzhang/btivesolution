<?php

namespace Tests\Feature;

use App\Http\Controllers\GuruController;
use App\Models\ForumRolePermission;
use App\Models\User;
use App\Support\Forum;
use App\Support\TickerStats;
use App\Support\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * users.access dikanonikalisasi jadi 'sarpras' saat disimpan (User model), tetapi
 * banyak tempat masih membandingkan/meng-key dengan alias lama 'sapras' — akibatnya
 * staf sarpras kehilangan izin forum, blok dashboard, dan notifikasi. Test ini
 * mengunci invariannya: apa pun ejaan yang dipakai pemanggil, hasilnya sama.
 */
class RoleSarprasAliasTest extends TestCase
{
    use RefreshDatabase;

    public function test_access_sapras_disimpan_kanonik_sebagai_sarpras(): void
    {
        $user = User::create([
            'username' => 'waka_sarpras',
            'password' => Hash::make('password'),
            'access'   => 'sapras',
        ]);

        $this->assertSame('sarpras', $user->fresh()->access);
    }

    public function test_lingkup_kategori_forum_sama_untuk_kedua_ejaan(): void
    {
        $harapan = ['sarpras', 'umum', 'pengumuman'];

        $this->assertSame($harapan, Forum::categoryScope('sarpras'));
        $this->assertSame($harapan, Forum::categoryScope('sapras'));
    }

    public function test_matriks_izin_forum_mengenali_baris_beralias_lama(): void
    {
        // Baris lama tersimpan dengan ejaan 'sapras'; user nyata ber-access 'sarpras'.
        ForumRolePermission::create([
            'access'     => 'sapras',
            'permission' => 'forum.topic.create',
            'allowed'    => true,
        ]);
        ForumRolePermission::clearCache();

        $this->assertTrue(ForumRolePermission::granted('sarpras', 'forum.topic.create'));
        $this->assertTrue(ForumRolePermission::granted('sapras', 'forum.topic.create'));
    }

    public function test_konfigurasi_kanonik_menang_atas_baris_lama(): void
    {
        ForumRolePermission::create(['access' => 'sapras', 'permission' => 'forum.moderate', 'allowed' => true]);
        ForumRolePermission::create(['access' => 'sarpras', 'permission' => 'forum.moderate', 'allowed' => false]);
        ForumRolePermission::clearCache();

        // Deterministik: ejaan kanonik yang menentukan, bukan urutan baris di DB.
        $this->assertFalse(ForumRolePermission::granted('sarpras', 'forum.moderate'));
    }

    public function test_user_sarpras_punya_izin_forum(): void
    {
        $user = User::create([
            'username' => 'waka_sarpras_forum',
            'password' => Hash::make('password'),
            'access'   => 'sapras', // tersimpan jadi 'sarpras'
        ]);

        ForumRolePermission::create([
            'access'     => 'sarpras',
            'permission' => 'forum.comment.create',
            'allowed'    => true,
        ]);
        ForumRolePermission::clearCache();

        $this->assertTrue($user->fresh()->canForum('forum.comment.create'));
    }

    public function test_ticker_menandai_sarpras_sebagai_manajemen(): void
    {
        foreach (['sarpras', 'sapras'] as $ejaan) {
            $flags = TickerStats::flags($ejaan);
            $this->assertTrue($flags['management'], "management gagal untuk '{$ejaan}'");
            $this->assertTrue($flags['teacher'], "teacher gagal untuk '{$ejaan}'");
        }
    }

    public function test_label_peran_guru_memakai_kunci_kanonik(): void
    {
        // Ditampilkan lewat ROLES[$user->access]; access selalu 'sarpras'.
        $this->assertArrayHasKey('sarpras', GuruController::ROLES);
        $this->assertSame('Sarana & Prasarana', GuruController::ROLES['sarpras']);
    }

    public function test_helper_userrole_menyamakan_kedua_ejaan(): void
    {
        $this->assertTrue(UserRole::matches('sarpras', 'sapras'));
        $this->assertTrue(UserRole::matches('sapras', 'sarpras'));
        $this->assertFalse(UserRole::matches('sarpras', 'kurikulum'));
    }
}
