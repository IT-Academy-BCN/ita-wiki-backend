<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\OldRole;

class OldRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        OldRole::create(['github_id' => 1, 'role' => 'superadmin']);

        OldRole::create([
            'github_id' => 6729608,
            'role' => 'student',
        ]);

        OldRole::factory()->count(20)->create();
    }
}
