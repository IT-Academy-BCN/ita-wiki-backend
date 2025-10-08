<?php
// filepath: c:\xampp\htdocs\Progetto ITA\ita-wiki-backend\tests\Feature\PermissionsTests\SpatieApiGuardTest.php

declare(strict_types=1);

namespace Tests\Feature\Permissions;

use Tests\TestCase;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SpatieApiGuardTest extends TestCase
{
    use RefreshDatabase;

    // ⚠️ THESE TESTS DEPEND ON RolesAndPermissionsSeeder
    // Will pass after seeder is implemented in next PR

    public function test_roles_are_created_with_api_guard(): void
    {
        $this->markTestSkipped('Waiting for RolesAndPermissionsSeeder in next PR');
        
        // RolesAndPermissionsSeeder should create roles with api guard
        $roles = ['student', 'mentor', 'admin', 'superadmin'];
        
        foreach ($roles as $roleName) {
            $role = Role::where('name', $roleName)->first();
            
            $this->assertNotNull($role, "Role {$roleName} should exist");
            $this->assertEquals('api', $role->guard_name, "Role {$roleName} should have api guard");
        }
    }

    public function test_permissions_are_created_with_api_guard(): void
    {
        $this->markTestSkipped('Waiting for RolesAndPermissionsSeeder in next PR');
        
        $permissions = [
            'view resources',
            'create resources',
            'edit own resources',
            'view technical tests',
        ];
        
        foreach ($permissions as $permissionName) {
            $permission = Permission::where('name', $permissionName)->first();
            
            $this->assertNotNull($permission, "Permission {$permissionName} should exist");
            $this->assertEquals('api', $permission->guard_name, "Permission {$permissionName} should have api guard");
        }
    }

    public function test_user_can_be_assigned_role_with_api_guard(): void
    {
        $this->markTestSkipped('Waiting for RolesAndPermissionsSeeder in next PR');
        
        $user = User::factory()->create();
        
        // This should work because user->guard_name = 'api'
        $user->assignRole('student');
        
        $this->assertTrue($user->hasRole('student'), 'User should have student role');
        $this->assertEquals('api', $user->roles->first()->guard_name, 'Assigned role should have api guard');
    }

    public function test_user_can_receive_permission_with_api_guard(): void
    {
        $this->markTestSkipped('Waiting for RolesAndPermissionsSeeder in next PR');
        
        $user = User::factory()->create();
        
        $user->givePermissionTo('view resources');
        
        $this->assertTrue($user->hasPermissionTo('view resources'), 'User should have view resources permission');
        $this->assertEquals('api', $user->permissions->first()->guard_name, 'Permission should have api guard');
    }

    public function test_user_with_student_role_has_correct_permissions(): void
    {
        $this->markTestSkipped('Waiting for RolesAndPermissionsSeeder in next PR');
        
        $user = User::factory()->create();
        $user->assignRole('student');
        
        // Student should have these permissions (from RolesAndPermissionsSeeder)
        $this->assertTrue($user->hasPermissionTo('view resources'), 'Student should have view resources');
        $this->assertTrue($user->hasPermissionTo('create resources'), 'Student should have create resources');
        $this->assertTrue($user->hasPermissionTo('edit own resources'), 'Student should have edit own resources');
        $this->assertTrue($user->hasPermissionTo('delete own resources'), 'Student should have delete own resources');
    }

    public function test_user_with_mentor_role_has_correct_permissions(): void
    {
        $this->markTestSkipped('Waiting for RolesAndPermissionsSeeder in next PR');
        
        $user = User::factory()->create();
        $user->assignRole('mentor');
        
        // Mentor should have all student permissions PLUS technical test permissions
        $this->assertTrue($user->hasPermissionTo('view resources'));
        $this->assertTrue($user->hasPermissionTo('create resources'));
        $this->assertTrue($user->hasPermissionTo('create technical tests'), 'Mentor should create technical tests');
    }

    public function test_user_with_admin_role_has_all_permissions(): void
    {
        $this->markTestSkipped('Waiting for RolesAndPermissionsSeeder in next PR');
        
        $user = User::factory()->create();
        $user->assignRole('admin');
        
        // Admin should have edit/delete ANY permissions
        $this->assertTrue($user->hasPermissionTo('edit any resources'), 'Admin should edit any resources');
        $this->assertTrue($user->hasPermissionTo('delete any resources'), 'Admin should delete any resources');
    }

    public function test_student_cannot_create_technical_tests(): void
    {
        $this->markTestSkipped('Waiting for RolesAndPermissionsSeeder in next PR');
        
        $user = User::factory()->create();
        $user->assignRole('student');
        
        $this->assertFalse($user->hasPermissionTo('create technical tests'), 'Student should NOT create technical tests');
    }
}