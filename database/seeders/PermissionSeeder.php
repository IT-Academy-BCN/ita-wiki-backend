<?php
declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Permisos - Resources
        Permission::firstOrCreate(['name' => 'view resources', 'guard_name' => 'api']);
        Permission::firstOrCreate(['name' => 'create resources', 'guard_name' => 'api']);
        Permission::firstOrCreate(['name' => 'edit own resources', 'guard_name' => 'api']);
        Permission::firstOrCreate(['name' => 'edit all resources', 'guard_name' => 'api']);
        Permission::firstOrCreate(['name' => 'delete own resources', 'guard_name' => 'api']);
        Permission::firstOrCreate(['name' => 'delete all resources', 'guard_name' => 'api']);

        // Permisos - Technical Tests
        Permission::firstOrCreate(['name' => 'view technical tests', 'guard_name' => 'api']);
        Permission::firstOrCreate(['name' => 'create technical tests', 'guard_name' => 'api']);
        Permission::firstOrCreate(['name' => 'edit own technical tests', 'guard_name' => 'api']);
        Permission::firstOrCreate(['name' => 'edit all technical tests', 'guard_name' => 'api']);
        Permission::firstOrCreate(['name' => 'delete own technical tests', 'guard_name' => 'api']);
        Permission::firstOrCreate(['name' => 'delete all technical tests', 'guard_name' => 'api']);

        // Permisos - User Management
        Permission::firstOrCreate(['name' => 'manage users', 'guard_name' => 'api']);
        Permission::firstOrCreate(['name' => 'view users', 'guard_name' => 'api']);
        Permission::firstOrCreate(['name' => 'edit user roles', 'guard_name' => 'api']);

        // Permisos - Interactions (Bookmarks & Likes)
        Permission::firstOrCreate(['name' => 'create bookmarks', 'guard_name' => 'api']);
        Permission::firstOrCreate(['name' => 'delete own bookmarks', 'guard_name' => 'api']);
        Permission::firstOrCreate(['name' => 'create likes', 'guard_name' => 'api']);
        Permission::firstOrCreate(['name' => 'delete own likes', 'guard_name' => 'api']);

        $this->command->info('✅ Permissions created successfully!');
        $this->command->info('Total permissions: ' . Permission::count());
    }
}