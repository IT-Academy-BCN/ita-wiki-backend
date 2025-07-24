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
        
        // Mock Socialite para evitar llamadas reales a GitHub
        Socialite::shouldReceive('driver->stateless->redirect->getTargetUrl')
            ->andReturn('https://github.com/login/oauth/authorize?client_id=test&redirect_uri=test');
    }

    /**
     * Test que verifica que se puede obtener la URL de redirección
     */
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

    /**
     * Test que verifica que se puede crear un nuevo usuario desde GitHub
     */
    public function test_can_create_user_from_github_callback()
    {
        // Mock del usuario de GitHub con datos únicos
        $githubUser = new SocialiteUser();
        $githubUser->id = '12345';
        $githubUser->nickname = 'testuser';
        $githubUser->name = 'Test User';
        $githubUser->email = 'test_' . time() . '@example.com'; // Email único
        $githubUser->avatar = 'https://github.com/avatars/test.jpg';

        Socialite::shouldReceive('driver->stateless->user')
            ->andReturn($githubUser);

        $response = $this->get('/api/auth/github/callback');

        // Ahora el callback redirige al frontend en lugar de retornar JSON
        $response->assertStatus(302)
            ->assertRedirect();

        // Verificar que la URL de redirección contiene los parámetros correctos
        $redirectUrl = $response->headers->get('Location');
        $this->assertStringContainsString('http://localhost:5173/auth/callback', $redirectUrl);
        $this->assertStringContainsString('success=true', $redirectUrl);
        $this->assertStringContainsString('github_id=12345', $redirectUrl);
        $this->assertStringContainsString('name=Test+User', $redirectUrl);
        $this->assertStringContainsString('github_user_name=testuser', $redirectUrl);

        // Verificar que el usuario se creó en la base de datos
        $this->assertDatabaseHas('users', [
            'github_id' => '12345',
            'github_user_name' => 'testuser',
            'name' => 'Test User',
        ]);
    }

    /**
     * Test que verifica que se puede actualizar un usuario existente
     */
    public function test_can_update_existing_user_from_github_callback()
    {
        // Crear usuario existente con email único
        $existingUser = User::factory()->create([
            'github_id' => '12345',
            'github_user_name' => 'oldusername',
            'name' => 'Old Name',
            'email' => 'test_update_' . time() . '@example.com'
        ]);

        // Mock del usuario de GitHub con datos actualizados
        $githubUser = new SocialiteUser();
        $githubUser->id = '12345';
        $githubUser->nickname = 'newusername';
        $githubUser->name = 'New Name';
        $githubUser->email = 'test_update_' . time() . '@example.com';
        $githubUser->avatar = 'https://github.com/avatars/new.jpg';

        Socialite::shouldReceive('driver->stateless->user')
            ->andReturn($githubUser);

        $response = $this->get('/api/auth/github/callback');

        // Ahora el callback redirige al frontend
        $response->assertStatus(302)
            ->assertRedirect();

        // Verificar que la URL de redirección contiene los datos actualizados
        $redirectUrl = $response->headers->get('Location');
        $this->assertStringContainsString('success=true', $redirectUrl);
        $this->assertStringContainsString('github_id=12345', $redirectUrl);
        $this->assertStringContainsString('name=New+Name', $redirectUrl);
        $this->assertStringContainsString('github_user_name=newusername', $redirectUrl);

        // Verificar que el usuario se actualizó en la base de datos
        $this->assertDatabaseHas('users', [
            'id' => $existingUser->id,
            'github_user_name' => 'newusername',
            'name' => 'New Name'
        ]);
    }

    /**
     * Test que verifica que se puede obtener información del usuario por GitHub ID
     */
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

    /**
     * Test que verifica que retorna error cuando el usuario no existe
     */
    public function test_returns_error_when_user_not_found()
    {
        $response = $this->get('/api/auth/github/user?github_id=99999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Usuario no encontrado'
            ]);
    }

    /**
     * Test que verifica el manejo de errores en el callback
     */
    public function test_handles_errors_in_callback()
    {
        // Mock para simular un error en Socialite
        Socialite::shouldReceive('driver->stateless->user')
            ->andThrow(new \Exception('Error de autenticación'));

        $response = $this->get('/api/auth/github/callback');

        // Debería redirigir al frontend con error
        $response->assertStatus(302)
            ->assertRedirect();

        $redirectUrl = $response->headers->get('Location');
        $this->assertStringContainsString('http://localhost:5173/auth/callback', $redirectUrl);
        $this->assertStringContainsString('success=false', $redirectUrl);
        $this->assertStringContainsString('error=Error+de+autenticaci%C3%B3n', $redirectUrl);
    }

    /**
     * Test que verifica que el frontend_url se configura correctamente
     */
    public function test_uses_correct_frontend_url()
    {
        // Configurar un frontend_url personalizado para el test
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