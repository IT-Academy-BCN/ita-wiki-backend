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

    protected function setUp(): void
    {
        parent::setUp();
        
        Socialite::shouldReceive('driver->stateless->redirect->getTargetUrl')
            ->andReturn('https://github.com/login/oauth/authorize?client_id=test&redirect_uri=test');
    }

    public function test_can_get_redirect_url()
    {
        $response = $this->get('/api/auth/github/redirect');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Redirigiendo a GitHub para autenticación'
            ])
            ->assertJsonStructure([
                'success',
                'redirect_url',
                'message'
            ]);
    }

    
    public function test_can_create_user_from_github_callback()
    {
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
        $this->assertStringContainsString('success=true', $redirectUrl);
        $this->assertStringContainsString('github_id=12345', $redirectUrl);
        $this->assertStringContainsString('name=Test+User', $redirectUrl);
        $this->assertStringContainsString('github_user_name=testuser', $redirectUrl);

        $this->assertDatabaseHas('users', [
            'github_id' => '12345',
            'github_user_name' => 'testuser',
            'name' => 'Test User',
        ]);
    }

    public function test_can_update_existing_user_from_github_callback()
    {
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
        $this->assertStringContainsString('success=true', $redirectUrl);
        $this->assertStringContainsString('github_id=12345', $redirectUrl);
        $this->assertStringContainsString('name=New+Name', $redirectUrl);
        $this->assertStringContainsString('github_user_name=newusername', $redirectUrl);

        $this->assertDatabaseHas('users', [
            'id' => $existingUser->id,
            'github_user_name' => 'newusername',
            'name' => 'New Name'
        ]);
    }



    public function test_can_get_user_by_github_id()
    {
        $user = User::factory()->create([
            'github_id' => '12345',
            'github_user_name' => 'testuser',
            'name' => 'Test User',
            'email' => 'test_get_' . time() . '@example.com',
        ]);

        $response = $this->get('/api/auth/github/user?github_id=12345');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'user' => [
                    'github_id' => '12345',
                    'github_user_name' => 'testuser',
                    'name' => 'Test User',
                ]
            ])
            ->assertJsonStructure([
                'success',
                'user' => [
                    'github_id',
                    'name',
                    'email',
                    'github_user_name',
                ]
            ]);
    }

    public function test_returns_error_when_user_not_found()
    {
        $response = $this->get('/api/auth/github/user?github_id=99999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Usuario no encontrado'
            ]);
    }

   
    public function test_handles_errors_in_callback()
    {
        Socialite::shouldReceive('driver->stateless->user')
            ->andThrow(new \Exception('Error de autenticación'));

        $response = $this->get('/api/auth/github/callback');

        $response->assertStatus(302)
            ->assertRedirect();

        $redirectUrl = $response->headers->get('Location');
        $this->assertStringContainsString('http://localhost:5173/auth/callback', $redirectUrl);
        $this->assertStringContainsString('success=false', $redirectUrl);
        $this->assertStringContainsString('error=Error+de+autenticaci%C3%B3n', $redirectUrl);
    }

     
    public function test_uses_correct_frontend_url()
    {
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