<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ========== RESOURCES PERMISSIONS ==========
        $resourcePermissions = [
            'view resources',
            'create resources',
            'edit own resources',
            'edit all resources',     
            'delete own resources',
            'delete all resources',   
        ];

        foreach ($resourcePermissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission, 'guard_name' => 'api']
            );
        }

        // ========== TECHNICAL TESTS PERMISSIONS ==========
        $technicalTestPermissions = [
            'view technical tests',
            'create technical tests',
            'upload technical tests',
        ];

        foreach ($technicalTestPermissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission, 'guard_name' => 'api']
            );
        }

        // ========== BOOKMARKS & LIKES PERMISSIONS ==========
        $bookmarkLikePermissions = [
            'create bookmarks',
            'delete own bookmarks',
            'create likes',
            'delete own likes',
        ];

        foreach ($bookmarkLikePermissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission, 'guard_name' => 'api']
            );
        }

        // ========== USER PERMISSIONS ==========
        $userPermissions = [
            'view users',
            'manage users',
            'assign roles',
        ];

        foreach ($userPermissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission, 'guard_name' => 'api']
            );
        }

        // ========== CREATE ROLES ==========

        // STUDENT ROLE
        $student = Role::firstOrCreate(
            ['name' => 'student', 'guard_name' => 'api']
        );
        $student->syncPermissions([
            'view resources',
            'create resources',
            'edit own resources',
            'delete own resources',
            'view technical tests',
            'create technical tests',
            'upload technical tests',
            'create bookmarks',
            'delete own bookmarks',
            'create likes',
            'delete own likes',
        ]);

        // MENTOR ROLE
        $mentor = Role::firstOrCreate(
            ['name' => 'mentor', 'guard_name' => 'api']
        );
        $mentor->syncPermissions([
            'view resources',
            'create resources',
            'edit own resources',
            'delete own resources',
            'view technical tests',
            'create technical tests',
            'upload technical tests',
            'create bookmarks',
            'delete own bookmarks',
            'create likes',
            'delete own likes',
            'view users',
        ]);

        // ADMIN ROLE
        $admin = Role::firstOrCreate(
            ['name' => 'admin', 'guard_name' => 'api']
        );
        $admin->syncPermissions([
            'view resources',
            'create resources',
            'edit own resources',
            'edit all resources',     
            'delete own resources',
            'delete all resources',   
            'view technical tests',
            'create technical tests',
            'upload technical tests',
            'create bookmarks',
            'delete own bookmarks',
            'create likes',
            'delete own likes',
            'view users',
            'manage users',
            'assign roles',
        ]);

        // SUPERADMIN ROLE
        $superadmin = Role::firstOrCreate(
            ['name' => 'superadmin', 'guard_name' => 'api']
        );
        $superadmin->givePermissionTo(Permission::where('guard_name', 'api')->get());

        $this->command->info('✅ Roles and Permissions seeded successfully');
    }
}
