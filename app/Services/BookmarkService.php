<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\Bookmark;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class BookmarkService
{
    /**
     * Create a bookmark for a user on a resource.
     * 
     * @throws ConflictHttpException
     */
    public function createBookmark(int $githubId, int $resourceId): Bookmark
    {
        if (Bookmark::where('github_id', $githubId)
            ->where('resource_id', $resourceId)
            ->exists()) {
            throw new ConflictHttpException('Bookmark already exists for this resource.');
        }

        return Bookmark::create([
            'github_id' => $githubId,
            'resource_id' => $resourceId,
        ]);
    }

    /**
     * Delete a bookmark for a user on a resource.
     * 
     * @throws ModelNotFoundException
     */
    public function deleteBookmark(int $githubId, int $resourceId): void
    {
        $bookmark = Bookmark::where('github_id', $githubId)
            ->where('resource_id', $resourceId)
            ->firstOrFail();

        $bookmark->delete();
    }
}