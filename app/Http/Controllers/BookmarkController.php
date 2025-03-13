<?php

declare (strict_types= 1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\BookmarkRequest;
use App\Models\Bookmark;

class BookmarkController extends Controller
{
    public function createStudentBookmark(BookmarkRequest $request)
    {
        $validated = $request->validated();
        $existingBookmark = Bookmark::where('github_id', $validated['github_id'])
        ->where('resource_id', $validated['resource_id'])
        ->first();

        if ($existingBookmark) {
            return response()->json([
                'message' => 'Bookmark already exists.',
            ], 409); // HTTP 409 Conflict
        }

        $bookmark = Bookmark::create($validated);
        return response()->json($bookmark, 201);
    }

    public function deleteStudentBookmark(BookmarkRequest $request)
    {
        $validated = $request->validated();
        $bookmark = Bookmark::where('github_id', $validated['github_id'])
            ->where('resource_id', $validated['resource_id'])
            ->first();
        if($bookmark) {
            $bookmark->delete();
            return response()->json(['message' => 'Bookmark deleted successfully'], 200);
        }
        return response()->json(['error' => 'Bookmark not found'], 404);
    }

    public function getStudentBookmarks(BookmarkRequest $request, $github_id)
    {
        $validated = $request->validated();
        $bookmarks = Bookmark::where('github_id', $validated['github_id'])->get();
        return response()->json($bookmarks, 200);
    }
}