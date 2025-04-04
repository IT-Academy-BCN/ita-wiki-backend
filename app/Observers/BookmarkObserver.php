<?php

declare (strict_types= 1);

namespace App\Observers;

use App\Models\Bookmark;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class BookmarkObserver implements ShouldHandleEventsAfterCommit
{
    /**
     * Handle the Bookmark "created" event.
     */
    public function created(Bookmark $bookmark): void
    {
        $bookmark->resource()->increment('bookmark_count');
    }

    /**
     * Handle the Bookmark "deleted" event.
     */
    public function deleted(Bookmark $bookmark): void
    {
        $bookmark->resource()->decrement('bookmark_count');
    }
}
