<?php

declare(strict_types=1);

namespace Tests\Feature\LikeTests;

use Tests\TestCase;
use App\Models\User;
use App\Models\Resource;
use App\Models\Like;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LikeControllerTest extends TestCase
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

    public function test_authenticated_student_can_get_their_likes(): void
    {
        $this->user = $this->authenticateUserWithRole('student');

        Like::create([
            'github_id' => $this->user->github_id,
            'resource_id' => $this->resources[0]->id
        ]);

        Like::create([
            'github_id' => $this->user->github_id,
            'resource_id' => $this->resources[1]->id
        ]);

        $response = $this->getJson(route('likes', $this->user->github_id));

        $response->assertStatus(200)
            ->assertJsonCount(2);
    }

    public function test_user_cannot_get_other_user_likes(): void
    {
        $this->user = $this->authenticateUserWithRole('student');

        $otherUser = User::factory()->create();
        $otherUser->assignRole('student');

        $response = $this->getJson(route('likes', $otherUser->github_id));

        $response->assertStatus(403);
    }

    public function test_get_likes_for_user_without_likes_returns_empty_array(): void
    {
        $this->user = $this->authenticateUserWithRole('student');

        $response = $this->getJson(route('likes', $this->user->github_id));

        $response->assertStatus(200)
    public function testGetLikesForNonexistentUserReturnsEmptyArray(): void {
        $nonExistentGithubId = 38928374;
        $response = $this->get('api/likes/' . $nonExistentGithubId); 
        $response->assertStatus(200)  
            ->assertJson([]);
    }

    public function test_authenticated_student_can_create_like(): void
    {
        $this->user = $this->authenticateUserWithRole('student');

        $resource = $this->resources[2];

        $response = $this->postJson(route('like.create'), [
            'resource_id' => $resource->id
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('likes', [
            'github_id' => $this->user->github_id,
            'resource_id' => $resource->id
        ]);
    }

    public function test_cannot_create_duplicate_like(): void
    {
        $this->user = $this->authenticateUserWithRole('student');

        $resource = $this->resources[3];

        $this->postJson(route('like.create'), [
            'resource_id' => $resource->id
        ]);

        $response = $this->postJson(route('like.create'), [
            'resource_id' => $resource->id
        ]);

        $response->assertStatus(409);
    }

    public function test_cannot_create_like_for_nonexistent_resource(): void
    {
        $this->user = $this->authenticateUserWithRole('student');

        $response = $this->postJson(route('like.create'), [
            'resource_id' => 999999
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['resource_id']);
    }

    public function test_resource_id_is_required_to_create_like(): void
    {
        $this->user = $this->authenticateUserWithRole('student');

        $response = $this->postJson(route('like.create'), []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['resource_id']);
    }

    public function test_authenticated_student_can_delete_their_like(): void
    {
        $this->user = $this->authenticateUserWithRole('student');

        Like::create([
            'github_id' => $this->user->github_id,
            'resource_id' => $this->resources[1]->id
        ]);

        $response = $this->deleteJson(route('like.delete'), [
            'resource_id' => $this->resources[1]->id
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseMissing('likes', [
            'github_id' => $this->user->github_id,
            'resource_id' => $this->resources[1]->id
        ]);
    }

    public function test_cannot_delete_nonexistent_like(): void
    {
        $this->user = $this->authenticateUserWithRole('student');

        $response = $this->deleteJson(route('like.delete'), [
            'resource_id' => $this->resources[5]->id
        ]);

        $response->assertStatus(404);
    }

    // ========== UNAUTHENTICATED TESTS ==========

    public function test_unauthenticated_user_cannot_create_like(): void
    {
      
        $response = $this->postJson(route('like.create'), [
            'resource_id' => $this->resources[0]->id
        ]);

        $response->assertStatus(401);
    }

    public function test_unauthenticated_user_cannot_delete_like(): void
    {
    
        $response = $this->deleteJson(route('like.delete'), [
            'resource_id' => $this->resources[0]->id
        ]);

        $response->assertStatus(401);
    }

    public function test_unauthenticated_user_cannot_get_likes(): void
    {
        $user = User::factory()->create();

        $response = $this->getJson(route('likes', $user->github_id));

        $response->assertStatus(401);
    }
}
