<?php

declare (strict_types= 1);

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ListProjects;

class ListProjectsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        ListProjects::create([
            'id' => 1,
            'title' => 'Project Alpha',
            'time_duration' => '1 month',
            'lenguage_Backend' => 'PHP',
            'lenguage_Frontend' => 'JavaScript',
        ]);

    }
}
