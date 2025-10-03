<?php
declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Se recomienda limpiar cache al inicio del seeder
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Crear roles. Usar firstOrCreate para evitar errores de duplicados en tabla Roles
        $student = Role::firstOrCreate(['name' => 'student', 'guard_name' => 'api']);
        $mentor = Role::firstOrCreate(['name' => 'mentor', 'guard_name' => 'api']);
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'api']);
        $superadmin = Role::firstOrCreate(['name' => 'superadmin', 'guard_name' => 'api']);

        // Crear permisos - Resources
        $viewResources = Permission::firstOrCreate(['name' => 'view resources', 'guard_name' => 'api']);
        $createResources = Permission::firstOrCreate(['name' => 'create resources', 'guard_name' => 'api']);
        $editOwnResources = Permission::firstOrCreate(['name' => 'edit own resources', 'guard_name' => 'api']);
        $editAllResources = Permission::firstOrCreate(['name' => 'edit all resources', 'guard_name' => 'api']);
        $deleteOwnResources = Permission::firstOrCreate(['name' => 'delete own resources', 'guard_name' => 'api']);
        $deleteAllResources = Permission::firstOrCreate(['name' => 'delete all resources', 'guard_name' => 'api']);

        // Crear permisos - Technical Tests
        $viewTechnicalTests = Permission::firstOrCreate(['name' => 'view technical tests', 'guard_name' => 'api']);
        $createTechnicalTests = Permission::firstOrCreate(['name' => 'create technical tests', 'guard_name' => 'api']);
        $editOwnTechnicalTests = Permission::firstOrCreate(['name' => 'edit own technical tests', 'guard_name' => 'api']);
        $editAllTechnicalTests = Permission::firstOrCreate(['name' => 'edit all technical tests', 'guard_name' => 'api']);
        $deleteOwnTechnicalTests = Permission::firstOrCreate(['name' => 'delete own technical tests', 'guard_name' => 'api']);
        $deleteAllTechnicalTests = Permission::firstOrCreate(['name' => 'delete all technical tests', 'guard_name' => 'api']);

        // Crear permisos - User Management
        $manageUsers = Permission::firstOrCreate(['name' => 'manage users', 'guard_name' => 'api']);
        $viewUsers = Permission::firstOrCreate(['name' => 'view users', 'guard_name' => 'api']);
        $editUserRoles = Permission::firstOrCreate(['name' => 'edit user roles', 'guard_name' => 'api']);

        // Crear permisos - Interactions (Bookmarks & Likes)
        $createBookmarks = Permission::firstOrCreate(['name' => 'create bookmarks', 'guard_name' => 'api']);
        $deleteOwnBookmarks = Permission::firstOrCreate(['name' => 'delete own bookmarks', 'guard_name' => 'api']);
        $createLikes = Permission::firstOrCreate(['name' => 'create likes', 'guard_name' => 'api']);
        $deleteOwnLikes = Permission::firstOrCreate(['name' => 'delete own likes', 'guard_name' => 'api']);

        // Asignar permisos a roles

        // STUDENT - Permisos básicos
        $student->givePermissionTo([
            $viewResources,
            $createResources,
            $editOwnResources,
            $deleteOwnResources,
            $viewTechnicalTests,
            $createBookmarks,
            $deleteOwnBookmarks,
            $createLikes,
            $deleteOwnLikes,
        ]);

        // MENTOR - Puede gestionar contenido
        $mentor->givePermissionTo([
            $viewResources,
            $createResources,
            $editOwnResources,
            $editAllResources,
            $deleteOwnResources,
            $viewTechnicalTests,
            $createTechnicalTests,
            $editOwnTechnicalTests,
            $editAllTechnicalTests,
            $deleteOwnTechnicalTests,
            $viewUsers,
            $createBookmarks,
            $deleteOwnBookmarks,
            $createLikes,
            $deleteOwnLikes,
        ]);

        // ADMIN - Casi todos los permisos
        $admin->givePermissionTo([
            $viewResources,
            $createResources,
            $editAllResources,
            $deleteAllResources,
            $viewTechnicalTests,
            $createTechnicalTests,
            $editAllTechnicalTests,
            $deleteAllTechnicalTests,
            $manageUsers,
            $viewUsers,
            $editUserRoles,
            $createBookmarks,
            $deleteOwnBookmarks,
            $createLikes,
            $deleteOwnLikes,
        ]);

        // SUPERADMIN - Todos los permisos
        $superadmin->givePermissionTo(Permission::all());

        $this->command->info('Roles and permissions created successfully!');
        $this->command->info('Roles: ' . Role::pluck('name')->implode(', '));
        $this->command->info('Permissions: ' . Permission::count() . ' permissions created');
    }
}
