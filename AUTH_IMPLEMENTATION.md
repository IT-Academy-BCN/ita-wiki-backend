# Documentación: Sistema de Autenticación GitHub OAuth + Laravel Sanctum

## 📋 Índice
1. [Resumen del Flujo de Autenticación](#resumen-del-flujo-de-autenticación)
2. [Cambios Aplicados](#cambios-aplicados)
3. [Configuración Necesaria](#configuración-necesaria)
4. [Pasos Realizados](#pasos-realizados)
5. [Tareas Pendientes](#tareas-pendientes)
6. [Testing del Sistema](#testing-del-sistema)

---

## 🔐 Resumen del Flujo de Autenticación

### Diagrama del Flujo
```
Frontend (React/Vue)
    ↓
1. GET /api/auth/github/redirect
    ↓
GitHubAuthController::redirect()
    ↓ (genera URL OAuth)
    ↓
2. Usuario redirigido a GitHub.com
    ↓ (usuario autoriza la app)
    ↓
3. GitHub redirige a /api/auth/github/callback?code=xxx
    ↓
GitHubAuthController::callback()
    ↓ (intercambia code por access_token)
    ↓ (obtiene datos del usuario de GitHub)
    ↓ (busca/crea usuario en DB)
    ↓ (genera token Sanctum)
    ↓
4. Redirige a frontend con token: /auth/callback?token=xxx
    ↓
Frontend guarda token en localStorage/cookie
    ↓
5. Solicitudes protegidas con header: Authorization: Bearer {token}
    ↓
GET /api/auth/me (obtener usuario autenticado)
GET /api/auth/github/user (datos de usuario)
POST /api/auth/logout (eliminar token)
```

### Endpoints del Sistema

#### 🌐 Públicos (Sin Autenticación)
- `GET /api/auth/github/redirect` - Genera URL de OAuth para GitHub
- `GET /api/auth/github/callback` - Procesa la respuesta de GitHub y genera token

#### 🔒 Protegidos (Requieren Token Sanctum)
- `GET /api/auth/me` - Retorna datos del usuario autenticado
- `GET /api/auth/github/user` - Retorna datos del usuario autenticado (controlador)
- `POST /api/auth/logout` - Elimina el token actual

---

## ✅ Cambios Aplicados

### 1. **GitHubAuthController** (`app/Http/Controllers/GitHubAuthController.php`)

#### ✨ Método `redirect()`
**Propósito:** Generar la URL de OAuth de GitHub para autenticación.

```php
public function redirect()
{
    try {
        $redirectUrl = Socialite::driver('github')
            ->stateless()
            ->redirect()
            ->getTargetUrl();
            
        return response()->json([
            'success' => true,
            'redirect_url' => $redirectUrl,
            'message' => 'Redirigiendo a GitHub para autenticación'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error al generar URL de redirección: ' . $e->getMessage()
        ], 500);
    }
}
```

**Características:**
- Usa `stateless()` para APIs sin sesiones
- Retorna URL en JSON para que el frontend redirija manualmente
- Manejo de errores con try-catch

---

#### ✨ Método `callback()`
**Propósito:** Procesar el código de GitHub, crear/actualizar usuario y generar token Sanctum.

```php
public function callback(Request $request)
{
    try {
        // 1. Obtener datos del usuario desde GitHub
        $githubUser = Socialite::driver('github')->stateless()->user();
        
        // 2. Buscar usuario existente por github_id
        $user = User::where('github_id', $githubUser->getId())->first();
        
        // 3. Crear nuevo usuario si no existe
        if (!$user) {
            $user = User::create([
                'github_id' => $githubUser->getId(),
                'github_user_name' => $githubUser->getNickname(),
                'name' => $githubUser->getName() ?: $githubUser->getNickname(),
                'email' => $githubUser->getEmail() ?? $githubUser->getNickname() . '@github.local',
                'password' => Hash::make(Str::random(32)),
            ]);
        } else {
            // 4. Actualizar datos del usuario existente
            $user->update([
                'github_user_name' => $githubUser->getNickname(),
                'name' => $githubUser->getName() ?: $githubUser->getNickname(),
                'email' => $githubUser->getEmail() ?? $user->email,
            ]);
        }

        // 5. Generar token Sanctum
        $token = $user->createToken('auth_token')->plainTextToken;

        // 6. Redirigir al frontend con token en query string
        $frontendUrl = config('app.frontend_url', 'http://localhost:5173');
        $redirectUrl = $frontendUrl . '/auth/callback?token=' . urlencode($token);

        return redirect($redirectUrl);

    } catch (\Exception $e) {
        // Manejo de errores: redirigir al frontend con error
        $frontendUrl = config('app.frontend_url', 'http://localhost:5173');
        $redirectUrl = $frontendUrl . '/auth/callback?' . http_build_query([
            'success' => 'false',
            'error' => $e->getMessage(),
        ]);

        return redirect($redirectUrl);
    }
}
```

**Mejoras Aplicadas:**
- ✅ Flujo completo de OAuth con Socialite
- ✅ Generación automática de token Sanctum
- ✅ Redireccionamiento al frontend con token
- ✅ Manejo de usuarios nuevos y existentes
- ✅ Email fallback para usuarios sin email público en GitHub

---

#### ✨ Método `user()` - **CORREGIDO**
**Propósito:** Retornar datos del usuario autenticado.

**❌ Versión Anterior (Vulnerable):**
```php
public function user(Request $request)
{
    $githubId = $request->input('github_id'); // ⚠️ Parámetro vulnerable
    $user = User::where('github_id', $githubId)->first(); // ⚠️ Acceso sin auth
    
    if (!$user) {
        return response()->json([
            'success' => false,
            'message' => 'Usuario no encontrado'
        ], 404);
    }
    
    return response()->json([
        'success' => true,
        'user' => [
            'id' => $request->user()->id(), // ⚠️ Acceso incorrecto
            // ...
        ]
    ]);
}
```

**Problemas:**
- Acepta `github_id` como parámetro sin verificación
- Permite acceso a datos de cualquier usuario
- No requiere autenticación
- Método `id()` incorrecto (es propiedad, no método)

**✅ Versión Corregida:**
```php
public function user(Request $request)
{
    return response()->json([
        'success' => true,
        'user' => [
            'id' => $request->user()->id, // ✅ Propiedad directa
            'github_id' => $request->user()->github_id,
            'github_user_name' => $request->user()->github_user_name,
            'name' => $request->user()->name,
            'email' => $request->user()->email,               
        ]
    ]);
}
```

**Mejoras:**
- ✅ Solo retorna datos del usuario autenticado vía `$request->user()`
- ✅ Protegido por middleware `auth:sanctum` en rutas
- ✅ Acceso correcto a propiedades (sin paréntesis)
- ✅ Sin parámetros vulnerables

---

### 2. **User Model** (`app/Models/User.php`)

#### ✅ Trait `HasApiTokens` Verificado

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Sanctum\HasApiTokens; // ✅ Importado correctamente

class User extends Authenticatable
{
    use HasFactory, HasApiTokens, Notifiable, HasRoles; // ✅ Trait aplicado

    protected string $guard_name = 'api';

    protected $fillable = [
        'github_id',        // ✅ Campo de GitHub
        'github_user_name', // ✅ Campo de GitHub
        'name',
        'email',
        'password',
    ];
    
    // ... resto del modelo
}
```

**Funcionalidad del Trait `HasApiTokens`:**
- ✅ Método `createToken(string $name)` - Generar tokens Sanctum
- ✅ Relación `tokens()` - Acceder a tokens del usuario
- ✅ Método `currentAccessToken()` - Token actual de la petición
- ✅ Método `withAccessToken()` - Asignar token a la instancia

**Campos Específicos de GitHub:**
- `github_id` - ID único del usuario en GitHub (clave primaria OAuth)
- `github_user_name` - Nickname del usuario en GitHub

---

### 3. **Rutas API** (`routes/api.php`)

#### ❌ Versión Anterior (Duplicada)
```php
// Rutas duplicadas en diferentes grupos
Route::prefix('auth/github')->group(function() {
    Route::get('/redirect', [GitHubAuthController::class, 'redirect']);
    Route::get('/callback', [GitHubAuthController::class, 'callback']);
    Route::get('/user', [GitHubAuthController::class, 'user']); // ⚠️ Sin protección
    Route::get('/getSessionUser', [GitHubAuthController::class, 'getSessionUser']); // ⚠️ Obsoleto
});

// Duplicación de rutas
Route::get('/auth/github/redirect', [GitHubAuthController::class, 'redirect']);
Route::get('/auth/github/callback', [GitHubAuthController::class, 'callback']);
Route::get('/auth/github/user', [GitHubAuthController::class, 'user']); // ⚠️ Sin protección
```

#### ✅ Versión Corregida (Organizada)
```php
// GitHub Auth System Endpoints (PUBLIC)
Route::get('/auth/github/redirect', [GitHubAuthController::class, 'redirect'])
    ->name('github.redirect');
Route::get('/auth/github/callback', [GitHubAuthController::class, 'callback'])
    ->name('github.callback');

// Protected Auth Endpoints (Require Sanctum Token)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/auth/me', function (Request $request) {
        return response()->json([
            'success' => true,
            'user' => $request->user()->only(['id', 'github_id', 'github_user_name', 'name', 'email'])
        ]);
    });

    Route::get('/auth/github/user', [GitHubAuthController::class, 'user'])
        ->name('github.user');

    Route::post('/auth/logout', function(Request $request){
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            'success' => true,
            'message' => 'Sesión cerrada correctamente'
        ]);
    });
});
```

**Mejoras:**
- ✅ Separación clara entre rutas públicas y protegidas
- ✅ Middleware `auth:sanctum` en rutas sensibles
- ✅ Eliminación de rutas duplicadas
- ✅ Eliminación de `getSessionUser` (obsoleto)
- ✅ Nombres de ruta claros y consistentes

---

## ⚙️ Configuración Necesaria

### 1. **Archivo `.env`**

#### Variables de Base de Datos
```dotenv
DB_CONNECTION=mysql
DB_HOST=mysql              # Nombre del servicio Docker
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=user
DB_PASSWORD=password
```

#### Variables de GitHub OAuth
```dotenv
# Desarrollo Local
GITHUB_CLIENT_ID_LOCAL=your_github_client_id_here
GITHUB_CLIENT_SECRET_LOCAL=your_github_client_secret_here
GITHUB_REDIRECT_URI_LOCAL=http://localhost:8000/api/auth/github/callback

# Producción
GITHUB_CLIENT_ID_PROD=your_prod_github_client_id
GITHUB_CLIENT_SECRET_PROD=your_prod_github_client_secret
GITHUB_REDIRECT_URI_PROD=https://your-domain.com/api/auth/github/callback
```

#### Variable de Frontend
```dotenv
FRONTEND_URL=http://localhost:5173
```

#### Entorno de la Aplicación
```dotenv
APP_ENV=local              # Cambia a 'production' en producción
APP_URL=http://localhost:8000
```

---

### 2. **Archivo `config/services.php`**

```php
<?php

$isProd = env('APP_ENV') === 'production';

return [
    'github' => [
        'client_id' => env($isProd ? 'GITHUB_CLIENT_ID_PROD' : 'GITHUB_CLIENT_ID_LOCAL'),
        'client_secret' => env($isProd ? 'GITHUB_CLIENT_SECRET_PROD' : 'GITHUB_CLIENT_SECRET_LOCAL'),
        'redirect' => env($isProd ? 'GITHUB_REDIRECT_URI_PROD' : 'GITHUB_REDIRECT_URI_LOCAL'),
    ],
    // ... otros servicios
];
```

**Funcionamiento:**
- ✅ Selección automática de credenciales según `APP_ENV`
- ✅ Desarrollo (`local`) usa variables `_LOCAL`
- ✅ Producción (`production`) usa variables `_PROD`

---

### 3. **Obtener Credenciales de GitHub OAuth**

1. Ir a: https://github.com/settings/developers
2. Click en **"New OAuth App"**
3. Completar formulario:
   - **Application name:** ITA Wiki (Local)
   - **Homepage URL:** `http://localhost:8000`
   - **Authorization callback URL:** `http://localhost:8000/api/auth/github/callback`
4. Click en **"Register application"**
5. Copiar **Client ID** y generar **Client Secret**
6. Añadir al `.env`:
   ```dotenv
   GITHUB_CLIENT_ID_LOCAL=tu_client_id
   GITHUB_CLIENT_SECRET_LOCAL=tu_client_secret
   ```

---

## 🚀 Pasos Realizados

### ✅ 1. Instalación de Laravel Socialite
```bash
composer require laravel/socialite
```

**Verificación:**
```bash
composer show laravel/socialite
# Salida: laravel/socialite v5.23.1
```

---

### ✅ 2. Configuración de GitHub OAuth

**Archivo:** `config/services.php`
- ✅ Configuración de credenciales
- ✅ Diferenciación entre local y producción

**Archivo:** `.env.example`
- ✅ Documentación de variables necesarias
- ✅ Ejemplos de URLs de callback

---

### ✅ 3. Implementación del Controlador

**Archivo:** `app/Http/Controllers/GitHubAuthController.php`
- ✅ Método `redirect()` - Generación de URL OAuth
- ✅ Método `callback()` - Procesamiento de respuesta y generación de token
- ✅ Método `user()` - Obtención de usuario autenticado (CORREGIDO)

---

### ✅ 4. Configuración de Rutas API

**Archivo:** `routes/api.php`
- ✅ Rutas públicas para OAuth (`/auth/github/redirect`, `/auth/github/callback`)
- ✅ Rutas protegidas con `auth:sanctum` (`/auth/me`, `/auth/github/user`, `/auth/logout`)
- ✅ Eliminación de duplicados y rutas obsoletas

---

### ✅ 5. Verificación del Modelo User

**Archivo:** `app/Models/User.php`
- ✅ Trait `HasApiTokens` importado y aplicado
- ✅ Campos `github_id` y `github_user_name` en `$fillable`
- ✅ Guard `api` configurado para Spatie Permissions

---

### ✅ 6. Migración de Base de Datos

**Archivo:** `database/migrations/0001_01_01_000000_create_users_table.php`

Campos de la tabla `users`:
```php
$table->id();
$table->string('github_id')->unique();      // ✅ ID de GitHub (único)
$table->string('github_user_name');         // ✅ Username de GitHub
$table->string('name');
$table->string('email')->unique();
$table->string('password');
$table->rememberToken();
$table->timestamps();
```

**Tabla `personal_access_tokens` (Sanctum):**
- Creada automáticamente por Sanctum
- Almacena tokens API
- Relación polimórfica con `users`

---

### ✅ 7. Factory de Usuario

**Archivo:** `database/factories/UserFactory.php`

```php
public function definition(): array
{
    return [
        'github_id' => fake()->unique()->numberBetween(10000, 99999),
        'github_user_name' => fake()->unique()->userName(),
        'name' => fake()->name(),
        'email' => fake()->unique()->safeEmail(),
        'email_verified_at' => now(),
        'password' => static::$password ??= Hash::make('password'),
        'remember_token' => Str::random(10),
    ];
}
```

**Características:**
- ✅ `github_id` único (10000-99999)
- ✅ `github_user_name` único con username fake
- ✅ Email y password por defecto
- ✅ Método `unverified()` para testing

**⚠️ Nota:** Hay duplicación de `github_id` y `github_user_name` en el array (líneas 28 y 35-36). Se debe eliminar la primera ocurrencia.

---

### ✅ 8. Seeder de Usuario de Prueba

**Archivo:** `database/seeders/UserSeeder.php`

```php
public function run(): void
{
    // 1. Crear usuario de prueba con Factory
    $testUser = User::factory()->create([
        'github_id' => '12345678',
        'github_user_name' => 'test_user',
    ]);

    // 2. Generar token Sanctum
    $token = $testUser->createToken('Personal Access Token')->plainTextToken;

    // 3. Mostrar información en consola
    $this->command->info('User test created successfully');
    $this->command->info('Github ID: ' . $testUser->github_id);
    $this->command->info('GitHub Username: ' . $testUser->github_user_name);
    $this->command->info('Personal Access Token: ' . $token);
}
```

**Corrección Aplicada:**
- ❌ **Antes:** `$testUser->github->id` (error: propiedad `github` no existe)
- ✅ **Ahora:** `$testUser->github_id` (acceso directo a propiedad)

**Uso:**
```bash
php artisan db:seed --class=UserSeeder
```

**Salida Esperada:**
```
User test created successfully
Github ID: 12345678
GitHub Username: test_user
Personal Access Token: 1|aBcDeFgHiJkLmNoPqRsTuVwXyZ...
```

---

## 🔧 Tareas Pendientes

### 🐳 1. **Instalación de Sanctum en Docker** (CRÍTICO)

#### Problema Actual
El contenedor Docker **no tiene** las dependencias de Sanctum instaladas, causando:
```
Error: Class 'Laravel\Sanctum\Sanctum' not found
```

#### Solución

**Opción A: Ejecutar composer install en el contenedor**
```bash
# 1. Iniciar el contenedor app
cd docker
docker-compose up -d app

# 2. Instalar dependencias dentro del contenedor
docker exec -it app composer install

# 3. Publicar configuración de Sanctum
docker exec -it app php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"

# 4. Ejecutar migraciones
docker exec -it app php artisan migrate
```

**Opción B: Rebuild completo del contenedor**
```bash
cd docker
docker-compose down
docker-compose build --no-cache app
docker-compose up -d
```

---

### 🗄️ 2. **Ejecutar `migrate:fresh` en Docker** (NECESARIO)

#### ¿Por Qué es Necesario?

1. **Nuevos Campos en `users`:**
   - `github_id` (único, requerido)
   - `github_user_name` (requerido)

2. **Tabla `personal_access_tokens`:**
   - Necesaria para almacenar tokens Sanctum
   - Creada por la migración de Sanctum

3. **Cambios en Estructura:**
   - Si existen usuarios anteriores sin `github_id`, la base de datos quedará inconsistente

#### Comandos

```bash
# Opción 1: Fresh (elimina todas las tablas y recrea)
docker exec -it app php artisan migrate:fresh

# Opción 2: Fresh + Seeder
docker exec -it app php artisan migrate:fresh --seed

# Opción 3: Fresh + Seeder específico
docker exec -it app php artisan migrate:fresh
docker exec -it app php artisan db:seed --class=UserSeeder
```

**⚠️ ADVERTENCIA:** 
- `migrate:fresh` **ELIMINA TODOS LOS DATOS** de la base de datos
- Solo usar en desarrollo, **NUNCA en producción**
- En producción usar `php artisan migrate` para cambios incrementales

---

### 🧪 3. **Validar Usuario del Seeder**

#### Verificación en Base de Datos

**Opción A: PHPMyAdmin**
1. Ir a: http://localhost:8080
2. Login: `user` / `password`
3. Seleccionar base de datos `laravel`
4. Tabla `users` → Buscar `github_id = '12345678'`

**Opción B: MySQL CLI**
```bash
docker exec -it mysql mysql -u user -ppassword laravel

# Dentro de MySQL
SELECT id, github_id, github_user_name, name, email 
FROM users 
WHERE github_id = '12345678';
```

**Salida Esperada:**
```
+----+-----------+-----------------+-----------+-------------------------+
| id | github_id | github_user_name| name      | email                   |
+----+-----------+-----------------+-----------+-------------------------+
|  1 | 12345678  | test_user       | Test User | test@example.com        |
+----+-----------+-----------------+-----------+-------------------------+
```

---

### 🧪 4. **Validar Tokens en `personal_access_tokens`**

```sql
SELECT id, tokenable_type, tokenable_id, name, abilities, created_at
FROM personal_access_tokens
WHERE tokenable_id = 1;
```

**Salida Esperada:**
```
+----+------------------+--------------+------------------------+-------------+---------------------+
| id | tokenable_type   | tokenable_id | name                   | abilities   | created_at          |
+----+------------------+--------------+------------------------+-------------+---------------------+
|  1 | App\Models\User  |            1 | Personal Access Token  | ["*"]       | 2025-11-26 10:30:00 |
+----+------------------+--------------+------------------------+-------------+---------------------+
```

---

### 🔍 5. **Validar Factory - Eliminar Duplicación**

**Archivo:** `database/factories/UserFactory.php`

**❌ Problema Actual:**
```php
public function definition(): array
{
    return [
        'github_id' => fake()->numberBetween(1, 999999999),        // Línea 28 ⚠️
        'github_user_name' => fake()->name(),                      // Línea 29 ⚠️
        'email' => fake()->unique()->safeEmail(),
        'email_verified_at' => now(),
        'password' => static::$password ??= Hash::make('password'),
        'remember_token' => Str::random(10),
        'github_id' => fake()->unique()->numberBetween(10000, 99999),  // Línea 35 ✅ (duplicado)
        'github_user_name' => fake()->unique()->userName(),             // Línea 36 ✅ (duplicado)
    ];
}
```

**✅ Corrección Necesaria:**
```php
public function definition(): array
{
    return [
        'github_id' => fake()->unique()->numberBetween(10000, 99999),
        'github_user_name' => fake()->unique()->userName(),
        'name' => fake()->name(),
        'email' => fake()->unique()->safeEmail(),
        'email_verified_at' => now(),
        'password' => static::$password ??= Hash::make('password'),
        'remember_token' => Str::random(10),
    ];
}
```

---

## 🧪 Testing del Sistema

### 1. **Ejecutar el Seeder y Obtener Token**

```bash
docker exec -it app php artisan db:seed --class=UserSeeder
```

**Copiar el token de la salida:**
```
Personal Access Token: 1|aBcDeFgHiJkLmNoPqRsTuVwXyZ...
```

---

### 2. **Test 1: Endpoint `/auth/me` (Usuario Autenticado)**

```bash
# Windows PowerShell
$token = "1|aBcDeFgHiJkLmNoPqRsTuVwXyZ..."
curl -H "Authorization: Bearer $token" http://localhost:8000/api/auth/me
```

**Respuesta Esperada:**
```json
{
    "success": true,
    "user": {
        "id": 1,
        "github_id": "12345678",
        "github_user_name": "test_user",
        "name": "Test User",
        "email": "test@example.com"
    }
}
```

---

### 3. **Test 2: Endpoint `/auth/github/user`**

```bash
curl -H "Authorization: Bearer $token" http://localhost:8000/api/auth/github/user
```

**Respuesta Esperada:**
```json
{
    "success": true,
    "user": {
        "id": 1,
        "github_id": "12345678",
        "github_user_name": "test_user",
        "name": "Test User",
        "email": "test@example.com"
    }
}
```

---

### 4. **Test 3: Endpoint `/auth/github/redirect` (OAuth URL)**

```bash
curl http://localhost:8000/api/auth/github/redirect
```

**Respuesta Esperada:**
```json
{
    "success": true,
    "redirect_url": "https://github.com/login/oauth/authorize?client_id=xxx&redirect_uri=http%3A%2F%2Flocalhost%3A8000%2Fapi%2Fauth%2Fgithub%2Fcallback&scope=&response_type=code&state=xxx",
    "message": "Redirigiendo a GitHub para autenticación"
}
```

---

### 5. **Test 4: Flujo Completo de OAuth**

#### Paso 1: Frontend obtiene URL de OAuth
```javascript
// React/Vue
const response = await fetch('http://localhost:8000/api/auth/github/redirect');
const data = await response.json();

// Redirigir al usuario
window.location.href = data.redirect_url;
```

#### Paso 2: Usuario autoriza en GitHub
- GitHub redirige a: `http://localhost:8000/api/auth/github/callback?code=xxx`

#### Paso 3: Backend procesa callback
- Usuario creado/actualizado en DB
- Token Sanctum generado
- Redirige a: `http://localhost:5173/auth/callback?token=xxx`

#### Paso 4: Frontend captura token
```javascript
// En la ruta /auth/callback del frontend
const urlParams = new URLSearchParams(window.location.search);
const token = urlParams.get('token');

if (token) {
    // Guardar token
    localStorage.setItem('auth_token', token);
    
    // Obtener usuario
    const userResponse = await fetch('http://localhost:8000/api/auth/me', {
        headers: {
            'Authorization': `Bearer ${token}`
        }
    });
    
    const userData = await userResponse.json();
    console.log('Usuario autenticado:', userData.user);
}
```

---

### 6. **Test 5: Logout (Eliminar Token)**

```bash
curl -X POST -H "Authorization: Bearer $token" http://localhost:8000/api/auth/logout
```

**Respuesta Esperada:**
```json
{
    "success": true,
    "message": "Sesión cerrada correctamente"
}
```

**Verificación:**
```bash
# Intentar acceder a /auth/me con el token eliminado
curl -H "Authorization: Bearer $token" http://localhost:8000/api/auth/me
```

**Respuesta Esperada (Token Inválido):**
```json
{
    "message": "Unauthenticated."
}
```

---

## 📊 Resumen de Estado

### ✅ Completado
- [x] Instalación de Laravel Socialite
- [x] Configuración de GitHub OAuth en `config/services.php`
- [x] Implementación de `GitHubAuthController` (redirect, callback, user)
- [x] Corrección del método `user()` vulnerable
- [x] Verificación de `HasApiTokens` en User model
- [x] Organización de rutas API (eliminación de duplicados)
- [x] Corrección de `UserSeeder` (`github_id` directo)
- [x] Documentación en `.env.example`

### ⚠️ Pendiente (Antes de Testing)
- [ ] Iniciar contenedor Docker `app`
- [ ] Ejecutar `composer install` en Docker
- [ ] Publicar configuración de Sanctum
- [ ] Ejecutar `migrate:fresh` en Docker
- [ ] Ejecutar `UserSeeder` y obtener token
- [ ] Corregir duplicación en `UserFactory.php`
- [ ] Configurar variables en `.env` (GitHub credentials)

### 🎯 Listo para Producción
- [ ] Obtener credenciales de GitHub OAuth para producción
- [ ] Configurar `GITHUB_*_PROD` en `.env` de producción
- [ ] Configurar `FRONTEND_URL` de producción
- [ ] Usar `migrate` en vez de `migrate:fresh`
- [ ] Implementar rate limiting en rutas OAuth
- [ ] Configurar CORS para dominio de producción

---

## 🔗 Referencias

- **Laravel Socialite:** https://laravel.com/docs/11.x/socialite
- **Laravel Sanctum:** https://laravel.com/docs/11.x/sanctum
- **GitHub OAuth Apps:** https://docs.github.com/en/apps/oauth-apps
- **Spatie Laravel Permissions:** https://spatie.be/docs/laravel-permission

---

## 📝 Notas Finales

### Seguridad
- ✅ Tokens Sanctum almacenados hasheados en DB
- ✅ Middleware `auth:sanctum` protege rutas sensibles
- ✅ Stateless OAuth (sin sesiones)
- ✅ Passwords de usuarios OAuth son random (no reutilizables)

### Performance
- ✅ Índice único en `github_id` para búsquedas rápidas
- ✅ Socialite usa `stateless()` para evitar sobrecarga de sesiones

### Mantenimiento
- ✅ Separación clara de credenciales local/producción
- ✅ Código documentado y estructurado
- ✅ Factory y Seeder preparados para testing

---

**Fecha de Documentación:** 26 de Noviembre de 2025  
**Versión de Laravel:** 11.31  
**Versión de Socialite:** 5.23.1  
**Versión de Sanctum:** Instalado (pendiente verificación en Docker)
