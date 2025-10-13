<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    /**
     * Setup runs before each test
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        
        $this->seed([
            \Database\Seeders\RoleSeeder::class,
            \Database\Seeders\PermissionSeeder::class,
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\TagSeeder::class, // Tags needed for many tests
        ]);
    }

    /**
     * Authenticate a user with a specific role using SESSION (not Passport)
     * 
     * @param string $role Role name (student, mentor, admin, superadmin)
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
     * Authenticate existing user with SESSION
     * 
     * @param User|null $user User to authenticate (creates new if null)
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
