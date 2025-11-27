<?php

declare (strict_types= 1);

namespace App\Observers;

use App\Models\Like;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class LikeObserver implements ShouldHandleEventsAfterCommit
{
    public function created(Like $like): void
    {
        $like->resource()->increment('like_count');
    }

    public function deleted(Like $like): void
    {
        $like->resource()->decrement('like_count');
    }
}
