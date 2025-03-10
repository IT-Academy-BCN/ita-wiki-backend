<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Role;
use Database\Seeders\RoleSeeder;

class RoleControllerTest extends TestCase
{
    protected $student;

    public function setUp(): void
    {
        parent::setUp();
        //$this->seed(RoleSeeder::class);
        $this->student = Role::factory()->create([
            'github_id' => 123456,
            'role' => 'student'
        ]);
    }


    public function testCanGetRoleByGithubId(): void
    {
        $response = $this->get('/api/users/user-signedin-as?github_id=123456');
        $response->assertStatus(200)
        ->assertJsonStructure(['message', 'role'])
        ->assertJson([
            'message' => 'Role found.',
            'role' => [
                'github_id' => 123456,
                'role' => 'student'
            ]
        ]);
    }

    public function testSignsUpAsAnonymous(): void
    {
        $random_github_id = random_int(1, 10000000);
        $response = $this->get('/api/users/user-signedin-as?github_id=' . $random_github_id);
        $response->assertStatus(201)
        ->assertJsonStructure(['message', 'role'])
        ->assertJson([
            'message' => 'Role not found. Created as new anonymous user.',
            'role' => [
                'github_id' => $random_github_id,
                'role' => 'anonymous' 
            ]
        ]);
    }

}
