<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test para crear un usuario con un GitHub ID específico.
     * Verifica que el usuario se guarda correctamente en la base de datos
     * con el GitHub ID proporcionado.
     */
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

    /**
     * Test para comprobar que el GitHub ID del usuario es accesible después de la creación.
     * Verifica que podemos recuperar el valor del campo github_id
     * del modelo User correctamente.
     */
    public function testGithubIdIsAccessible(): void
    {
        $githubId = '987654321';
        $user = User::factory()->create(['github_id' => $githubId]);

        $this->assertEquals($githubId, $user->github_id);
    }

    /**
     * Test para comprobar que el nombre de usuario de GitHub es accesible después de la creación.
     * Verifica que podemos recuperar el valor del campo github_user_name
     * del modelo User correctamente.
     */
    public function testGithubNameIsAccessible(): void
    {
        $githubUserName = 'Cristina';
        $user = User::factory()->create(['github_user_name' =>$githubUserName]);

        $this->assertEquals($githubUserName, $user->github_user_name);
    }

    /**
     * test para confirmar que el campo password ha sido completamente eliminado de la tabla users.
     * Verifica múltiples aspectos:
     * - El campo no existe en los atributos del modelo
     * - No está en los campos fillable (no se puede asignar en masa)
     * - No está en los campos hidden (no se oculta en serialización)
     * - No existe como columna en la estructura de la tabla de la base de datos
     */
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
        $columns = Schema::getColumnListing('users');
        $this->assertNotContains('password', $columns);
    }

}