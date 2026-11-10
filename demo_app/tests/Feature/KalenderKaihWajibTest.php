<?php

namespace Tests\Feature;

use App\Models\HariEfektif;
use App\Models\KaihJawaban;
use App\Models\Kelas;
use App\Models\Setting;
use App\Models\Siswa;
use App\Models\User;
use App\Support\KaihSiswa;
use App\Support\KalenderAbsensi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class KalenderKaihWajibTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::firstOrCreate(
            ['username' => 'kalender_kaih_admin'],
            [
                'password' => Hash::make('password'),
                'access' => 'superadmin',
            ]
        );
    }

    private function siswa(): Siswa
    {
        $kelas = Kelas::create(['tingkat' => 7, 'kelas' => 'A']);
        $user = User::create([
            'username' => 'siswa_kalender_kaih',
            'password' => Hash::make('password'),
            'access' => 'siswa',
        ]);

        return Siswa::create([
            'id_login' => $user->uuid,
            'nama' => 'Siswa KAIH Kalender',
            'nis' => 'KAIH-CAL-001',
            'id_kelas' => $kelas->uuid,
            'jk' => 'L',
        ]);
    }

    public function test_mode_menyimpan_penegakan_kaih_kalender(): void
    {
        $this->actingAs($this->admin())
            ->post(route('kalender.mode'), [
                'kalender_absen_aktif' => '1',
                'kalender_kaih_aktif' => '1',
            ])
            ->assertRedirect();

        $this->assertSame('1', Setting::get('kalender_absen_aktif'));
        $this->assertSame('0', Setting::get('kalender_agenda_aktif', '0'));
        $this->assertSame('1', Setting::get('kalender_kaih_aktif'));
    }

    public function test_toggle_dan_bulk_kaih_wajib(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->postJson(route('kalender.toggle'), [
            'tanggal' => '2026-07-20',
            'field' => 'kaih_wajib',
            'value' => true,
        ])->assertOk()->assertJson(['ok' => true]);

        $this->assertDatabaseHas('hari_efektif', [
            'tanggal' => '2026-07-20',
            'kaih_wajib' => 1,
        ]);

        $this->actingAs($admin)->post(route('kalender.bulk'), [
            'bulan' => '2026-07',
            'field' => 'kaih_wajib',
            'value' => 0,
        ])->assertRedirect();

        $this->assertDatabaseHas('hari_efektif', [
            'tanggal' => '2026-07-20',
            'kaih_wajib' => 0,
        ]);
        // Hari kerja Juli 2026: 23 (1–31 minus weekends)
        $this->assertSame(23, HariEfektif::where('kaih_wajib', false)->whereMonth('tanggal', 7)->whereYear('tanggal', 2026)->count());
    }

    public function test_kaih_tanpa_batas_kalender_wajib_setiap_hari_jika_fitur_global_on(): void
    {
        Setting::set('kaih_wajib_sebelum_absen', '1');
        Setting::set('kalender_kaih_aktif', '0');
        KalenderAbsensi::lupakanCache();

        $siswa = $this->siswa();
        $this->assertTrue(KaihSiswa::wajibPadaTanggal('2026-07-20'));
        $this->assertFalse(KaihSiswa::bolehAbsen($siswa->uuid, '2026-07-20'));
    }

    public function test_kaih_dibatasi_kalender_hanya_hari_bertanda(): void
    {
        Setting::set('kaih_wajib_sebelum_absen', '1');
        Setting::set('kalender_kaih_aktif', '1');
        KalenderAbsensi::lupakanCache();

        $siswa = $this->siswa();
        HariEfektif::create([
            'tanggal' => '2026-07-20',
            'kaih_wajib' => true,
            'semester' => 1,
        ]);
        KalenderAbsensi::lupakanCache();

        $this->assertTrue(KaihSiswa::wajibPadaTanggal('2026-07-20'));
        $this->assertFalse(KaihSiswa::bolehAbsen($siswa->uuid, '2026-07-20'));

        $this->assertFalse(KaihSiswa::wajibPadaTanggal('2026-07-21'));
        $this->assertTrue(KaihSiswa::bolehAbsen($siswa->uuid, '2026-07-21'));

        KaihJawaban::create([
            'id_siswa' => $siswa->uuid,
            'tanggal' => '2026-07-20',
            'status' => 'dilewati',
            'total_skor' => 0,
        ]);
        $this->assertTrue(KaihSiswa::bolehAbsen($siswa->uuid, '2026-07-20'));
    }

    public function test_fitur_global_kaih_off_mengalahkan_kalender(): void
    {
        Setting::set('kaih_wajib_sebelum_absen', '0');
        Setting::set('kalender_kaih_aktif', '1');
        HariEfektif::create([
            'tanggal' => '2026-07-20',
            'kaih_wajib' => true,
            'semester' => 1,
        ]);
        KalenderAbsensi::lupakanCache();

        $siswa = $this->siswa();
        $this->assertFalse(KaihSiswa::wajibPadaTanggal('2026-07-20'));
        $this->assertTrue(KaihSiswa::bolehAbsen($siswa->uuid, '2026-07-20'));
    }

    public function test_halaman_kalender_menampilkan_kontrol_kaih(): void
    {
        $this->actingAs($this->admin())
            ->get(route('kalender.index', ['bulan' => '2026-07']))
            ->assertOk()
            ->assertSee('Batasi Wajib 7 KAIH')
            ->assertSee('7 KAIH ON')
            ->assertSee('kaih_wajib');
    }
}
