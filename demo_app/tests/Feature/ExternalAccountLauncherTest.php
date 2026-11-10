<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use App\Services\GeminiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Mockery\MockInterface;
use Tests\TestCase;

class ExternalAccountLauncherTest extends TestCase
{
    use RefreshDatabase;

    private function guru(array $extra = []): User
    {
        return User::create(array_merge([
            'username' => 'guru_launcher_'.uniqid(),
            'password' => Hash::make('password'),
            'access' => 'guru',
        ], $extra));
    }

    private function admin(): User
    {
        return User::create([
            'username' => 'admin_launcher',
            'password' => Hash::make('password'),
            'access' => 'admin',
        ]);
    }

    public function test_admin_can_save_school_integration_defaults(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post(route('setting.integrasi'), [
                'tp_launcher_aktif' => '1',
            ])
            ->assertRedirect();

        $this->assertSame('1', Setting::get('tp_launcher_aktif'));
    }

    public function test_first_visit_requires_api_key_before_using_asisten_guru(): void
    {
        Setting::set('tp_launcher_aktif', '1');

        $guru = $this->guru();

        $html = $this->actingAs($guru)
            ->get(route('ai.teacher.index'))
            ->assertOk()
            ->assertSee('Hubungkan API key Gemini', false)
            ->assertSee('aistudio.google.com/apikey', false)
            ->assertDontSee('Lengkapi email Anda', false)
            ->getContent();

        $this->assertMatchesRegularExpression('/needsApiKeySetup:\\s*true/', $html);

        $this->actingAs($guru)
            ->postJson(route('ai.teacher.quiz'), [
                'topik' => 'Fotosintesis',
                'jumlah' => 1,
                'jenis_soal' => ['pg'],
                'tingkat' => 'sedang',
            ])
            ->assertStatus(422)
            ->assertJsonPath('needs_api_key', true);
    }

    public function test_guru_can_save_api_key_then_use_asisten_guru(): void
    {
        $guru = $this->guru();
        $plain = 'AIzaSyTestPersonalKeyABCDEFG123456';

        $this->mock(GeminiService::class, function (MockInterface $mock) use ($plain) {
            $mock->shouldReceive('probeApiKey')->once()->with($plain)->andReturnNull();
            $mock->shouldIgnoreMissing();
        });

        $this->actingAs($guru)
            ->putJson(route('ai.teacher.gemini-key'), [
                'gemini_api_key' => $plain,
            ])
            ->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('accounts.has_gemini_api_key', true);

        $html = $this->actingAs($guru)
            ->get(route('ai.teacher.index'))
            ->assertOk()
            ->getContent();

        $this->assertMatchesRegularExpression('/needsApiKeySetup:\\s*false/', $html);
        $this->assertStringContainsString('3456', $html);
    }

    public function test_asisten_guru_shows_launcher_when_aktif(): void
    {
        Setting::set('tp_launcher_aktif', '1');

        $guru = $this->guru();
        $guru->setGeminiApiKey('AIzaSyTestPersonalKeyForFeatureTests01');

        $this->actingAs($guru)
            ->get(route('ai.teacher.index'))
            ->assertOk()
            ->assertSee('Nalar Guru', false)
            ->assertSee('Buka Nalar Guru', false)
            ->assertSee('Generate di SIMS memakai API key akun Google Anda', false)
            ->assertDontSee(route('ai.teacher.presentasi-from-chat'), false);
    }

    public function test_asisten_guru_hides_launcher_when_nonaktif(): void
    {
        Setting::set('tp_launcher_aktif', '0');

        $guru = $this->guru();
        $guru->setGeminiApiKey('AIzaSyTestPersonalKeyForFeatureTests01');

        $this->actingAs($guru)
            ->get(route('ai.teacher.index'))
            ->assertOk()
            ->assertDontSee('Buka Nalar Guru', false);
    }
}
