<?php

declare (strict_types= 1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Role;
use App\Models\Resource;
use App\Models\Bookmark;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Bookmark>
 */
class BookmarkFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    protected static $usedCombinations = [];

    public function definition()
    {

        $students = Role::where('role', 'student')->pluck('github_id');
        $resources = Resource::pluck('id');
        $combinations = $students->crossJoin($resources);

        $existingBookmarks = Bookmark::select('github_id', 'resource_id')->get();

        $availableCombinations = $combinations->filter(function ($combination) use ($existingBookmarks) {
            $key = $combination[0] . '-' . $combination[1];
            return !$existingBookmarks->contains(function ($bookmark) use ($combination) {
                return $bookmark->github_id == $combination[0] && $bookmark->resource_id == $combination[1];
            }) && !isset(static::$usedCombinations[$key]);
        });

        if ($availableCombinations->isEmpty()) {
            throw new \RuntimeException('No more unique combinations available for bookmarks.');
        }

        $randomPair = $availableCombinations->random();
        $key = $randomPair[0] . '-' . $randomPair[1];
        static::$usedCombinations[$key] = true;

        return [
            'github_id' => $randomPair[0],
            'resource_id' => $randomPair[1],
        ];
    }
}