<?php

declare(strict_types=1);

namespace Tests\Feature\ResourceTests;

use Tests\TestCase;
use App\Models\User;
use App\Models\Resource;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;

class CreateResourceTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    public function setUp(): void
    {
        parent::setUp();
    }

    private function getResourceData(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Laravel Best Practices',
            'description' => 'A comprehensive guide to Laravel development',
            'url' => 'https://example.com/laravel-' . uniqid(),
            'category' => 'Fullstack PHP',
            'type' => 'Blog',
            'tags' => null
        ], $overrides);
    }

    private function getResourceDataWithTags(): array
    {
        $tagNames = Tag::inRandomOrder()->take(3)->pluck('name')->toArray();

        return $this->getResourceData([
            'tags' => $tagNames
        ]);
    }

    // ========== SUCCESS TESTS ==========

   /*public function test_authenticated_student_can_create_resource(): void
    {
        $this->user = $this->authenticateUserWithRole('student');
        
        $data = $this->getResourceData();

        $response = $this->postJson(route('resources.store'), $data);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'github_id',
                    'title',
                    'description',
                    'url',
                    'category',
                    'type',
                    'created_at',
                    'updated_at'
                ]
            ]);

        $this->assertDatabaseHas('resources', [
            'github_id' => $this->user->github_id,
            'title' => $data['title'],
            'url' => $data['url'],
        ]);
    }*/

    /*public function test_authenticated_student_can_create_resource_with_tags(): void
    {
        $this->user = $this->authenticateUserWithRole('student');
        
        $data = $this->getResourceDataWithTags();

        $response = $this->postJson(route('resources.store'), $data);

        $response->assertStatus(201)
            ->assertJsonFragment(['title' => $data['title']]);

        $this->assertDatabaseHas('resources', [
            'github_id' => $this->user->github_id,
            'title' => $data['title'],
        ]);
    }

    // ========== AUTHENTICATION TESTS ==========

    public function test_unauthenticated_user_cannot_create_resource(): void
    {
        $response = $this->postJson(route('resources.store'), $this->getResourceData());

        $response->assertStatus(401);
    }*/

    // ========== VALIDATION TESTS ==========

    #[DataProvider('resourceCreationValidationProvider')]
    public function test_create_resource_validation(array $invalidData, string $fieldName): void
    {
       // $this->user = $this->authenticateUserWithRole('student');
        $githubId = 123456;
        
        // Crear usuario primero para evitar foreign key constraint
        User::factory()->create(['github_id' => $githubId]);
        
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
        $this->user = $this->authenticateUserWithRole('student');
        
        $response = $this->postJson('/api/non-existent-route', []);

        $response->assertStatus(404);
    }
}