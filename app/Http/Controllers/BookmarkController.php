<?php

declare (strict_types= 1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\BookmarkRequest;
use App\Models\Bookmark;

class BookmarkController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/bookmarks",
     *     summary="Create a bookmark",
     *     tags={"Bookmarks"},
     *     description="Creates a new bookmark and returns a confirmation message",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"github_id","resource_id"},
     *             @OA\Property(property="github_id", type="integer", example=6729608),
     *             @OA\Property(property="resource_id", type="integer", example=10)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Created",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="github_id", type="integer", example=6729608),
     *             @OA\Property(property="resource_id", type="integer", example=10),
     *             @OA\Property(property="created_at", type="string", format="date-time", example="2025-04-03T15:27:09.000000Z"),
     *             @OA\Property(property="updated_at", type="string", format="date-time", example="2025-04-03T15:27:09.000000Z")
     *         )
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="Conflict",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Bookmark already exists.")
     *         )
     *     )
     * )
    */

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

    /**
     * @OA\Delete(
     *     path="/api/bookmarks",
     *     summary="Delete a bookmark",
     *     tags={"Bookmarks"},
     *     description="Deletes a bookmark and returns a confirmation message",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"github_id","resource_id"},
     *             @OA\Property(property="github_id", type="integer", example=6729608),
     *             @OA\Property(property="resource_id", type="integer", example=10)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Bookmark deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not Found",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Bookmark not found")
     *         )
     *     )
     * )
    */

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

    /**
     * @OA\Get(
     *     path="/api/bookmarks/{github_id}",
     *     summary="Get all bookmarks for a student",
     *     tags={"Bookmarks"},
     *     description="If the student's github_id exists it returns all bookmarks for that student or an empty array in case there is not any",
     *     @OA\Parameter(
     *         name="github_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer", example=6729608)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Bookmark")
     *         )
     *     )
     * )
    */

    public function getStudentBookmarks(BookmarkRequest $request)
    {
        $bookmarks = Bookmark::where('github_id', $request->validated('github_id'))->get();
        return response()->json($bookmarks, 200);
    }
}