<?php
declare(strict_types=1);

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserGuardTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_has_api_guard_name(): void
    {
        $user = User::factory()->create();
        
        $this->assertEquals('api', $user->getGuardName(), 'User guard_name should be api');
    }

    public function test_user_getDefaultGuardName_returns_api(): void
    {
        $user = User::factory()->create();
        
        $reflection = new \ReflectionClass($user);
        $method = $reflection->getMethod('getGuardName');
        $method->setAccessible(true);
        
        $guardName = $method->invoke($user);
        
        $this->assertEquals('api', $guardName, 'getGuardName() should return api');
    }

    public function test_user_can_be_found_by_github_id(): void
    {
        $user = User::factory()->create([
            'github_id' => '12345',
        ]);
        
        $foundUser = User::findByGithubId(12345);
        
        $this->assertNotNull($foundUser, 'User should be found by github_id');
        $this->assertEquals($user->id, $foundUser->id);
        $this->assertEquals('12345', $foundUser->github_id);
    }

    public function test_user_findByGithubId_returns_null_when_not_found(): void
    {
        $foundUser = User::findByGithubId(99999);
        
        $this->assertNull($foundUser, 'findByGithubId should return null when user not found');
    }

    public function test_user_getRoleName_returns_anonymous_when_no_role(): void
    {
        $user = User::factory()->create();
        
        $roleName = $user->getRoleName();
        
        $this->assertEquals('anonymous', $roleName, 'getRoleName should return anonymous when no role assigned');
    }

   
}