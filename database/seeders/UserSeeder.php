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
            'github_id' => '12345678',
            'github_user_name' => 'test_user',
        ]);

        $token = $testUser->createToken('Personal Access Token')->plainTextToken;

        $this->command->info('User test created successfully');
        $this->command->info('Github ID: ' . $testUser->github_id);
        $this->command->info('GitHub Username: ' . $testUser->github_user_name);
        $this->command->info('Personal Access Token: ' . $token);
        
        /* $testUser->assignRole('student');

        $users = User::factory(20)->create();
        
        foreach ($users as $user) {
            $roles = ['student', 'student', 'student', 'mentor', 'admin','superadmin'];
            $randomRole = $roles[array_rand($roles)];
            $user->assignRole($randomRole);
        }
        
        $this->command->info('Created User and assigned Spatie roles'); */
    }
}

