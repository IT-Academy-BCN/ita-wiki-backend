<?php

declare(strict_types=1);

namespace Tests\Feature\ResourceTests;

use Tests\TestCase;
use App\Models\User;
use App\Models\Resource;
use App\Models\Tag;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\DataProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CreateResourceTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    public function setUp(): void
    {
        parent::setUp();
    }

    private function authenticateSanctumUser(int $githubId = 123456): User
    {
        $user = User::factory()->create(['github_id' => $githubId]);

        Sanctum::actingAs($user, ['*']);

        return $user;
    }

    private function getResourceData(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Laravel Best Practices',
            'description' => 'A comprehensive guide to Laravel development',
            'url' => 'https://example.com/laravel-' . uniqid(),
            'category' => 'Fullstack PHP',
            'type' => 'Blog',
            'tags' => null,
            'github_id' => 123456,
        ], $overrides);
    }

    private function getResourceDataWithTags(): array
    {
        $tagNames = Tag::inRandomOrder()->take(3)->pluck('name')->toArray();

        return $this->getResourceData([
            'tags' => $tagNames
        ]);
    }


    // ========== VALIDATION TESTS ==========

    #[DataProvider('resourceCreationValidationProvider')]
    public function test_create_resource_validation(array $invalidData, string $fieldName): void
    {
        $this->authenticateSanctumUser();

        $data = $this->getResourceData();
        $data = array_merge($data, $invalidData);

        $response = $this->postJson(route('resources.store'), $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors($fieldName);
    }

    public static function resourceCreationValidationProvider(): array
    {
        return [
            // title validation
            'missing title' => [['title' => null], 'title'],
            'title too short' => [['title' => 'abc'], 'title'],
            'title too long' => [['title' => str_repeat('a', 256)], 'title'],
            'title is array' => [['title' => []], 'title'],

            // description validation
            'description too short' => [['description' => 'short'], 'description'],
            'description too long' => [['description' => str_repeat('a', 1001)], 'description'],
            'description is array' => [['description' => []], 'description'],

            // url validation
            'missing url' => [['url' => null], 'url'],
            'invalid url format' => [['url' => 'not-a-valid-url'], 'url'],
            'url is array' => [['url' => []], 'url'],
            'url is integer' => [['url' => 123], 'url'],

            // category validation
            'missing category' => [['category' => null], 'category'],
            'invalid category' => [['category' => 'InvalidCategory'], 'category'],

            // type validation
            'missing type' => [['type' => null], 'type'],
            'invalid type' => [['type' => 'InvalidType'], 'type'],

            // tags validation
            'tags not array' => [['tags' => 'not-array'], 'tags'],
            'too many tags' => [['tags' => ['tag1', 'tag2', 'tag3', 'tag4', 'tag5', 'tag6']], 'tags'],
        ];
    }

    public function test_returns_404_when_route_not_found(): void
    {
        $response = $this->postJson('/api/non-existent-route', []);

        $response->assertStatus(404);
    }
}
