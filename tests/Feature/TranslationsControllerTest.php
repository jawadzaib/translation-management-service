<?php

namespace Tests\Feature;

use App\Models\Translation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class TranslationsControllerTest extends TestCase
{
    use RefreshDatabase;

    protected string $token;

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::factory()->create();
        $token = Str::random(60);
        $user->api_token = $token;
        $user->save();

        $this->token = $token;
    }

    protected function authHeaders(): array
    {
        return ['Authorization' => 'Bearer ' . $this->token];
    }

    public function test_can_list_translations()
    {
        Translation::factory()->count(5)->create();

        $response = $this->getJson('/api/translations', $this->authHeaders());

        $response->assertOk()->assertJsonStructure(['data']);
    }

    public function test_can_filter_translations_by_key()
    {
        Translation::factory()->create(['key' => 'special_key']);
        Translation::factory()->create(['key' => 'other_key']);

        $response = $this->getJson('/api/translations?key=special', $this->authHeaders());

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
    }

    public function test_can_create_translation_with_tags()
    {
        $payload = [
            'key' => 'home.title',
            'value' => 'Home',
            'locale' => 'en',
            'tags' => ['web', 'mobile']
        ];

        $response = $this->postJson('/api/translations', $payload, $this->authHeaders());

        $response->assertCreated()
            ->assertJsonFragment(['key' => 'home.title'])
            ->assertJsonStructure(['tags']);
    }

    public function test_can_show_translation()
    {
        $translation = Translation::factory()->create();

        $response = $this->getJson("/api/translations/{$translation->id}", $this->authHeaders());

        $response->assertOk()->assertJsonFragment(['id' => $translation->id]);
    }

    public function test_can_update_translation()
    {
        $translation = Translation::factory()->create();

        $payload = [
            'key' => 'updated.key',
            'value' => 'Updated Value',
            'locale' => 'en',
            'tags' => ['updated']
        ];

        $response = $this->putJson("/api/translations/{$translation->id}", $payload, $this->authHeaders());

        $response->assertOk()->assertJsonFragment(['key' => 'updated.key']);
    }

    public function test_can_delete_translation()
    {
        $translation = Translation::factory()->create();

        $response = $this->deleteJson("/api/translations/{$translation->id}", [], $this->authHeaders());

        $response->assertNoContent();
        $this->assertDatabaseMissing('translations', ['id' => $translation->id]);
    }

    public function test_can_export_translations()
    {
        Translation::factory()->create([
            'key' => 'export.key',
            'value' => 'Exported',
            'locale' => 'en',
        ]);

        $response = $this->get('/api/translations/export?locale=en', $this->authHeaders());

        // Capture streamed content
        ob_start();
        $response->sendContent();
        $output = ob_get_clean();

        $this->assertStringContainsString('"export.key":"Exported"', $output);
    }
}