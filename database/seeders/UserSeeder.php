<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $testUser = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'github_id' => '999999999',
            'github_user_name' => 'Github Test User',
        ]);
        
        $testUser->assignRole('student');

        $users = User::factory(20)->create();
        
        foreach ($users as $user) {
            $roles = ['student', 'student', 'student', 'mentor', 'admin','superadmin'];
            $randomRole = $roles[array_rand($roles)];
            $user->assignRole($randomRole);
        }
        
        $this->command->info('Created User and assigned Spatie roles');
    }
}

