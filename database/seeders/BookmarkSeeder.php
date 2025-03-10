<?php

declare (strict_types= 1);

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Bookmark;
use App\Models\Resource;
use App\Models\Role;

class BookmarkSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $student = Role::where('github_id', 6729608)->where('role', 'student')->firstOrFail();
        $resources = Resource::inRandomOrder()->take(3)->get();

        if ($student && $resources) {
            foreach ($resources as $resource) {
                Bookmark::firstOrCreate([ // this function prevents creation of duplicates
                    'github_id' => $student->github_id,
                    'resource_id' => $resource->id,
                ]);
            }
        }

        // Additional bookmarks : USING A FACTORY IN THIS CASE IS TROUBLESOME
        $students = Role::where('role', 'student')->inRandomOrder()->take(3)->get();
        $other_resources = Resource::inRandomOrder()->take(3)->get();
        foreach ($students as $student) {
            foreach ($other_resources as $resource) {
                Bookmark::firstOrCreate([
                    'github_id' => $student->github_id,
                    'resource_id' => $resource->id,
                ]);
            }
        }
    }
}
