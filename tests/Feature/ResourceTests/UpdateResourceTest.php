<?php

declare(strict_types=1);

namespace Tests\Feature\ResourceTests;

use Tests\TestCase;
use App\Models\User;
use App\Models\Resource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;

class UpdateResourceTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Resource $resource;

    private function getUpdateData(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Updated Resource Title',
            'description' => 'Updated description for the resource',
            'url' => 'https://updated-url.com',
            'category' => 'React',
            'type' => 'Video',
            'tags' => null
        ], $overrides);
    }

    // ========== SUCCESS TESTS ==========

    public function test_owner_can_update_their_resource(): void
    {
        $this->user = $this->authenticateUserWithRole('student');
        
        // Crear resource del usuario autenticado
        $this->resource = Resource::factory()->create([
            'github_id' => $this->user->github_id
        ]);
        
        $data = $this->getUpdateData();

        $response = $this->putJson(route('resources.update', $this->resource->id), $data);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'title' => 'Updated Resource Title',
                'description' => 'Updated description for the resource',
            ]);

        $this->assertDatabaseHas('resources', [
            'id' => $this->resource->id,
            'title' => 'Updated Resource Title',
            'url' => 'https://updated-url.com',
        ]);
    }

    public function test_admin_can_update_any_resource(): void
    {
        $admin = $this->authenticateUserWithRole('admin');
        
        // ✅ DEBUG: Verifica permessi admin
        dump('Admin ID: ' . $admin->id);
        dump('Admin github_id: ' . $admin->github_id);
        dump('Admin roles: ' . $admin->roles->pluck('name'));
        dump('Admin permissions: ' . $admin->getAllPermissions()->pluck('name'));
        dump('Can edit all resources? ' . ($admin->can('edit all resources') ? 'YES' : 'NO'));
        
        $otherUserResource = Resource::factory()->create();
        
        dump('Resource ID: ' . $otherUserResource->id);
        dump('Resource owner github_id: ' . $otherUserResource->github_id);

        $data = $this->getUpdateData();

        $response = $this->putJson(route('resources.update', $otherUserResource->id), $data);
        
        dump('Response status: ' . $response->status());
        dump('Response body: ' . $response->getContent());

        $response->assertStatus(200);

        $this->assertDatabaseHas('resources', [
            'id' => $otherUserResource->id,
            'title' => 'Updated Resource Title',
        ]);
    }

    // ========== AUTHORIZATION TESTS ==========

    public function test_user_cannot_update_other_user_resource(): void
    {
        $this->user = $this->authenticateUserWithRole('student');
        
        // Crear resource de otro usuario
        $otherUserResource = Resource::factory()->create();

        $data = $this->getUpdateData();

        $response = $this->putJson(route('resources.update', $otherUserResource->id), $data);

        $response->assertStatus(403)
            ->assertJson(['error' => 'Forbidden - Not your resource']);

        // Verificar que NO se actualizó
        $this->assertDatabaseMissing('resources', [
            'id' => $otherUserResource->id,
            'title' => 'Updated Resource Title',
        ]);
    }

    public function test_unauthenticated_user_cannot_update_resource(): void
    {
        // ✅ Crear resource sin autenticación previa
        $resource = Resource::factory()->create();
        
        $data = $this->getUpdateData();

        // ✅ NO authentication
        $response = $this->putJson(route('resources.update', $resource->id), $data);

        $response->assertStatus(401);
    }

    // ========== VALIDATION TESTS ==========

    #[DataProvider('resourceUpdateValidationProvider')]
    public function test_update_resource_validation(array $invalidData, string $fieldName): void
    {
        $this->user = $this->authenticateUserWithRole('student');
        
        $this->resource = Resource::factory()->create([
            'github_id' => $this->user->github_id
        ]);
        
        $data = $this->getUpdateData();
        $data = array_merge($data, $invalidData);

        $response = $this->putJson(route('resources.update', $this->resource->id), $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors($fieldName);

        // Verificar que NO se actualizó
        $this->assertDatabaseHas('resources', [
            'id' => $this->resource->id,
            'title' => $this->resource->title,
            'url' => $this->resource->url,
        ]);
    }

    public static function resourceUpdateValidationProvider(): array
    {
        return [
            'missing title' => [['title' => null], 'title'],
            'title too short' => [['title' => 'a'], 'title'],
            'title too long' => [['title' => str_repeat('a', 256)], 'title'],
            'title is array' => [['title' => []], 'title'],
            'description too short' => [['description' => 'short'], 'description'],
            'description too long' => [['description' => str_repeat('a', 1001)], 'description'],
            'description is array' => [['description' => []], 'description'],
            'missing url' => [['url' => null], 'url'],
            'invalid url' => [['url' => 'not a url'], 'url'],
            'url is array' => [['url' => []], 'url'],
            'url is integer' => [['url' => 123], 'url'],
        ];
    }
}