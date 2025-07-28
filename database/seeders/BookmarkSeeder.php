<?php

declare (strict_types= 1);

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Bookmark;
use App\Models\Resource;
use App\Models\User;

class BookmarkSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */

    public function run()
    {
        // First 3 manually created bookmarks using Role created by Seeder
        $knownStudentId = 999999999;
        $knownStudent = User::where('github_id', $knownStudentId)->firstOrFail();
        $resources = Resource::inRandomOrder()->take(3)->get();
        
        foreach ($resources as $resource) {
            Bookmark::firstOrCreate([
                'github_id' => $knownStudent->github_id,
                'resource_id' => $resource->id,
            ]);
        }
        // Create 5 additional bookmarks
        Bookmark::factory(5)->create();
    }
}