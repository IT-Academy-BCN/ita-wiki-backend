<?php
declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
   
    public function run(): void
    {
      
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $rolePermissions = $this->getRolePermissions();

        foreach ($rolePermissions as $roleName => $permissions) {
            $role = Role::findByName($roleName, 'api');
            
            if ($permissions === 'all') {
                
                $role->syncPermissions(Permission::where('guard_name', 'api')->pluck('name'));
            } else {
                $role->syncPermissions($permissions);
            }

            $this->command->info("✓ {$roleName}: {$role->permissions->count()} permissions");
        }

        $this->command->info(' Role-Permission assignments completed!');
    }

   
    private function getRolePermissions(): array
    {
        return [
            'student' => [
                'view resources',
                'create resources',
                'edit own resources',
                'delete own resources',
                'view technical tests',
                'create bookmarks',
                'delete own bookmarks',
                'create likes',
                'delete own likes',
            ],

            'mentor' => [
                'view resources',
                'create resources',
                'edit own resources',
                'edit all resources',
                'delete own resources',
                'view technical tests',
                'create technical tests',
                'edit own technical tests',
                'edit all technical tests',
                'delete own technical tests',
                'view users',
                'create bookmarks',
                'delete own bookmarks',
                'create likes',
                'delete own likes',
            ],

            'admin' => [
                'view resources',
                'create resources',
                'edit all resources',
                'delete all resources',
                'view technical tests',
                'create technical tests',
                'edit all technical tests',
                'delete all technical tests',
                'manage users',
                'view users',
                'edit user roles',
                'create bookmarks',
                'delete own bookmarks',
                'create likes',
                'delete own likes',
            ],

            'superadmin' => 'all',  // Special marker for all permissions
        ];
    }
}
