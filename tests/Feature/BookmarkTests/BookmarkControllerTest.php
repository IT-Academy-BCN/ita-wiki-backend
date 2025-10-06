<?php

declare(strict_types=1);

namespace Tests\Feature\BookmarkTests;

use Tests\TestCase;
use App\Models\User;
use App\Models\Resource;
use App\Models\Bookmark;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BookmarkControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected $resources;

    public function setUp(): void
    {
        parent::setUp();

        // ✅ Usar Spatie en lugar de OldRole
        $this->user = $this->authenticateUserWithRole('student');
        
        $this->resources = Resource::factory(10)->create();
    }

    public function test_authenticated_student_can_get_their_bookmarks(): void
    {
        // Crear bookmarks para el usuario autenticado
        Bookmark::create([
            'github_id' => $this->user->github_id,
            'resource_id' => $this->resources[0]->id
        ]);
        
        Bookmark::create([
            'github_id' => $this->user->github_id,
            'resource_id' => $this->resources[1]->id
        ]);

        $response = $this->getJson(route('bookmarks', $this->user->github_id));

        $response->assertStatus(200)
            ->assertJsonCount(2)
            ->assertJsonFragment(['resource_id' => $this->resources[0]->id])
            ->assertJsonFragment(['resource_id' => $this->resources[1]->id]);
    }

    public function test_user_cannot_get_other_user_bookmarks(): void
    {
        $otherUser = User::factory()->create();
        $otherUser->assignRole('student');

        $response = $this->getJson(route('bookmarks', $otherUser->github_id));

        $response->assertStatus(403)
            ->assertJson(['error' => 'Forbidden - Can only view your own bookmarks']);
    }

    public function test_get_bookmarks_for_user_without_bookmarks_returns_empty_array(): void
    {
        $response = $this->getJson(route('bookmarks', $this->user->github_id));
        
        $response->assertStatus(200)
            ->assertJson([]);
    }

    public function test_authenticated_student_can_create_bookmark(): void
    {
        $resource = $this->resources[2];

        $response = $this->postJson(route('bookmark.create'), [
            'resource_id' => $resource->id
        ]);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'github_id' => $this->user->github_id,
                'resource_id' => $resource->id,
            ]);

        $this->assertDatabaseHas('bookmarks', [
            'github_id' => $this->user->github_id,
            'resource_id' => $resource->id
        ]);
    }

    public function test_cannot_create_duplicate_bookmark(): void
    {
        $resource = $this->resources[3];

        // Crear primer bookmark
        $this->postJson(route('bookmark.create'), [
            'resource_id' => $resource->id
        ]);

        // Intentar crear duplicado
        $response = $this->postJson(route('bookmark.create'), [
            'resource_id' => $resource->id
        ]);

        $response->assertStatus(409)
            ->assertJson(['error' => 'Bookmark already exists']);
    }

    public function test_cannot_create_bookmark_for_nonexistent_resource(): void
    {
        $response = $this->postJson(route('bookmark.create'), [
            'resource_id' => 999999
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['resource_id']);
    }

    public function test_resource_id_is_required_to_create_bookmark(): void
    {
        $response = $this->postJson(route('bookmark.create'), []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['resource_id']);
    }

    public function test_authenticated_student_can_delete_their_bookmark(): void
    {
        $bookmark = Bookmark::create([
            'github_id' => $this->user->github_id,
            'resource_id' => $this->resources[1]->id
        ]);

        $response = $this->deleteJson(route('bookmark.delete'), [
            'resource_id' => $this->resources[1]->id
        ]);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Bookmark deleted successfully']);

        $this->assertDatabaseMissing('bookmarks', [
            'github_id' => $this->user->github_id,
            'resource_id' => $this->resources[1]->id
        ]);
    }

    public function test_cannot_delete_nonexistent_bookmark(): void
    {
        $response = $this->deleteJson(route('bookmark.delete'), [
            'resource_id' => $this->resources[5]->id
        ]);

        $response->assertStatus(404)
            ->assertJson(['error' => 'Bookmark not found']);
    }

    public function test_unauthenticated_user_cannot_create_bookmark(): void
    {
        // Logout
        auth()->guard('api')->logout();

        $response = $this->postJson(route('bookmark.create'), [
            'resource_id' => $this->resources[0]->id
        ]);

        $response->assertStatus(401);
    }

    public function test_unauthenticated_user_cannot_delete_bookmark(): void
    {
        auth()->guard('api')->logout();

        $response = $this->deleteJson(route('bookmark.delete'), [
            'resource_id' => $this->resources[0]->id
        ]);

        $response->assertStatus(401);
    }

    public function test_unauthenticated_user_cannot_get_bookmarks(): void
    {
        auth()->guard('api')->logout();

        $response = $this->getJson(route('bookmarks', $this->user->github_id));

        $response->assertStatus(401);
    }

    public function test_student_without_create_permission_cannot_create_bookmark(): void
    {
        // Crear un usuario sin el permiso 'create bookmarks'
        $userWithoutPermission = User::factory()->create();
        $userWithoutPermission->assignRole('student');
        $userWithoutPermission->revokePermissionTo('create bookmarks');

        $this->actingAs($userWithoutPermission, 'api');

        $response = $this->postJson(route('bookmark.create'), [
            'resource_id' => $this->resources[0]->id
        ]);

        $response->assertStatus(403)
            ->assertJson(['error' => 'Forbidden']);
    }
}