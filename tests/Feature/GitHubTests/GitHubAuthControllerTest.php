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
        $abstractUser = Mockery::mock(SocialiteUser::class);
        $abstractUser->shouldReceive('getId')->andReturn('12345');
        $abstractUser->shouldReceive('getName')->andReturn('Test User');
        $abstractUser->shouldReceive('getEmail')->andReturn('test_' . time() . '@example.com');
        $abstractUser->shouldReceive('getNickname')->andReturn('testuser');

        Socialite::shouldReceive('driver->stateless->user')
            ->andReturn($abstractUser);

        $response = $this->get('/api/auth/github/callback');

        $response->assertStatus(302)
            ->assertRedirect();

        $redirectUrl = $response->headers->get('Location');
        $this->assertStringContainsString('http://localhost:5173/auth/callback', $redirectUrl);
        // El nuevo formato solo envía el token
        $this->assertStringContainsString('token=', $redirectUrl);
        
        // Verificar que el usuario fue creado en la base de datos
        $this->assertDatabaseHas('users', [
            'github_id' => '12345',
            'github_user_name' => 'testuser',
            'name' => 'Test User',
        ]);
    }
}
