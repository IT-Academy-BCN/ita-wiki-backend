<?php

namespace Tests\Feature;

use Tests\TestCase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Mockery;

class GitHubAuthControllerTest extends TestCase
{
    public function test_callback_redirects_to_frontend_with_user_data()
    {
        // Mock del usuario de GitHub con email único
        $abstractUser = Mockery::mock(SocialiteUser::class);
        $abstractUser->shouldReceive('getId')->andReturn('12345');
        $abstractUser->shouldReceive('getName')->andReturn('Test User');
        $abstractUser->shouldReceive('getEmail')->andReturn('test_' . time() . '@example.com');
        $abstractUser->shouldReceive('getNickname')->andReturn('testuser');

        // Mock de Socialite
        Socialite::shouldReceive('driver->stateless->user')
            ->andReturn($abstractUser);

        $response = $this->get('/api/auth/github/callback');

        // El controlador ahora redirige al frontend
        $response->assertStatus(302)
            ->assertRedirect();

        $redirectUrl = $response->headers->get('Location');
        $this->assertStringContainsString('http://localhost:5173/auth/callback', $redirectUrl);
        $this->assertStringContainsString('success=true', $redirectUrl);
        $this->assertStringContainsString('github_id=12345', $redirectUrl);
        $this->assertStringContainsString('name=Test+User', $redirectUrl);
        $this->assertStringContainsString('github_user_name=testuser', $redirectUrl);
    }
}
