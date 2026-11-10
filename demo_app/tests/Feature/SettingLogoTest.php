<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SettingLogoTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::create([
            'username' => 'test_admin',
            'password' => Hash::make('password'),
            'access'   => 'superadmin',
        ]);
    }

    public function test_admin_can_upload_and_delete_sekolah_logo()
    {
        Storage::fake('public');
        $admin = $this->admin();

        // 1. Initial check (no logo)
        $this->assertNull(Setting::get('sekolah_logo'));

        // 2. Upload logo
        $logo = UploadedFile::fake()->image('school_logo.png', 100, 100);

        $response = $this->actingAs($admin)->post(route('setting.identitas'), [
            'nama_sekolah' => 'SMP Maitreyawira',
            'sekolah_logo' => $logo,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Identitas sekolah disimpan.');

        $savedLogo = Setting::get('sekolah_logo');
        $this->assertNotNull($savedLogo);
        $this->assertStringContainsString('logo/sekolah_logo_', $savedLogo);

        Storage::disk('public')->assertExists($savedLogo);

        // 3. Delete logo
        $response = $this->actingAs($admin)->post(route('setting.identitas'), [
            'nama_sekolah' => 'SMP Maitreyawira',
            'hapus_logo'   => '1',
        ]);

        $response->assertRedirect();
        $this->assertEquals('', Setting::get('sekolah_logo'));
        Storage::disk('public')->assertMissing($savedLogo);
    }
}
