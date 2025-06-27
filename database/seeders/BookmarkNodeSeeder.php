<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\BookmarkNode;
use App\Models\ResourceNode;
use App\Models\RoleNode;

class BookmarkNodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // First 3 manually created bookmarks using the RoleNode created Seeder class
        $knownNodeId = 'MDQ6VXNlcjY3Mjk2MDg='; //'MDQ6VXNlcj6729608='; a known seeder 
        //$knownStudent = RoleNode::where('node_id', $knownNodeId)->firstOrFail();
        $knownStudent = RoleNode::firstOrCreate(['node_id' => $knownNodeId],['role' => 'student']);

        $resources = ResourceNode::inRandomOrder()->take(3)->get();

        foreach ($resources as $resource) {
            BookmarkNode::firstOrCreate([
                'node_id'          => $knownStudent->node_id,
                'resource_node_id' => $resource->id,
            ]);
        }

        // Creating 5 additional bookmarks
        //BookmarkNode::factory(5)->create();


    // Calculate max possible unique bookmarks and match
    $students = RoleNode::where('role', 'student')->pluck('node_id');
    $resources = ResourceNode::pluck('id');
    $maxBookmarks = $students->count() * $resources->count();
    $existingBookmarks = BookmarkNode::count();
    $toCreate = max(0, min(5, $maxBookmarks - $existingBookmarks)); // Only create what is possible

    if ($toCreate > 0) {
        BookmarkNode::factory($toCreate)->create();

    }
}
}
