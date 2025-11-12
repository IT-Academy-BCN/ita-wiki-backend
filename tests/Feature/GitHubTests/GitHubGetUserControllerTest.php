<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GitHubGetUserControllerTest extends TestCase
{
    use RefreshDatabase;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'github_user_name' => 'testuser',
        ]);
    }
        

    public function test_get_session_user()
    {
        $response = $this->get('/api/auth/github/getSessionUser?github_id=' . $this->user->github_id);

        $response->assertStatus(200);

        $this->assertTrue($response['success']);
        $this->assertEquals($this->user->github_id, $response['user']['github_id']);
        $this->assertNotEmpty($response['php_session']);
    }

    public function test_get_user_not_found()
    {
        $response = $this->get('/api/auth/github/getSessionUser?github_id=nonexistent');

        $response->assertStatus(404);

        $this->assertFalse($response['success']);
        $this->assertEquals('User not found', $response['message']);
        $this->assertNotEmpty($response['php_session']);
    }
    
    public function test_get_user_no_github_id()
    {
        $response = $this->get('/api/auth/github/getSessionUser');

        $response->assertStatus(400);

        $this->assertFalse($response['success']);
        $this->assertEquals('github_id is required', $response['message']);
        $this->assertNotEmpty($response['php_session']);
    }
}
