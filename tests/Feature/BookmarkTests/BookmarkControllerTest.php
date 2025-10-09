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

        $this->resources = Resource::factory(10)->create();
    }

    // ========== AUTHENTICATED TESTS ==========

    public function test_authenticated_student_can_get_their_bookmarks(): void
    {
        $this->user = $this->authenticateUserWithRole('student');

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
            ->assertJsonCount(2);
    }

    public function test_user_cannot_get_other_user_bookmarks(): void
    {
        $this->user = $this->authenticateUserWithRole('student');

        $otherUser = User::factory()->create();
        $otherUser->assignRole('student');

        $response = $this->getJson(route('bookmarks', $otherUser->github_id));

        $response->assertStatus(403);
    }

    public function test_get_bookmarks_for_user_without_bookmarks_returns_empty_array(): void
    {
        $this->user = $this->authenticateUserWithRole('student');

        $response = $this->getJson(route('bookmarks', $this->user->github_id));

        $response->assertStatus(200)
            ->assertJsonCount(0);
    }
    
    public function test_get_bookmarks_for_nonexistent_user_returns_empty_array(): void 
    {
        $this->authenticateUserWithRole('admin'); 
        
        $nonExistentGithubId = 38928374;
        $response = $this->getJson('/api/bookmarks/' . $nonExistentGithubId);
        
        $response->assertStatus(200)  
            ->assertJson([]);
    }

    public function test_authenticated_student_can_create_bookmark(): void
    {
        $this->user = $this->authenticateUserWithRole('student');

        $resource = $this->resources[2];

        $response = $this->postJson(route('bookmark.create'), [
            'resource_id' => $resource->id
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('bookmarks', [
            'github_id' => $this->user->github_id,
            'resource_id' => $resource->id
        ]);
    }

    public function test_cannot_create_duplicate_bookmark(): void
    {
        $this->user = $this->authenticateUserWithRole('student');

        $resource = $this->resources[3];

        $this->postJson(route('bookmark.create'), [
            'resource_id' => $resource->id
        ]);

        $response = $this->postJson(route('bookmark.create'), [
            'resource_id' => $resource->id
        ]);

        $response->assertStatus(409);
    }

    public function test_cannot_create_bookmark_for_nonexistent_resource(): void
    {
        $this->user = $this->authenticateUserWithRole('student');

        $response = $this->postJson(route('bookmark.create'), [
            'resource_id' => 999999
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['resource_id']);
    }

    public function test_resource_id_is_required_to_create_bookmark(): void
    {
        $this->user = $this->authenticateUserWithRole('student');

        $response = $this->postJson(route('bookmark.create'), []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['resource_id']);
    }

    public function test_authenticated_student_can_delete_their_bookmark(): void
    {
        $this->user = $this->authenticateUserWithRole('student');

        Bookmark::create([
            'github_id' => $this->user->github_id,
            'resource_id' => $this->resources[1]->id
        ]);

        $response = $this->deleteJson(route('bookmark.delete'), [
            'resource_id' => $this->resources[1]->id
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseMissing('bookmarks', [
            'github_id' => $this->user->github_id,
            'resource_id' => $this->resources[1]->id
        ]);
    }

    public function test_cannot_delete_nonexistent_bookmark(): void
    {
        $this->user = $this->authenticateUserWithRole('student');

        $response = $this->deleteJson(route('bookmark.delete'), [
            'resource_id' => $this->resources[5]->id
        ]);

        $response->assertStatus(404);
    }

    // ========== UNAUTHENTICATED TESTS ==========

    public function test_unauthenticated_user_cannot_create_bookmark(): void
    {
        $response = $this->postJson(route('bookmark.create'), [
            'resource_id' => $this->resources[0]->id
        ]);

        $response->assertStatus(401);
    }

    public function test_unauthenticated_user_cannot_delete_bookmark(): void
    {
        $response = $this->deleteJson(route('bookmark.delete'), [
            'resource_id' => $this->resources[0]->id
        ]);

        $response->assertStatus(401);
    }

    public function test_unauthenticated_user_cannot_get_bookmarks(): void
    {
        $user = User::factory()->create();

        $response = $this->getJson(route('bookmarks', $user->github_id));

        $response->assertStatus(401);
    }

    // ========== PERMISSION TESTS ==========

    public function test_student_without_create_permission_cannot_create_bookmark(): void
    {
        $userWithoutRole = User::factory()->create();
        
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        
        $this->actingAs($userWithoutRole, 'api');

        $response = $this->postJson(route('bookmark.create'), [
            'resource_id' => $this->resources[0]->id
        ]);

        $response->assertStatus(403)
            ->assertJson(['error' => 'Forbidden']);
    }
}