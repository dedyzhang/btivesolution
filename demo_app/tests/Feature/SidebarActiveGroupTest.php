<?php

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\RolePermission;
use App\Models\Setting;
use App\Models\User;
use App\Models\Walikelas;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Regresi: user yang PUNYA DUA peran sekaligus (mis. kesiswaan + wali kelas) melihat route
 * yang sama (poin.siswa.index) muncul di 2 grup sidebar berbeda ("Poin & Aturan" milik
 * kesiswaan, "Wali Kelas" miliknya sendiri). Sebelum fix, membuka halaman itu dari dalam
 * menu Wali Kelas malah "meloncat" membuka grup Poin & Aturan, karena kode lama cuma
 * mengambil grup PERTAMA yang cocok (lihat layouts/app.blade.php $activeGroup lama).
 */
class SidebarActiveGroupTest extends TestCase
{
    use RefreshDatabase;

    public function test_route_yang_muncul_di_dua_grup_sidebar_terdaftar_di_keduanya(): void
    {
        // "Poin & Aturan" (bukan "P3 Kedisiplinan") pakai route poin.siswa.index — yang sama
        // dgn item "Poin Siswa Kelas" di grup Wali Kelas. Default setting-nya 'p3', jadi harus
        // dipaksa ke 'poin' dulu supaya kedua grup benar-benar tabrakan pakai route yang sama.
        Setting::set('jenis_aturan', 'poin');

        $kelas = Kelas::create(['tingkat' => 7, 'kelas' => 'F']);

        $user = User::create([
            'username' => 'kesiswaan_walikelas',
            'password' => Hash::make('password'),
            'access'   => 'kesiswaan',
        ]);
        $guru = Guru::create([
            'id_login'        => $user->getKey(),
            'nama'            => 'Guru Kesiswaan Wali',
            'nik'             => 'GKW001',
            'face_descriptor' => [array_map(fn ($i) => $i % 2 === 0 ? 1.0 : -1.0, range(0, 63))],
        ]);
        Walikelas::create(['id_kelas' => $kelas->uuid, 'id_guru' => $guru->uuid]);
        RolePermission::create(['role' => 'kesiswaan', 'permission' => 'manage_disiplin']);

        $html = $this->actingAs($user)->get(route('poin.siswa.index'))->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/const matches = (\[[^\]]*\]);/', $html);
        preg_match('/const matches = (\[[^\]]*\]);/', $html, $m);
        $matches = json_decode($m[1], true);

        $this->assertContains('disiplin', $matches, 'Grup Poin & Aturan (kesiswaan) harus tetap terdaftar sbg grup aktif.');
        $this->assertContains('walikelas', $matches, 'Grup Wali Kelas harus tetap terdaftar sbg grup aktif, bukan tergeser oleh grup disiplin.');
    }
}
