<?php

declare (strict_types= 1);

namespace App\Observers;

use App\Models\Like;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class LikeObserver implements ShouldHandleEventsAfterCommit
{
    /**
     * Handle the Like "created" event.
     */
    public function created(Like $like): void
    {
        $like->resource()->increment('like_count');
    }

    /**
     * Handle the Like "deleted" event.
     */
    public function deleted(Like $like): void
    {
        $like->resource()->decrement('like_count');
    }
}
