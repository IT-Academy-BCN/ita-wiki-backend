<?php

declare (strict_types= 1);

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ContributorListProject;
use App\Models\ListProjects;
use App\Models\User;
use App\Enums\ContributorStatusEnum;

class ContributorListProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $userContributor = User::firstOrCreate(
            ['email' => 'contributor@example.com'],
            [
                'name' => 'Contributor User',
                'github_id' => '888888888',
                'github_user_name' => 'Github Contributor User',
                'password' => bcrypt('password123'),
            ]
        );

        $Project1 = ListProjects::first();
        
        $contributor1 = ContributorListProject::firstOrCreate([
            'user_id' => $userContributor->id,
            'list_project_id' => $Project1->id,
            'programming_role' => 'Backend Developer',
            'status' => ContributorStatusEnum::Pending->value,
        ]);

        
        
    }
}
