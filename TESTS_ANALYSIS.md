# 🧪 Análisis de Tests: Compatibilidad con Sanctum + UserSeeder

## 📋 RESUMEN EJECUTIVO

### ✅ Tests que PASAN sin cambios:
- `UserModelTest` - Tests básicos de User factory
- `GitHubOAuthTest` (parcial) - Tests de OAuth flow

### ❌ Tests que FALLAN y necesitan corrección:
- `GitHubOAuthTest::test_can_get_user_by_github_id` - Endpoint eliminado
- `GitHubOAuthTest::test_returns_error_when_user_not_found` - Endpoint eliminado
- Tests que usan `callback()` - Cambio en estructura de respuesta

### ⚠️ Tests que necesitan ADAPTACIÓN:
- Tests de recursos protegidos - Migrar de `actingAs()` a `Sanctum::actingAs()`
- Tests de autenticación - Actualizar para Sanctum

---

## 🔍 ANÁLISIS DETALLADO

### **1. UserModelTest.php** ✅ PASA SIN CAMBIOS

**Archivo:** `tests/Feature/UserTests/UserModelTest.php`

```php
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
```

**Estado:** ✅ **COMPATIBLE**

**Razón:**
- UserFactory genera correctamente `github_id` (10000000-99999999)
- UserSeeder usa el mismo patrón
- No requiere cambios

---

### **2. GitHubOAuthTest.php** ⚠️ REQUIERE CORRECCIONES

#### **Tests que FALLAN:**

##### ❌ **Test 1: `test_can_get_user_by_github_id`**

**Código actual:**
```php
public function test_can_get_user_by_github_id()
{
    $user = User::factory()->create([
        'github_id' => '12345',
        'github_user_name' => 'testuser',
        'name' => 'Test User',
        'email' => 'test_get_' . time() . '@example.com',
    ]);

    $response = $this->get('/api/auth/github/user?github_id=12345');
    // ❌ Endpoint eliminado por vulnerable

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'user' => [
                'github_id' => '12345',
                'github_user_name' => 'testuser',
                'name' => 'Test User',
            ]
        ]);
}
```

**Problema:**
- El endpoint `/api/auth/github/user` ya **NO acepta parámetro `github_id`**
- Ahora requiere autenticación Sanctum
- Retorna datos del usuario autenticado

**Corrección:**
```php
public function test_authenticated_user_can_get_own_data()
{
    $user = User::factory()->create([
        'github_id' => '12345',
        'github_user_name' => 'testuser',
        'name' => 'Test User',
    ]);

    // ✅ Autenticar con Sanctum
    Sanctum::actingAs($user, ['*']);

    // ✅ No enviar github_id (usa token)
    $response = $this->getJson('/api/auth/github/user');

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'github_id' => '12345',
                'github_user_name' => 'testuser',
                'name' => 'Test User',
            ]
        ]);
}
```

---

##### ❌ **Test 2: `test_returns_error_when_user_not_found`**

**Código actual:**
```php
public function test_returns_error_when_user_not_found()
{
    $response = $this->get('/api/auth/github/user?github_id=99999');
    // ❌ Ya no es válido

    $response->assertStatus(404)
        ->assertJson([
            'success' => false,
            'message' => 'Usuario no encontrado'
        ]);
}
```

**Problema:**
- El endpoint ya NO busca por `github_id`
- Ahora si no hay token, retorna 401 Unauthorized

**Corrección:**
```php
public function test_unauthenticated_request_returns_401()
{
    // ✅ Sin token Sanctum
    $response = $this->getJson('/api/auth/github/user');

    $response->assertStatus(401)
        ->assertJson([
            'message' => 'Unauthenticated.'
        ]);
}
```

---

##### ⚠️ **Test 3: `test_can_create_user_from_github_callback`**

**Código actual:**
```php
$response = $this->get('/api/auth/github/callback');

$response->assertStatus(302)
    ->assertRedirect();

$redirectUrl = $response->headers->get('Location');
$this->assertStringContainsString('http://localhost:5173/auth/callback', $redirectUrl);
$this->assertStringContainsString('success=true', $redirectUrl);
$this->assertStringContainsString('github_id=12345', $redirectUrl);
$this->assertStringContainsString('name=Test+User', $redirectUrl);
$this->assertStringContainsString('github_user_name=testuser', $redirectUrl);
```

**Problema:**
- El callback() actual ya **NO envía datos del usuario en query string**
- Solo envía el **token**

**GitHubAuthController actual:**
```php
$token = $user->createToken('auth_token')->plainTextToken;
$frontendUrl = config('app.frontend_url', 'http://localhost:5173');
$redirectUrl = $frontendUrl . '/auth/callback?token=' . urlencode($token);
return redirect($redirectUrl);
```

**Corrección:**
```php
public function test_can_create_user_from_github_callback()
{
    $githubUser = new SocialiteUser();
    $githubUser->id = '12345';
    $githubUser->nickname = 'testuser';
    $githubUser->name = 'Test User';
    $githubUser->email = 'test_' . time() . '@example.com';

    Socialite::shouldReceive('driver->stateless->user')
        ->andReturn($githubUser);

    $response = $this->get('/api/auth/github/callback');

    $response->assertStatus(302)
        ->assertRedirect();

    $redirectUrl = $response->headers->get('Location');
    
    // ✅ Verificar que redirige al frontend
    $this->assertStringContainsString('http://localhost:5173/auth/callback', $redirectUrl);
    
    // ✅ Verificar que contiene token (no datos de usuario)
    $this->assertStringContainsString('token=', $redirectUrl);
    
    // ✅ Extraer token de la URL
    parse_str(parse_url($redirectUrl, PHP_URL_QUERY), $params);
    $this->assertNotNull($params['token'] ?? null);
    $this->assertStringStartsWith('1|', $params['token']); // Formato Sanctum

    // ✅ Verificar que el usuario fue creado en DB
    $this->assertDatabaseHas('users', [
        'github_id' => '12345',
        'github_user_name' => 'testuser',
        'name' => 'Test User',
    ]);
    
    // ✅ Verificar que se creó un token en personal_access_tokens
    $user = User::where('github_id', '12345')->first();
    $this->assertDatabaseHas('personal_access_tokens', [
        'tokenable_id' => $user->id,
        'tokenable_type' => 'App\Models\User',
    ]);
}
```

---

### **3. ResourceTests** ⚠️ YA USAN SANCTUM (OK)

**Archivo:** `tests/Feature/ResourceTests/CreateResourceTest.php`

```php
private function authenticateSanctumUser(int $githubId = 123456): User
{
    $user = User::factory()->create(['github_id' => $githubId]);
    Sanctum::actingAs($user, ['*']);
    return $user;
}
```

**Estado:** ✅ **COMPATIBLE** - Ya usan Sanctum correctamente

---

### **4. TestCase.php** ⚠️ DUAL: Session + Sanctum

**Archivo:** `tests/TestCase.php`

```php
protected function authenticateUserWithRole(string $role): User
{
    $user = User::factory()->create();
    $user->assignRole($role);
    $this->actingAs($user, 'api'); // ⚠️ Usa SESSION (guard 'api')
    return $user;
}
```

**Problema:**
- Usa `actingAs($user, 'api')` que es **autenticación por sesión**
- Los nuevos endpoints usan **Sanctum (token)**
- Hay **inconsistencia** entre tests antiguos (session) y nuevos (Sanctum)

**Solución: Agregar método Sanctum en TestCase:**
```php
/**
 * Authenticate user with Sanctum token
 * 
 * @param string $role Role name
 * @return User Authenticated user with token
 */
protected function authenticateUserWithSanctum(string $role = 'student'): User
{
    $user = User::factory()->create();
    $user->assignRole($role);
    
    Sanctum::actingAs($user, ['*']);
    
    return $user;
}
```

---

## 🔧 CORRECCIONES NECESARIAS

### **PASO 1: Actualizar GitHubOAuthTest.php**

````php
<?php
// filepath: tests/Feature/GitHubTests/GitHubOAuthTest.php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Laravel\Sanctum\Sanctum;
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

        Socialite::shouldReceive('driver->stateless->user')
            ->andReturn($githubUser);

        $response = $this->get('/api/auth/github/callback');

        $response->assertStatus(302)
            ->assertRedirect();

        $redirectUrl = $response->headers->get('Location');
        
        $this->assertStringContainsString('http://localhost:5173/auth/callback', $redirectUrl);
        $this->assertStringContainsString('token=', $redirectUrl);
        
        parse_str(parse_url($redirectUrl, PHP_URL_QUERY), $params);
        $this->assertNotNull($params['token'] ?? null);
        $this->assertStringStartsWith('1|', $params['token']);

        $this->assertDatabaseHas('users', [
            'github_id' => '12345',
            'github_user_name' => 'testuser',
            'name' => 'Test User',
        ]);
        
        $user = User::where('github_id', '12345')->first();
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'tokenable_type' => 'App\Models\User',
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
        $githubUser->email = $existingUser->email;

        Socialite::shouldReceive('driver->stateless->user')
            ->andReturn($githubUser);

        $response = $this->get('/api/auth/github/callback');

        $response->assertStatus(302)
            ->assertRedirect();

        $redirectUrl = $response->headers->get('Location');
        $this->assertStringContainsString('token=', $redirectUrl);

        $this->assertDatabaseHas('users', [
            'id' => $existingUser->id,
            'github_user_name' => 'newusername',
            'name' => 'New Name'
        ]);
    }

    // ✅ NUEVO: Test para endpoint protegido /auth/github/user
    public function test_authenticated_user_can_get_own_data()
    {
        $user = User::factory()->create([
            'github_id' => '12345',
            'github_user_name' => 'testuser',
            'name' => 'Test User',
        ]);

        Sanctum::actingAs($user, ['*']);

        $response = $this->getJson('/api/auth/github/user');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'user' => [
                    'id' => $user->id,
                    'github_id' => '12345',
                    'github_user_name' => 'testuser',
                    'name' => 'Test User',
                ]
            ]);
    }

    // ✅ NUEVO: Test para verificar 401 sin token
    public function test_unauthenticated_request_to_user_endpoint_returns_401()
    {
        $response = $this->getJson('/api/auth/github/user');

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.'
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
````

---

### **PASO 2: Actualizar TestCase.php**

````php
<?php
// filepath: tests/TestCase.php

namespace Tests;

use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        
        $this->seed([
            \Database\Seeders\RoleSeeder::class,
            \Database\Seeders\PermissionSeeder::class,
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\TagSeeder::class,
        ]);
    }

    /**
     * Authenticate a user with a specific role using SESSION (legacy)
     * ⚠️ DEPRECATED: Use authenticateUserWithSanctum() instead
     * 
     * @param string $role Role name
     * @return User Authenticated user
     */
    protected function authenticateUserWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);
        $this->actingAs($user, 'api');
        return $user;
    }

    /**
     * ✅ NEW: Authenticate user with Sanctum token
     * 
     * @param string $role Role name
     * @param array $abilities Token abilities (default: ['*'])
     * @return User Authenticated user
     */
    protected function authenticateUserWithSanctum(string $role = 'student', array $abilities = ['*']): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);
        Sanctum::actingAs($user, $abilities);
        return $user;
    }

    /**
     * Authenticate existing user with SESSION (legacy)
     * ⚠️ DEPRECATED: Use authenticateExistingUserWithSanctum() instead
     * 
     * @param User|null $user User to authenticate
     * @return User Authenticated user
     */
    protected function authenticateUser(User $user = null): User
    {
        $user = $user ?: User::factory()->create();

        if (!$user->roles->count()) {
            $user->assignRole('student');
        }

        $this->actingAs($user, 'api');
        return $user;
    }

    /**
     * ✅ NEW: Authenticate existing user with Sanctum token
     * 
     * @param User|null $user User to authenticate (creates new if null)
     * @param array $abilities Token abilities (default: ['*'])
     * @return User Authenticated user
     */
    protected function authenticateExistingUserWithSanctum(User $user = null, array $abilities = ['*']): User
    {
        $user = $user ?: User::factory()->create();

        if (!$user->roles->count()) {
            $user->assignRole('student');
        }

        Sanctum::actingAs($user, $abilities);
        return $user;
    }

    /**
     * Create a user with a specific role WITHOUT authenticating
     * 
     * @param string $role Role name
     * @return User User instance
     */
    protected function createUserWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);
        return $user;
    }
}
````

---

## 🧪 COMANDO PARA EJECUTAR TESTS

### **Ejecutar todos los tests:**
```bash
docker exec -it app php artisan test
```

### **Ejecutar tests específicos:**
```bash
# Tests de autenticación Sanctum
docker exec -it app php artisan test --filter=SanctumAuthenticationTest

# Tests de User
docker exec -it app php artisan test --filter=UserModelTest

# Tests de GitHub OAuth
docker exec -it app php artisan test --filter=GitHubOAuthTest

# Tests de recursos
docker exec -it app php artisan test --filter=ResourceTest
```

### **Ejecutar con coverage:**
```bash
docker exec -it app php artisan test --coverage
```

---

## 📊 CHECKLIST DE VALIDACIÓN

### **Usuario del Seeder:**
- [ ] UserSeeder crea usuario con `github_id = '12345678'`
- [ ] UserSeeder genera token Sanctum
- [ ] Token aparece en logs al ejecutar `docker-compose up`
- [ ] Usuario existe en tabla `users`
- [ ] Token existe en tabla `personal_access_tokens`

### **Tests básicos:**
- [ ] `UserModelTest::testUserCanBeCreatedWithGithubId` PASA
- [ ] `UserModelTest::testGithubIdIsAccessible` PASA
- [ ] `UserModelTest::testGithubNameIsAccessible` PASA

### **Tests de Sanctum:**
- [ ] `SanctumAuthenticationTest::test_user_can_generate_sanctum_token` PASA
- [ ] `SanctumAuthenticationTest::test_sanctum_token_authenticates_user` PASA
- [ ] `SanctumAuthenticationTest::test_auth_me_endpoint_requires_authentication` PASA

### **Tests de GitHub OAuth (corregidos):**
- [ ] `GitHubOAuthTest::test_can_get_redirect_url` PASA
- [ ] `GitHubOAuthTest::test_can_create_user_from_github_callback` PASA
- [ ] `GitHubOAuthTest::test_authenticated_user_can_get_own_data` PASA
- [ ] `GitHubOAuthTest::test_unauthenticated_request_to_user_endpoint_returns_401` PASA

---

## 🎯 RESUMEN DE CAMBIOS

| Componente | Cambio | Impacto |
|------------|--------|---------|
| **GitHubAuthController** | Endpoint `/auth/github/user` ahora protegido | ALTO - Tests deben usar Sanctum |
| **GitHubAuthController** | `callback()` retorna solo token (no datos) | ALTO - Tests deben verificar token, no datos |
| **UserSeeder** | Genera token automáticamente | BAJO - Solo afecta a testing manual |
| **UserFactory** | Campo `name` ahora usado en seeder | BAJO - Tests compatibles |
| **Tests nuevos** | `SanctumAuthenticationTest` agregado | NUEVO - Valida flujo completo |
| **TestCase** | Métodos Sanctum agregados | BAJO - Métodos legacy siguen funcionando |

---

## ✅ CAMINO SEGURO PARA VALIDAR

### **1. Validación en Docker (sin tests):**
```bash
# Levantar contenedores
docker-compose up -d

# Ver logs del seeder
docker-compose logs -f app | grep "Personal Access Token"

# Copiar el token que aparece
# Ejemplo: 1|T4SOpbyQnfCCmshIIDTmyiaatC3rHG4VxZn9ldewc6c0276f
```

### **2. Probar manualmente con curl (desde PowerShell):**
```powershell
# Guardar token en variable
$token = "1|T4SOpbyQnfCCmshIIDTmyiaatC3rHG4VxZn9ldewc6c0276f"

# Test 1: Endpoint /auth/me
curl -H "Authorization: Bearer $token" http://localhost:8000/api/auth/me

# Test 2: Endpoint /auth/github/user
curl -H "Authorization: Bearer $token" http://localhost:8000/api/auth/github/user

# Test 3: Sin token (debe dar 401)
curl http://localhost:8000/api/auth/me
```

### **3. Ejecutar tests automatizados:**
```bash
# Test 1: Validar UserModelTest (básico)
docker exec -it app php artisan test --filter=UserModelTest

# Test 2: Validar SanctumAuthenticationTest (nuevo)
docker exec -it app php artisan test --filter=SanctumAuthenticationTest

# Test 3: Validar GitHubOAuthTest (corregido)
docker exec -it app php artisan test --filter=GitHubOAuthTest

# Test 4: Todos los tests
docker exec -it app php artisan test
```

### **4. Validar en base de datos:**
```bash
docker exec -it mysql mysql -u user -ppassword laravel -e "
SELECT id, github_id, github_user_name, name, email 
FROM users 
WHERE github_id = '12345678';
"

docker exec -it mysql mysql -u user -ppassword laravel -e "
SELECT id, tokenable_id, tokenable_type, name 
FROM personal_access_tokens 
WHERE tokenable_id = 1;
"
```

---

**Fecha:** 27 de Noviembre de 2025  
**Estado:** Tests básicos compatibles, correcciones aplicadas para GitHubOAuthTest  
**Próximo paso:** Ejecutar tests y validar resultados
