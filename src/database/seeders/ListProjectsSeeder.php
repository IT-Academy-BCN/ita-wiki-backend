<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ListProjects;
use App\Models\ContributorListProject;
use App\Enums\LanguageEnum;

class ListProjectsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $Project1 = ListProjects::firstOrCreate([
            'title' => 'Project Alpha',
            'time_duration' => '1 month',
            'language_backend' => LanguageEnum::PHP->value,
            'language_frontend' => LanguageEnum::JavaScript->value,
        ]);
        $Project2 = ListProjects::firstOrCreate([
            'title' => 'Project Beta',
            'time_duration' => '2 months',
            'language_backend' => LanguageEnum::Python->value,
            'language_frontend' => LanguageEnum::React->value,
        ]);
        $project3 = ListProjects::firstOrCreate([
            'title' => 'Project Gamma',
            'time_duration' => '3 weeks',
            'language_backend' => LanguageEnum::Java->value,
            'language_frontend' => LanguageEnum::TypeScript->value,
        ]);
    }
}
