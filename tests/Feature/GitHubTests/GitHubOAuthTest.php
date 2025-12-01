<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GitHubOAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void{
        parent::setUp();
        
        Socialite::shouldReceive('driver->stateless->redirect->getTargetUrl')
            ->andReturn('https://github.com/login/oauth/authorize?client_id=test&redirect_uri=test');
    }

    public function test_can_get_redirect_url(){
        $response = $this->get('/api/auth/github/redirect');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Redirecting to GitHub for authentication'
            ])
            ->assertJsonStructure([
                'success',
                'redirect_url',
                'message'
            ]);
    }

    
    public function test_can_create_user_from_github_callback(){
        $githubUser = new SocialiteUser();
        $githubUser->id = '12345';
        $githubUser->nickname = 'testuser';
        $githubUser->name = 'Test User';
        $githubUser->email = 'test_' . time() . '@example.com'; 
        $githubUser->avatar = 'https://github.com/avatars/test.jpg';

        Socialite::shouldReceive('driver->stateless->user')
            ->andReturn($githubUser);

        $response = $this->get('/api/auth/github/callback');

        $response->assertStatus(302)
            ->assertRedirect();

        $redirectUrl = $response->headers->get('Location');
        $this->assertStringContainsString('http://localhost:5173/auth/callback', $redirectUrl);
        $this->assertStringContainsString('token=', $redirectUrl);
        $this->assertDatabaseHas('users', [
            'github_id' => '12345',
            'github_user_name' => 'testuser',
            'name' => 'Test User',
        ]);
    }

    public function test_can_update_existing_user_from_github_callback(){
        $existingUser = User::factory()->create([
            'github_id' => '12345',
            'github_user_name' => 'oldusername',
            'name' => 'Old Name',
            'email' => 'test_update_' . time() . '@example.com'
        ]);

        $githubUser = new SocialiteUser();
        $githubUser->id = '12345';
        $githubUser->nickname = 'newusername';
        $githubUser->name = 'New Name';
        $githubUser->email = 'test_update_' . time() . '@example.com';
        $githubUser->avatar = 'https://github.com/avatars/new.jpg';

        Socialite::shouldReceive('driver->stateless->user')
            ->andReturn($githubUser);

        $response = $this->get('/api/auth/github/callback');

        $response->assertStatus(302)
            ->assertRedirect();

        $redirectUrl = $response->headers->get('Location');
        $this->assertStringContainsString('http://localhost:5173/auth/callback', $redirectUrl);
        $this->assertStringContainsString('token=', $redirectUrl);

        $this->assertDatabaseHas('users', [
            'id' => $existingUser->id,
            'github_user_name' => 'newusername',
            'name' => 'New Name'
        ]);
    }



    public function test_can_get_user_by_github_id(){
        $user = User::factory()->create([
            'github_id' => '12345',
            'github_user_name' => 'testuser',
            'name' => 'Test User',
            'email' => 'test_get_' . time() . '@example.com',
        ]);

        // Create authentication token
        $token = $user->createToken('test-token')->plainTextToken;

        // Use the endpoint with Sanctum authentication
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->getJson('/api/auth/github/user');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'user' => [
                    'id' => $user->id,
                    'github_id' => '12345',
                    'github_user_name' => 'testuser',
                    'name' => 'Test User',
                ]
            ])
            ->assertJsonStructure([
                'success',
                'user' => [
                    'id',
                    'github_id',
                    'name',
                    'email',
                    'github_user_name',
                ]
            ]);
    }

    public function test_returns_error_when_user_not_found(){
        // The /auth/github/user endpoint requires authentication
        // Without token, should return 401 Unauthenticated
        $response = $this->getJson('/api/auth/github/user');

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.'
            ]);
    }

   
    public function test_handles_errors_in_callback(){
        Socialite::shouldReceive('driver->stateless->user')
            ->andThrow(new \Exception('Authentication error'));

        $response = $this->get('/api/auth/github/callback');

        $response->assertStatus(302)
            ->assertRedirect();

        $redirectUrl = $response->headers->get('Location');
        $this->assertStringContainsString('http://localhost:5173/auth/callback', $redirectUrl);
        $this->assertStringContainsString('success=false', $redirectUrl);
        $this->assertStringContainsString('error=Authentication+error', $redirectUrl);
    }

     
    public function test_uses_correct_frontend_url(){
        config(['app.frontend_url' => 'https://test-frontend.com']);

        $githubUser = new SocialiteUser();
        $githubUser->id = '12345';
        $githubUser->nickname = 'testuser';
        $githubUser->name = 'Test User';
        $githubUser->email = 'test@example.com';

        Socialite::shouldReceive('driver->stateless->user')
            ->andReturn($githubUser);

        $response = $this->get('/api/auth/github/callback');

        $response->assertStatus(302);
        
        $redirectUrl = $response->headers->get('Location');
        $this->assertStringContainsString('https://test-frontend.com/auth/callback', $redirectUrl);
    }
} 