<?php
declare(strict_types=1);

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthGuardTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_guard_uses_session_driver(): void
    {
        $guardDriver = config('auth.guards.api.driver');
        
        $this->assertEquals('session', $guardDriver, 'API guard should use session driver');
    }

    public function test_api_guard_provider_is_users(): void
    {
        $provider = config('auth.guards.api.provider');
        
        $this->assertEquals('users', $provider, 'API guard should use users provider');
    }

    public function test_user_provider_uses_eloquent_driver(): void
    {
        $providerDriver = config('auth.providers.users.driver');
        
        $this->assertEquals('eloquent', $providerDriver, 'Users provider should use eloquent driver');
    }

    public function test_user_model_is_configured_correctly(): void
    {
        $model = config('auth.providers.users.model');
        
        $this->assertEquals(\App\Models\User::class, $model, 'Users provider should use App\Models\User');
    }

    public function test_web_guard_also_uses_session_driver(): void
    {
        $webGuardDriver = config('auth.guards.web.driver');
        
        $this->assertEquals('session', $webGuardDriver, 'Web guard should use session driver');
    }
}