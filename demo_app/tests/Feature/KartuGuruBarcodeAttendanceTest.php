<?php

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\PresensiGuru;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Kartu ID Guru bisa dipakai utk absensi via kamera QR yang sama dgn kartu siswa
 * (AbsensiController::markByBarcode → delegasi ke PresensiGuruController::mark()).
 * Payload kartu = NIP, fallback NIK, fallback UUID.
 */
class KartuGuruBarcodeAttendanceTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Guru $guru;

    protected function setUp(): void
    {
        parent::setUp();
        Setting::create(['key' => 'nama_sekolah', 'value' => 'Test']);
        Setting::create(['key' => 'cara_absensi_guru', 'value' => 'wajah']);

        $this->admin = User::create([
            'username' => 'admin_kartu_guru_barcode',
            'password' => Hash::make('x'),
            'access' => 'superadmin',
        ]);

        $guruUser = User::create([
            'username' => 'guru_barcode', 'password' => Hash::make('x'), 'access' => 'guru',
        ]);
        $this->guru = Guru::create([
            'id_login' => $guruUser->uuid,
            'nama' => 'Guru Barcode Test',
            'nik' => '5501122',
            'nip' => '198501012010011002',
            'jk' => 'L',
            // tanpa wajah terdaftar, middleware paksa-daftar-wajah me-redirect sebelum ke halaman apa pun
            'face_descriptor' => [0.1],
        ]);
    }

    public function test_scan_kartu_nip_menandai_guru_hadir_masuk(): void
    {
        $this->actingAs($this->admin)
            ->postJson(route('absensi.markBarcode'), [
                'barcode' => '198501012010011002',
                'tanggal' => now()->toDateString(),
                'mode' => 'masuk',
            ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('type', 'guru')
            ->assertJsonPath('uuid', $this->guru->uuid)
            ->assertJsonPath('mode', 'masuk');

        $this->assertDatabaseHas('presensi_gurus', [
            'id_guru' => $this->guru->uuid,
            'status' => 'hadir',
            'keterangan' => 'Kartu ID (barcode)',
        ]);
    }

    public function test_scan_kartu_nik_juga_dikenali_saat_nip_tidak_dipakai(): void
    {
        $this->actingAs($this->admin)
            ->postJson(route('absensi.markBarcode'), [
                'barcode' => '5501122',
                'tanggal' => now()->toDateString(),
            ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('type', 'guru');
    }

    public function test_scan_kartu_uuid_dikenali(): void
    {
        $this->actingAs($this->admin)
            ->postJson(route('absensi.markBarcode'), [
                'barcode' => $this->guru->uuid,
                'tanggal' => now()->toDateString(),
            ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('type', 'guru')
            ->assertJsonPath('nama', 'Guru Barcode Test');
    }

    public function test_scan_kartu_pulang_mengisi_jam_pulang_terpisah_dari_masuk(): void
    {
        $payload = ['barcode' => $this->guru->nip, 'tanggal' => now()->toDateString()];

        $this->actingAs($this->admin)->postJson(route('absensi.markBarcode'), $payload + ['mode' => 'masuk'])
            ->assertOk()->assertJsonPath('success', true);

        $this->actingAs($this->admin)->postJson(route('absensi.markBarcode'), $payload + ['mode' => 'pulang'])
            ->assertOk()->assertJsonPath('success', true)->assertJsonPath('mode', 'pulang');

        $row = PresensiGuru::where('id_guru', $this->guru->uuid)->first();
        $this->assertNotNull($row->jam_masuk);
        $this->assertNotNull($row->jam_pulang);
    }

    public function test_scan_kartu_kedua_kali_mode_yang_sama_ditolak_duplikat(): void
    {
        $payload = ['barcode' => $this->guru->nip, 'tanggal' => now()->toDateString(), 'mode' => 'masuk'];

        $this->actingAs($this->admin)->postJson(route('absensi.markBarcode'), $payload)
            ->assertOk()->assertJsonPath('success', true);

        $this->actingAs($this->admin)->postJson(route('absensi.markBarcode'), $payload)
            ->assertOk()
            ->assertJsonPath('success', false)
            ->assertJsonPath('duplicate', true)
            ->assertJsonPath('type', 'guru');
    }

    public function test_kartu_guru_ditolak_saat_kamera_wajah_saja_dan_metode_sekolah_barcode(): void
    {
        // Barcode via SAH kalau metode wajah sekolah aktif ATAU kamera kiosk membaca QR (pola sama
        // dgn siswa) — jadi utk benar2 memblokir perlu KEDUA syarat itu gagal sekaligus: metode
        // sekolah dikunci ke barcode/QR (bukan wajah) DAN kamera kiosk disetel wajah-saja.
        Setting::set('cara_absensi_guru', 'barcode');
        Setting::set('scan_kiosk_mode', 'wajah');

        $this->actingAs($this->admin)
            ->postJson(route('absensi.markBarcode'), [
                'barcode' => $this->guru->nip,
                'tanggal' => now()->toDateString(),
            ])
            ->assertOk()
            ->assertJsonPath('success', false)
            ->assertJsonPath('blocked', true);
    }

    public function test_kartu_guru_tetap_boleh_saat_kamera_keduanya_walau_metode_wajah(): void
    {
        Setting::set('scan_kiosk_mode', 'keduanya');

        $this->actingAs($this->admin)
            ->postJson(route('absensi.markBarcode'), [
                'barcode' => $this->guru->nip,
                'tanggal' => now()->toDateString(),
            ])
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    public function test_kartu_id_saya_menampilkan_kartu_guru_sendiri(): void
    {
        $guruUser = User::where('username', 'guru_barcode')->first();

        $this->actingAs($guruUser)->get(route('kartu-guru.self'))
            ->assertOk()
            ->assertSee('Guru Barcode Test', false)
            ->assertSee('Perbesar', false);
    }

    public function test_kartu_id_saya_ditolak_utk_akun_bukan_guru(): void
    {
        $this->actingAs($this->admin)->get(route('kartu-guru.self'))->assertForbidden();
    }
}
