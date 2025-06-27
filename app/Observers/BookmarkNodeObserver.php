<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\BookmarkNode;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class BookmarkNodeObserver
{
    /**
     * Handle the BookmarkNode "created" event.
     */
    public function created(BookmarkNode $bookmarkNode): void
    {
        $bookmarkNode->resourceNode()->increment('bookmark_count');
    }

    /**
     * Handle the BookmarkNode "deleted" event.
     */
    public function deleted(BookmarkNode $bookmarkNode): void
    {
        $bookmarkNode->resourceNode()->decrement('bookmark_count');
    }


}
