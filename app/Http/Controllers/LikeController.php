<?php
declare (strict_types= 1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\LikeService;
use App\Http\Requests\LikeRequest;
use App\Models\Like;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class LikeController extends Controller
{
    private LikeService $likeService;

    public function __construct(LikeService $likeService)
    {
        $this->likeService = $likeService;
        $this->middleware('auth:api');
        $this->middleware('permission:create likes')->only(['createStudentLike']);
        $this->middleware('permission:delete own likes')->only(['deleteStudentLike']);
        $this->middleware('permission:view resources')->only(['getStudentLikes']);
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
    public function createStudentLike(LikeRequest $request)
    {
        $user = auth('api')->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        if ($request->github_id !== $user->github_id) {
            return response()->json(['error' => 'Forbidden - Can only create likes for yourself'], 403);
        }

        try {
            $like = $this->likeService->createLike(
                $request->github_id, 
                $request->resource_id
            );
            return response()->json($like, 201);
        } catch (ConflictHttpException $e) {
            return response()->json(['error' => $e->getMessage()], 409);
        }
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
    public function deleteStudentLike(LikeRequest $request)
    {
        $user = auth('api')->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        if ($request->github_id !== $user->github_id) {
            return response()->json(['error' => 'Forbidden - Can only delete your own likes'], 403);
        }

        try {
            $this->likeService->deleteLike(
                $request->github_id, 
                $request->resource_id
            );
            return response()->json(['message' => 'Like deleted successfully'], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Like not found.'], 404);
        }
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
    public function getStudentLikes($github_id)
    {
        $user = auth('api')->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        if (!$user->can('view users') && $github_id != $user->github_id) {
            return response()->json(['error' => 'Forbidden - Can only view your own likes'], 403);
        }

        $likes = Like::where('github_id', $github_id)->get();
        return response()->json($likes, 200);
    }
}
