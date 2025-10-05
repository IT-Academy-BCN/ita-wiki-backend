<?php

declare (strict_types= 1);

namespace App\Http\Controllers;

use App\Http\Requests\ShowResourceRequest;
use App\Models\Resource;
use App\Http\Requests\StoreResourceRequest;
use App\Http\Requests\UpdateResourceRequest; 


/**
 * @OA\Info(
 *  title="Swagger Documentation for ITA-Wiki",
 *  version="1.0.0.0",
 *  description="Project ITA-Wiki documentation wall"
 * )
 */
class ResourceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
        $this->middleware('permission:view resources')->only(['index']);
        $this->middleware('permission:create resources')->only(['store']);
        $this->middleware('permission:edit own resources|edit all resources')->only(['update']);
        $this->middleware('permission:delete own resources|delete all resources')->only(['destroy']);
    }

    /**
     * @OA\Get(
     *  path="/api/resources",
     *  summary="Search or list all resources",
     *  tags={"Resources"},
     *  description="Returns resources matching the search term or all resources if no search term is provided",
     *  @OA\Parameter(
     *      name="search",
     *      in="query",
     *      required=false,
     *      @OA\Schema(type="string", example="JavaScript")
     *  ),
     *  @OA\Response(
     *      response=200,
     *      description="List of resources",
     *      @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Resource"))
     *  )
     * )
     */
    public function index(ShowResourceRequest $request)
    {
        $validated = $request->validated();
        $searchTerm = $validated['search'] ?? null;

        if ($searchTerm && trim($searchTerm) !== '') {
            $resources = Resource::where('title', 'like', '%' . $searchTerm . '%')
                ->orWhere('description', 'like', '%' . $searchTerm . '%')
                ->orWhere('url', 'like', '%' . $searchTerm . '%')
                ->orWhere('category', 'like', '%' . $searchTerm . '%')
                ->orWhere('type', 'like', '%' . $searchTerm . '%')
                ->get();

            return response()->json($resources, 200);
        }

        $resources = Resource::all();
        return response()->json($resources, 200);
    }

    /**
     * @OA\Post(
     *     path="/api/resources",
     *     summary="Create a new resource using tag IDs",
     *     tags={"Resources"},
     *     description="Creates a resource. This version expects an array of tag IDs instead of tag names.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"github_id", "title", "url", "category", "type"},
     *             @OA\Property(property="github_id", type="integer", example=123456),
     *             @OA\Property(property="title", type="string", example="Aprende Laravel en 10 días"),
     *             @OA\Property(property="description", type="string", example="Curso completo de Laravel para principiantes."),
     *             @OA\Property(property="url", type="string", format="url", example="https://miweb.com/laravel"),
     *             @OA\Property(property="category", type="string", example="Fullstack PHP"),
     *             @OA\Property(property="tags", type="array", @OA\Items(type="integer"), example={1, 3, 5}),
     *             @OA\Property(property="type", type="string", example="Video")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Resource created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Resource")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function store(StoreResourceRequest $request)
    {
        $validated = $request->validated();
        $resource = Resource::create($validated);
        return response()->json($resource, 201);
    }

    /**
     * @OA\Put(
     *     path="/api/resources/{resource}",
     *     summary="Update a resource",
     *     tags={"Resources"},
     *     description="Update an existing resource with validation",
     *     @OA\Parameter(
     *         name="resource",
     *         in="path",
     *         description="ID of the resource to update",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"github_id", "title", "description", "url"},
     *             @OA\Property(property="github_id", type="integer", example=6729608),
     *             @OA\Property(property="title", type="string", example="Laravel Best Practices"),
     *             @OA\Property(property="description", type="string", example="A collection of best practices for Laravel development"),
     *             @OA\Property(property="url", type="string", format="url", example="https://laravelbestpractices.com"),
     *             @OA\Property(property="tags", type="array", maxItems=5, uniqueItems=true, nullable=true, @OA\Items(type="string", example="testing"), example={"testing", "tdd", "hooks"}),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Resource updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Resource")
     *     ),
     *     @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
    */
    public function update(UpdateResourceRequest $request, Resource $resource)
    {
        $user = auth('api')->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Check ownership for "own" permissions
        if (!$user->can('edit all resources')) {
            if ($resource->github_id !== $user->github_id) {
                return response()->json(['error' => 'Forbidden - Not your resource'], 403);
            }
        }

        $validated = $request->validated();
        unset($validated['github_id']);

        if (array_key_exists('tags', $validated) && empty($validated['tags'])) {
            $validated['tags'] = null;
        }

        $resource->update($validated);

        return response()->json($resource, 200);
    }

    /**
     * @OA\Delete(
     *  path="/api/resources/{id}",
     *  summary="Delete a resource",
     *  tags={"Resources"},
     *  @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *  @OA\Response(response=200, description="Resource deleted successfully"),
     *  @OA\Response(response=403, description="Forbidden - Not your resource"),
     *  @OA\Response(response=404, description="Resource not found")
     * )
     */
    public function destroy(Resource $resource)
    {
        $user = auth('api')->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Check ownership for "own" permissions
        if (!$user->can('delete all resources')) {
            if ($resource->github_id !== $user->github_id) {
                return response()->json(['error' => 'Forbidden - Not your resource'], 403);
            }
        }

        $resource->delete();
        return response()->json([
            'success' => true,
            'message' => 'Resource deleted successfully'
        ]);
    }
}
