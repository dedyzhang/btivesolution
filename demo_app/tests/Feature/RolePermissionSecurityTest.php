<?php

namespace Tests\Feature;

use App\Models\RolePermission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Keamanan matriks hak akses (SettingController::rolesSave): whitelist
 * role/permission mencegah role/permission sembarang tersimpan ke tabel
 * role_permissions (mis. self-escalation ke permission uang/manage_keuangan).
 */
class RolePermissionSecurityTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::create(['username' => 'rbac_admin', 'password' => Hash::make('x'), 'access' => 'superadmin']);
    }

    public function test_role_tidak_valid_diabaikan(): void
    {
        $this->actingAs($this->admin())->post('/settings/roles', [
            'perms' => [
                'superadmin'      => ['manage_keuangan' => 1], // role di luar whitelist
                'role_sembarang'  => ['manage_keuangan' => 1],
            ],
        ])->assertRedirect();

        $this->assertDatabaseMissing('role_permissions', ['role' => 'superadmin']);
        $this->assertDatabaseMissing('role_permissions', ['role' => 'role_sembarang']);
    }

    public function test_permission_tidak_valid_diabaikan(): void
    {
        $this->actingAs($this->admin())->post('/settings/roles', [
            'perms' => [
                'kepala' => ['permission_ngarang' => 1, 'manage_keuangan' => 1],
            ],
        ])->assertRedirect();

        $this->assertDatabaseMissing('role_permissions', ['permission' => 'permission_ngarang']);
        $this->assertDatabaseHas('role_permissions', ['role' => 'kepala', 'permission' => 'manage_keuangan']);
    }

    public function test_entri_bukan_array_tidak_membuat_error(): void
    {
        // Simulasi payload rusak: perms[kepala] = string, bukan array.
        $this->actingAs($this->admin())->post('/settings/roles', [
            'perms' => ['kepala' => 'bukan-array'],
        ])->assertRedirect(); // tidak boleh 500

        $this->assertDatabaseMissing('role_permissions', ['role' => 'kepala']);
    }

    public function test_role_dan_permission_valid_tersimpan_normal(): void
    {
        $this->actingAs($this->admin())->post('/settings/roles', [
            'perms' => [
                'bendahara' => ['manage_keuangan' => 1],
                'guru'      => ['manage_jadwal' => 1],
            ],
        ])->assertRedirect();

        $this->assertDatabaseHas('role_permissions', ['role' => 'bendahara', 'permission' => 'manage_keuangan']);
        $this->assertDatabaseHas('role_permissions', ['role' => 'guru', 'permission' => 'manage_jadwal']);
        $this->assertSame(2, RolePermission::count());
    }

    public function test_perms_kosong_tidak_error(): void
    {
        $this->actingAs($this->admin())->post('/settings/roles', [])
            ->assertRedirect();

        $this->assertSame(0, RolePermission::count());
    }
}
