<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\Like;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class LikeService
{
    /**
     * Create a like for a user on a resource.
     *
     * @throws ConflictHttpException
     */
    public function createLike(int $githubId, int $resourceId): Like
    {
        if (Like::where('github_id', $githubId)
            ->where('resource_id', $resourceId)
            ->exists()) {
            throw new ConflictHttpException('Like already exists for this resource.');
        }

        return Like::create([
            'github_id' => $githubId,
            'resource_id' => $resourceId,
        ]);
    }

    /**
     * Delete a like for a user on a resource.
     *
     * @throws ModelNotFoundException
     */
    public function deleteLike(int $githubId, int $resourceId): void
    {
        $like = Like::where('github_id', $githubId)
            ->where('resource_id', $resourceId)
            ->firstOrFail(); 

        $like->delete();
    }
}