<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\OldRole;

class OldRoleControllerTest extends TestCase
{
    protected $student;

    public function setUp(): void
    {
        parent::setUp();
        $this->student = OldRole::factory()->create([
            'github_id' => 123456,
            'role' => 'student'
        ]);
    }

    public function testCanGetRoleByGithubId(): void
    {
        $response = $this->post(route('login'), [
            'github_id' => 123456
        ]);

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

    public function testRoleNotFound(): void
    {
        $random_github_id = random_int(1, 10000000);
        $response = $this->post(route('login'), [
            'github_id' => $random_github_id
        ]);

        $response->assertStatus(404)
            ->assertJsonStructure(['message'])
            ->assertJson([
                'message' => 'Role not found.',
            ]);
    }
}