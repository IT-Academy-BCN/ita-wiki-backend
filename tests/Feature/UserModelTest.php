<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    public function testUserCanBeCreatedWithGithubId(): void
    {
        $user = User::factory()->create([
            'github_id' => '123456789'
        ]);

        $this->assertDatabaseHas('users', [
            'github_id' => '123456789',
            'email' => $user->email
        ]);
    }

    public function testGithubIdIsAccessible(): void
    {
        $githubId = '987654321';
        $user = User::factory()->create(['github_id' => $githubId]);

        $this->assertEquals($githubId, $user->github_id);
    }

    public function testGithubNameIsAccessible(): void
    {
        $githubUserName = 'Cristina';
        $user = User::factory()->create(['github_user_name' =>$githubUserName]);

        $this->assertEquals($githubUserName, $user->github_user_name);
    }

    public function testPasswordFieldHasBeenRemovedFromUsersTable(): void
    {
        // Verificar que no se puede acceder al campo password en el modelo
        $user = User::factory()->create();
        
        // El campo password no debería existir en los atributos del modelo
        $this->assertArrayNotHasKey('password', $user->getAttributes());
        
        // Verificar que el campo password no está en los fillable
        $this->assertNotContains('password', $user->getFillable());
        
        // Verificar que el campo password no está en los hidden
        $this->assertNotContains('password', $user->getHidden());
        
        // Verificar que la estructura de la tabla no incluye password
        $columns = \Schema::getColumnListing('users');
        $this->assertNotContains('password', $columns);
    }

    public function testCannotCreateUserWithPasswordField(): void
    {
        // Intentar crear un usuario con password debería fallar o ignorar el campo
        $userData = [
            'name' => 'Test User',
            'email' => 'test' . uniqid() . '@example.com',
            'github_id' => 123456789,
            'github_user_name' => 'testuser' . uniqid(),
            'password' => 'secretpassword' // Este campo debería ser ignorado
        ];

        $user = User::create($userData);
        
        // Verificar que el usuario se creó pero sin el campo password
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Test User',
            'email' => $userData['email'],
            'github_id' => 123456789,
            'github_user_name' => $userData['github_user_name']
        ]);
        
        // Verificar que no hay campo password en la base de datos
        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
            'password' => 'secretpassword'
        ]);
    }


}