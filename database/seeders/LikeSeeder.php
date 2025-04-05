<?php

declare (strict_types= 1);

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Like;
use App\Models\Resource;
use App\Models\Role;

class LikeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */

    public function run(): void
    {
        // First 3 manually created likes using Role created by Seeder
        $knownStudentId = 6729608;
        $knownStudent = Role::where('github_id', $knownStudentId)->firstOrFail();
        $resources = Resource::inRandomOrder()->take(3)->get();
        
        foreach ($resources as $resource) {
            Like::firstOrCreate([
                'github_id' => $knownStudent->github_id,
                'resource_id' => $resource->id,
            ]);
        }
        // Create 5 additional likes
        Like::factory(5)->create();
    }
}
