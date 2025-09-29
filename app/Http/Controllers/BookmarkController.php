<?php

declare (strict_types= 1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\BookmarkRequest;
use App\Models\Bookmark;
use App\Services\BookmarkService;
use Exception;

class BookmarkController extends Controller
{
    private BookmarkService $bookmarkService;

    public function __construct(BookmarkService $bookmarkService)
    {
        $this->bookmarkService = $bookmarkService;
    }

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
     *             @OA\Property(property="github_id", type="integer", example=6729608, description="GitHub ID of the user creating the bookmark"),
     *             @OA\Property(property="resource_id", type="integer", example=10, description="ID of the resource being bookmarked by the user")
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
        try {
            $bookmark = $this->bookmarkService->createBookmark($request->github_id, $request->resource_id);
            return response()->json($bookmark, 201);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
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
     *             @OA\Property(property="message", type="string", example="Bookmark not found")
     *         )
     *     )
     * )
    */

    public function deleteStudentBookmark(BookmarkRequest $request)
    {
        try {
            $this->bookmarkService->deleteBookmark($request->github_id, $request->resource_id);
            return response()->json(['message' => 'Bookmark deleted successfully'], 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
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

    public function getStudentBookmarks($github_id)
    {
        $bookmarks = Bookmark::where('github_id', $github_id)->get();
        return response()->json($bookmarks, 200);
    }
}