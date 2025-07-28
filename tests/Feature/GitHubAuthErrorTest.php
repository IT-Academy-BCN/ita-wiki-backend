<?php

namespace Tests\Feature;

use Tests\TestCase;
use Laravel\Socialite\Facades\Socialite;
use Mockery;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GitHubAuthErrorTest extends TestCase
{
    public function test_github_callback_redirects_to_frontend_with_error_on_network_error()
    {
        // Mock correcto para Socialite con stateless()
        Socialite::shouldReceive('driver->stateless->user')
            ->andThrow(new RequestException('Network error', new \GuzzleHttp\Psr7\Request('GET', 'test')));

        $response = $this->get('/api/auth/github/callback');

        // El controlador redirige al frontend con error en lugar de retornar 500
        $response->assertStatus(302)
            ->assertRedirect();

        $redirectUrl = $response->headers->get('Location');
        $this->assertStringContainsString('http://localhost:5173/auth/callback', $redirectUrl);
        $this->assertStringContainsString('success=false', $redirectUrl);
        $this->assertStringContainsString('error=', $redirectUrl);
        
        // Verificar que no se exponen detalles internos en la URL
        $this->assertStringNotContainsString('Stack trace', $redirectUrl);
        $this->assertStringNotContainsString('Exception', $redirectUrl);
        $this->assertStringNotContainsString('Guzzle', $redirectUrl);
    }

    public function test_github_callback_redirects_to_frontend_with_error_on_generic_exception()
    {
        // Mock correcto para Socialite con stateless()
        Socialite::shouldReceive('driver->stateless->user')
            ->andThrow(new \Exception('Unexpected error'));

        $response = $this->get('/api/auth/github/callback');

        // El controlador redirige al frontend con error en lugar de retornar 500
        $response->assertStatus(302)
            ->assertRedirect();

        $redirectUrl = $response->headers->get('Location');
        $this->assertStringContainsString('http://localhost:5173/auth/callback', $redirectUrl);
        $this->assertStringContainsString('success=false', $redirectUrl);
        $this->assertStringContainsString('error=Unexpected+error', $redirectUrl);
        
        // Verificar que no se exponen detalles internos en la URL
        $this->assertStringNotContainsString('Stack trace', $redirectUrl);
        $this->assertStringNotContainsString('Exception', $redirectUrl);
    }

    public function test_github_redirect_returns_500_on_error()
    {
        // Mock para simular error en el método redirect
        Socialite::shouldReceive('driver->stateless->redirect->getTargetUrl')
            ->andThrow(new \Exception('Error al generar URL de redirección'));

        $response = $this->get('/api/auth/github/redirect');

        $response->assertStatus(500)
            ->assertJson([
                'success' => false,
                'message' => 'Error al generar URL de redirección: Error al generar URL de redirección'
            ]);
    }
} 