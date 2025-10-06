<?php

namespace Tests;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\TagSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Laravel\Passport\Passport;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // ✅ Seed roles, permissions AND tags
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(TagSeeder::class); 
    }

    /**
     * Authenticate user with specific Spatie role
     */
    protected function authenticateUserWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        Passport::actingAs($user, ['*']);

        return $user;
    }

    /**
     * Authenticate existing user
     */
    protected function authenticateUser(User $user = null): User
    {
        $user = $user ?: User::factory()->create();

        if (!$user->roles->count()) {
            $user->assignRole('student');
        }

        Passport::actingAs($user, ['*']);

        return $user;
    }
}
