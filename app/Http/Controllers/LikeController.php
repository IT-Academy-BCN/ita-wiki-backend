<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Likes\CreateLikeRequest;
use App\Http\Requests\Likes\DeleteLikeRequest;
use App\Models\Like;
use Illuminate\Http\JsonResponse;

class LikeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
        $this->middleware('check.permission:create likes')->only(['createStudentLike']);
        $this->middleware('check.permission:delete own likes')->only(['deleteStudentLike']);
    }

    /**
     * @OA\Get(
     *     path="/api/likes/{github_id}",
     *     summary="Get all likes for a student",
     *     tags={"Likes"},
     *     description="If the student's github_id exists it returns all likes for that student or an empty array in case there is not any",
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
     *             @OA\Items(ref="#/components/schemas/Like")
     *         )
     *     )
     * )
    */
    public function getStudentLikes(int $github_id): JsonResponse
    {
        $user = auth('api')->user();
        
        if ($user->github_id !== $github_id) {
            return response()->json(['error' => 'Forbidden - Can only view your own likes'], 403);
        }

        $likes = Like::where('github_id', $github_id)->get();
        return response()->json($likes);
    }

    /**
     * @OA\Post(
     *     path="/api/likes",
     *     summary="Create a like",
     *     tags={"Likes"},
     *     description="Creates a new like and returns a confirmation message",
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
     *             @OA\Property(property="message", type="string", example="Like already exists.")
     *         )
     *     )
     * )
    */
    public function createStudentLike(CreateLikeRequest $request): JsonResponse
    {
        $user = auth('api')->user();

        $existingLike = Like::where('github_id', $user->github_id)
            ->where('resource_id', $request->resource_id)
            ->first();

        if ($existingLike) {
            return response()->json(['error' => 'Like already exists'], 409);
        }

        $like = Like::create([
            'github_id' => $user->github_id,
            'resource_id' => $request->resource_id,
        ]);

        return response()->json($like, 201);
    }

    /**
     * @OA\Delete(
     *     path="/api/likes",
     *     summary="Delete a like",
     *     tags={"Likes"},
     *     description="Deletes a like and returns a confirmation message",
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
     *             @OA\Property(property="message", type="string", example="Like deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not Found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Like not found")
     *         )
     *     )
     * )
    */
    public function deleteStudentLike(DeleteLikeRequest $request): JsonResponse
    {
        $user = auth('api')->user();

        $like = Like::where('github_id', $user->github_id)
            ->where('resource_id', $request->resource_id)
            ->first();

        if (!$like) {
            return response()->json(['error' => 'Like not found'], 404);
        }

        $like->delete();

        return response()->json(['message' => 'Like deleted successfully']);
    }
}
