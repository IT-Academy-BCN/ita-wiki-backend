<?php

declare (strict_types= 1);

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Resource;
use App\Models\Like;
use App\Models\OldRole; // ← Eliminar cuando Spatie esté implementado
use Illuminate\Foundation\Testing\RefreshDatabase;

class LikeControllerTest extends TestCase
{
    use RefreshDatabase;
    protected $user;
    protected $resources;
    protected $likes;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'github_id' => 9871315,
        ]);
    
        // ELIMINAR: cuando Spatie esté implementado
        OldRole::factory()->create([
            'github_id' => $this->user->github_id,
            'role' => 'student'
        ]);

        $this->resources = Resource::factory(10)->create();

        $this->likes = [
            Like::create([
                'github_id' => $this->user->github_id,
                'resource_id' => $this->resources[0]->id]),
            Like::create([
                'github_id' => $this->user->github_id,
                'resource_id' => $this->resources[1]->id])
        ];
    }

    public function testGetStudentLikes(): void
    {
        $response = $this->get('api/likes/' . $this->user->github_id);

        // ← AÑADIR ESTAS LÍNEAS PARA DEBUG
        if ($response->status() !== 200) {
            dump('Response status: ' . $response->status());
            dump('Response body: ' . $response->getContent());
        }
    
        $response->assertStatus(200)
            ->assertJsonCount(2)
            ->assertJson([
                ['github_id' => $this->user->github_id, 'resource_id' => $this->resources[0]->id],
                ['github_id' => $this->user->github_id, 'resource_id' => $this->resources[1]->id]
            ]);
    }

    public function testGetLikesForUnexistentRoleFails(): void {
        $nonExistentGithubId = 38928374;
        $response = $this->get('api/bookmarks/' . $nonExistentGithubId);
        $response->assertStatus(422);
    }

    public function testDestroyLike(): void
    {
        $response = $this->delete('api/likes', [
            'github_id' => $this->user->github_id,
            'resource_id' => $this->likes[1]->resource_id
        ]);
                
        $response->assertStatus(200)
            ->assertJson(['message' => 'Like deleted successfully']);


        $this->assertDatabaseMissing('likes', [
            'github_id' => $this->user->github_id,
            'resource_id' => $this->likes[1]->resource_id
        ]);
    }

    public function testCreateLike(): void
    {
        $response = $this->post('api/likes', [
            'github_id' => $this->user->github_id,
            'resource_id' => $this->resources[2]->id
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'github_id' => $this->user->github_id,
                'resource_id' => $this->resources[2]->id,
            ]);

        $this->assertDatabaseHas('likes', [
            'github_id' => $this->user->github_id,
            'resource_id' => $this->resources[2]->id
        ]);
    }

    public function testCreateLikeForNonexistentRoleFails(): void {
        $response = $this->post('api/likes', [
            'github_id' => 9384758,
            'resource_id' => $this->resources[2]->id
        ]);
        $response->assertStatus(422);
    }

    public function testCreateLikeForNonexistentResourceFails(): void {
        $response = $this->post('api/likes', [
            'github_id' => $this->user->github_id,
            'resource_id' => 447012
        ]);
        $response->assertStatus(422);
    }
}
