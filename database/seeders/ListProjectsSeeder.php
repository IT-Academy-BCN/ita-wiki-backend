<?php

declare (strict_types= 1);

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ListProjects;
use App\Models\ContributorListProject;

class ListProjectsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $Project1= ListProjects::firstOrCreate([
            'title' => 'Project Alpha',
            'time_duration' => '1 month',
            'language_Backend' => 'PHP',
            'language_Frontend' => 'JavaScript',
        ]);
        $Project2= ListProjects::firstOrCreate([
            'title' => 'Project Beta',
            'time_duration' => '2 months',
            'language_Backend' => 'Python',
            'language_Frontend' => 'HTML',
        ]);
        $project3= ListProjects::firstOrCreate([
            'title' => 'Project Gamma',
            'time_duration' => '3 weeks',
            'language_Backend' => 'Ruby',
            'language_Frontend' => 'CSS',
        ]);

    }
}
