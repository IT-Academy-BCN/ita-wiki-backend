<?php
declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Crear roles con guard 'api'
        Role::firstOrCreate(['name' => 'student', 'guard_name' => 'api']);
        Role::firstOrCreate(['name' => 'mentor', 'guard_name' => 'api']);
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'api']);
        Role::firstOrCreate(['name' => 'superadmin', 'guard_name' => 'api']);

        $this->command->info('✅ Roles created successfully!');
        $this->command->info('Roles: ' . Role::pluck('name')->implode(', '));
    }
}