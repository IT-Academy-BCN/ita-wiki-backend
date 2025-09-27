<?php

namespace App\Services;

use App\Models\Like;
use Exception;

class LikeService
{
    /**
     * Create a like for a user on a resource.
     * Throws exception if the like already exists.
     */
    public function createLike(int $userId, int $resourceId): Like
    {
        if (Like::where('user_id', $userId)->where('resource_id', $resourceId)->exists()) {
            throw new Exception('Like already exists for this resource.');
        }

        return Like::create([
            'user_id' => $userId,
            'resource_id' => $resourceId,
        ]);
    }

    /**
     * Delete a like for a user on a resource.
     * Throws exception if the like does not exist.
     */
    public function deleteLike(int $userId, int $resourceId): void
    {
        $like = Like::where('user_id', $userId)->where('resource_id', $resourceId)->first();

        if (!$like) {
            throw new Exception('Like not found.');
        }

        $like->delete();
    }
}