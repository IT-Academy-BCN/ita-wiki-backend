<?php

namespace App\Services;

use App\Models\Bookmark;
use Exception;

class BookmarkService
{
    /**
     * Create a bookmark for a user on a resource.
     * Throws exception if the bookmark already exists.
     */
    public function createBookmark(int $githubId, int $resourceId): Bookmark
    {
        if (Bookmark::where('github_id', $githubId)->where('resource_id', $resourceId)->exists()) {
            throw new Exception('Bookmark already exists for this resource.');
        }

        return Bookmark::create([
            'github_id' => $githubId,
            'resource_id' => $resourceId,
        ]);
    }

    /**
     * Delete a bookmark for a user on a resource.
     * Throws exception if the bookmark does not exist.
     */
    public function deleteBookmark(int $githubId, int $resourceId): void
    {
        $bookmark = Bookmark::where('github_id', $githubId)->where('resource_id', $resourceId)->first();

        if (!$bookmark) {
            throw new Exception('Bookmark not found.');
        }

        $bookmark->delete();
    }
}