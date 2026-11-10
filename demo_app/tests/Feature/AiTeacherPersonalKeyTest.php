<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\GeminiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Mockery\MockInterface;
use Tests\TestCase;

class AiTeacherPersonalKeyTest extends TestCase
{
    use RefreshDatabase;

    private function guru(array $extra = []): User
    {
        return User::create(array_merge([
            'username' => 'guru_key_'.uniqid(),
            'password' => Hash::make('password'),
            'access' => 'guru',
            'gemini_account' => 'guru@belajar.id',
        ], $extra));
    }

    public function test_generate_requires_api_key(): void
    {
        $guru = $this->guru();

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

    public function test_save_api_key_masks_and_encrypts(): void
    {
        $guru = $this->guru();
        $plain = 'AIzaSyTestPersonalKeyABCDEFG123456';

        $this->mock(GeminiService::class, function (MockInterface $mock) use ($plain) {
            $mock->shouldReceive('probeApiKey')
                ->once()
                ->with($plain)
                ->andReturnNull();
            $mock->shouldIgnoreMissing();
        });

        $this->actingAs($guru)
            ->putJson(route('ai.teacher.gemini-key'), [
                'gemini_api_key' => $plain,
            ])
            ->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('accounts.has_gemini_api_key', true)
            ->assertJsonPath('accounts.gemini_api_key_masked', '••••3456')
            ->assertJsonMissingPath('accounts.gemini_api_key');

        $guru->refresh();
        $this->assertTrue($guru->hasGeminiApiKey());
        $this->assertSame($plain, $guru->plainGeminiApiKey());
        $this->assertNotSame($plain, $guru->gemini_api_key);
        $this->assertSame($plain, Crypt::decryptString($guru->gemini_api_key));
    }

    public function test_page_shows_api_key_setup_copy_without_leaking_key(): void
    {
        $guru = $this->guru();
        $plain = 'AIzaSyTestPersonalKeyABCDEFG123456';
        $guru->setGeminiApiKey($plain);

        $html = $this->actingAs($guru)->get(route('ai.teacher.index'))->assertOk()->getContent();
        $this->assertStringNotContainsString($plain, $html);
        $this->assertStringContainsString('aistudio.google.com/apikey', $html);
        $this->assertStringContainsString('Generate di SIMS memakai API key akun Google Anda', $html);
        $this->assertStringContainsString('3456', $html);
        $this->assertMatchesRegularExpression('/has_gemini_api_key:\\s*true/', $html);
    }

    public function test_invalid_api_key_rejected_on_save(): void
    {
        $guru = $this->guru();

        $this->mock(GeminiService::class, function (MockInterface $mock) {
            $mock->shouldReceive('probeApiKey')
                ->once()
                ->andThrow(new \RuntimeException('API key tidak valid atau belum aktif di Google AI Studio.'));
        });

        $this->actingAs($guru)
            ->putJson(route('ai.teacher.gemini-key'), [
                'gemini_api_key' => 'AIzaSyInvalidKeyThatIsLongEnough',
            ])
            ->assertStatus(422)
            ->assertJsonPath('ok', false);

        $this->assertFalse($guru->fresh()->hasGeminiApiKey());
    }

    public function test_quiz_generate_uses_personal_api_key(): void
    {
        $guru = $this->guru();
        $plain = 'AIzaSyTeacherPersonalKeyXYZ1234567';
        $guru->setGeminiApiKey($plain);

        $this->mock(GeminiService::class, function (MockInterface $mock) use ($plain) {
            $mock->shouldReceive('generate')
                ->once()
                ->withArgs(function (string $prompt, array $options) use ($plain) {
                    return ($options['api_key'] ?? null) === $plain
                        && str_contains($prompt, 'Fotosintesis');
                })
                ->andReturn([
                    'text' => '1. Soal?\nA. Ya\n\nKunci Jawaban\n1. A',
                    'model' => 'gemini-test',
                    'prompt_tokens' => 10,
                    'completion_tokens' => 20,
                ]);
            $mock->shouldIgnoreMissing();
        });

        $this->actingAs($guru)
            ->postJson(route('ai.teacher.quiz'), [
                'topik' => 'Fotosintesis',
                'jumlah' => 1,
                'jenis_soal' => ['pg'],
                'tingkat' => 'sedang',
            ])
            ->assertOk()
            ->assertJsonPath('ok', true);
    }

    public function test_delete_api_key_locks_generate(): void
    {
        $guru = $this->guru();
        $guru->setGeminiApiKey('AIzaSyTeacherPersonalKeyXYZ1234567');

        $this->actingAs($guru)
            ->deleteJson(route('ai.teacher.gemini-key.destroy'))
            ->assertOk()
            ->assertJsonPath('needs_api_key', true);

        $this->assertFalse($guru->fresh()->hasGeminiApiKey());

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
}
