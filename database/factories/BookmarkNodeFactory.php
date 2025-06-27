<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\RoleNode;
use App\Models\ResourceNode;
use App\Models\BookmarkNode;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BookmarkNode>
 */
class BookmarkNodeFactory extends Factory
{
    protected static $usedCombinations = [];

    public function definition()
    {
        $students = RoleNode::where('role', 'student')->pluck('node_id');
        $resources = ResourceNode::pluck('id');
        $combinations = $students->crossJoin($resources);

        $existingBookmarks = BookmarkNode::select('node_id', 'resource_node_id')->get();
        
        $availableCombinations = $combinations->filter(function ($combination) use ($existingBookmarks) {
        $key = $combination[0] . '-' . $combination[1];

        return !$existingBookmarks->contains(function ($bookmark) use ($combination) {
            return $bookmark->node_id == $combination[0] && $bookmark->resource_node_id == $combination[1];
        }) && !isset(static::$usedCombinations[$key]);
    });

        if ($availableCombinations->isEmpty()) {
            throw new \RuntimeException('No more unique combinations available for bookmarks_node.');
        }

        $randomPair = $availableCombinations->random();
        
        $key = $randomPair[0] . '-' . $randomPair[1];

        static::$usedCombinations[$key] = true;

        return [
            'node_id' => $randomPair[0],
            'resource_node_id' => $randomPair[1],
        ];
    }
}
