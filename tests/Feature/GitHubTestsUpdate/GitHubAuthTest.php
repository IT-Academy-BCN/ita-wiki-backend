<?php

namespace Tests\Feature\GitHubTestsUpdate;

use Tests\TestCase;
use App\Models\User;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GitHubAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Socialite::shouldReceive('driver->stateless->redirect->getTargetUrl')
            ->andReturn('https://github.com/login/oauth/authorize?client_id=test&redirect_uri=test');
    }

    public function test_auth_github_redirect()
    {
        $response = $this->get('/api/auth/github');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Redirigiendo a GitHub para autenticación'
            ]);
    }

    public function test_auth_github_callback()
    {
        // Mock del usuario de GitHub con datos únicos
       $response = $this->get('/api/auth/github/callback');

       $response->assertStatus(200)
           ->assertJson([
               'success' => true,
               'message' => 'Usuario autenticado con éxito',
           ]);
    }

    public function test_auth_github_logout()
    {
        $response = $this->get('/api/auth/logout');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Logged out successfully'
            ]);
    }
}
