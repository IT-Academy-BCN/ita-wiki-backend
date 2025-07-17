<?php

declare (strict_types= 1);

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Resource;
use App\Models\Like;
use Spatie\Permission\Models\Role as SpatieRole;
use Illuminate\Support\Facades\DB;

class LikeControllerTest extends TestCase
{
    protected $student;
    protected $resources;
    protected $likes;

    public function setUp(): void
    {
        parent::setUp();

        SpatieRole::findOrCreate('student', 'web');
        $this->student = User::factory()->create([
            'github_id' => 9871315,
        ]);
        $this->student->assignRole('student');

        DB::table('user_roles')->insert([
            'github_id' => $this->student->github_id,
            'role' => 'student',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->resources = Resource::factory(10)->create([
            'github_id' => $this->student->github_id,
        ]);

        $this->likes = [
            Like::create([
            'github_id' => $this->student->github_id,
            'resource_id' => $this->resources[0]->id]),
            Like::create([
            'github_id' => $this->student->github_id,
            'resource_id' => $this->resources[1]->id])
        ];
    }

    public function testGetStudentLikes(): void
    {
        $response = $this->get('api/likes/' . $this->student->github_id);
        $response->assertStatus(200)
            ->assertJsonCount(2)
            ->assertJson([
                ['github_id' => $this->student->github_id, 'resource_id' => $this->resources[0]->id],
                ['github_id' => $this->student->github_id, 'resource_id' => $this->resources[1]->id]
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
            'github_id' => $this->student->github_id,
            'resource_id' => $this->likes[1]->resource_id
        ]);
                
        $response->assertStatus(200)
            ->assertJson(['message' => 'Like deleted successfully']);


        $this->assertDatabaseMissing('likes', [
            'github_id' => $this->student->github_id,
            'resource_id' => $this->likes[1]->resource_id
        ]);
    }

    public function testCreateLike(): void
    {
        $response = $this->post('api/likes', [
            'github_id' => $this->student->github_id,
            'resource_id' => $this->resources[2]->id
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'github_id' => $this->student->github_id,
                'resource_id' => $this->resources[2]->id,
            ]);

        $this->assertDatabaseHas('likes', [
            'github_id' => $this->student->github_id,
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
            'github_id' => $this->student->github_id,
            'resource_id' => 447012
        ]);
        $response->assertStatus(422);
    }
}
