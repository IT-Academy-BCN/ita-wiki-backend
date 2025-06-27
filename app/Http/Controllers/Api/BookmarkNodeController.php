<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Models\RoleNode;
use App\Models\BookmarkNode;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\BookmarkNodeRequest;

class BookmarkNodeController extends Controller
{

/**
 * @OA\Post(
 *     path="/api/bookmarks-node",
 *     summary="Create a bookmark (node_id version)",
 *     tags={"BookmarksNode"},
 *     description="Creates a new bookmark for a user identified by node_id.",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"node_id","resource_node_id"},
 *             @OA\Property(property="node_id", type="string", example="MDQ6VXNlcj58922="),
 *             @OA\Property(property="resource_node_id", type="integer", example=5)
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Created",
 *         @OA\JsonContent(ref="#/components/schemas/BookmarkNode")
 *     ),
 *     @OA\Response(
 *         response=409,
 *         description="Conflict",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Bookmark already exists.")
 *         )
 *     )
 * )
 */
public function createStudentBookmarkNode(BookmarkNodeRequest $request)
{
    $validated = $request->validated();
    $existingBookmark = BookmarkNode::where('node_id', $validated['node_id'])
        ->where('resource_node_id', $validated['resource_node_id'])
        ->first();

    if ($existingBookmark) {
        return response()->json([
            'message' => 'Bookmark already exists.',
        ], 409);
    }

    $bookmark = BookmarkNode::create($validated);
    return response()->json($bookmark, 201);
}

/**
 * @OA\Delete(
 *     path="/api/bookmarks-node",
 *     summary="Delete a bookmark (node_id version)",
 *     tags={"BookmarksNode"},
 *     description="Deletes a bookmark for a user identified by node_id.",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"node_id","resource_node_id"},
 *             @OA\Property(property="node_id", type="string", example="MDQ6VXNlcj58922="),
 *             @OA\Property(property="resource_node_id", type="integer", example=5)
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
public function deleteStudentBookmarkNode(BookmarkNodeRequest $request)
{
    $validated = $request->validated();
    $bookmark = BookmarkNode::where('node_id', $validated['node_id'])
        ->where('resource_node_id', $validated['resource_node_id'])
        ->first();

    if ($bookmark) {
        $bookmark->delete();
        return response()->json([
            'message' => 'Bookmark deleted successfully'
    ], 200);

    }
    
    return response()->json(['message' => 'Bookmark not found'], 404);
}

/**
 * @OA\Get(
 *     path="/api/bookmarks-node/{node_id}",
 *     summary="Get all bookmarks for a student (node_id version)",
 *     tags={"BookmarksNode"},
 *     description="Returns all bookmarks for the given node_id.",
 *     @OA\Parameter(
 *         name="node_id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="string", example= "MDQ6VXNlcj8=")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Success",
 *         @OA\JsonContent(
 *             type="array",
 *             @OA\Items(ref="#/components/schemas/BookmarkNode")
 *         )
 *     )
 * )
 */
public function getStudentBookmarksNode(BookmarkNodeRequest $request)
{
    // if (!RoleNode::where('node_id', $node_id)->exists()) {
    //     return response()->json(['message' => 'Role not found'], 422);
    // }
    
    $bookmarks = BookmarkNode::where('node_id', $request->validated('node_id'))->get();
    return response()->json($bookmarks, 200);
}

}
