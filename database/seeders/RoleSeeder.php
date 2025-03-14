<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::create(['github_id' => 1, 'role' => 'superadmin']);

        Role::create([
            'github_id' => 6729608,
            'role' => 'student',
        ]);

        Role::factory(20)->create();
    }
}
