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

    //protected User $user;
    protected $resources;

    public function setUp(): void
    {
        parent::setUp();
        // Create a user with github_id=123456 for the tests
        User::factory()->create([
            'github_id' => 123456,
        ]);
        $this->resources = Resource::factory(10)->create();
    }

  
    public function test_get_likes_for_user_without_likes_returns_empty_array(): void
    {
        // $this->user = $this->authenticateUserWithRole('student');
        $githubId = 123456;
        $response = $this->getJson(route('likes', $githubId));
        $response->assertStatus(200)
            ->assertJsonCount(0);
    }

    public function test_get_likes_for_nonexistent_user_returns_empty_array(): void 
    {
        // $this->authenticateUserWithRole('admin');
        $nonExistentGithubId = 38928374;
        $response = $this->getJson('/api/likes/' . $nonExistentGithubId); 
        $response->assertStatus(200)  
            ->assertJson([]);
    }

    public function test_authenticated_student_can_create_like(): void
    {
        // $this->user = $this->authenticateUserWithRole('student');
        $githubId = 123456;
        $resource = $this->resources[2];
        $response = $this->postJson(route('like.create'), [
            'github_id' => $githubId,
            'resource_id' => $resource->id
        ]);
        $response->assertStatus(201);
        $this->assertDatabaseHas('likes', [
            'github_id' => $githubId,
            'resource_id' => $resource->id
        ]);
    }

    public function test_cannot_create_duplicate_like(): void
    {
        // $this->user = $this->authenticateUserWithRole('student');
        $githubId = 123456;
        $resource = $this->resources[3];
        $this->postJson(route('like.create'), [
            'github_id' => $githubId,
            'resource_id' => $resource->id
        ]);
        $response = $this->postJson(route('like.create'), [
            'github_id' => $githubId,
            'resource_id' => $resource->id
        ]);
        $response->assertStatus(409);
    }

    public function test_cannot_create_like_for_nonexistent_resource(): void
    {
        // $this->user = $this->authenticateUserWithRole('student');
        $githubId = 123456;
        $response = $this->postJson(route('like.create'), [
            'github_id' => $githubId,
            'resource_id' => 999999
        ]);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['resource_id']);
    }

    public function test_resource_id_is_required_to_create_like(): void
    {
        // $this->user = $this->authenticateUserWithRole('student');
        $githubId = 123456;
        $response = $this->postJson(route('like.create'), [
            'github_id' => $githubId
        ]);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['resource_id']);
    }

   
    public function test_cannot_delete_nonexistent_like(): void
    {
        // $this->user = $this->authenticateUserWithRole('student');
        /*$response = $this->deleteJson(route('like.delete'), [
            'github_id' => $githubId,
            'resource_id' => $this->resources[5]->id
        ]);*/
        
        $githubId = 123456;
        $response = $this->delete("/api/likes", [
            'github_id' => $githubId,
            'resource_id' => $this->resources[5]->id
        ]);

        $response->assertStatus(404);
    }

    
}
