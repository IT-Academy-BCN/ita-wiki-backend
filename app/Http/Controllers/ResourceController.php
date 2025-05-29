<?php

declare (strict_types= 1);

namespace App\Http\Controllers;

use App\Models\Resource;
use App\Http\Requests\StoreResourceRequest;
use App\Http\Requests\StoreResourceV2Request;

    /**
     * @OA\Info(
     *  title="Swagger Documentation for ITA-Wiki",
     *  version="1.0.0.0",
     *  description="Project ITA-Wiki documentation wall"
     * )
     */

class ResourceController extends Controller
{

    /**
     * @OA\Post(
     *     path="/api/resources",
     *     summary="Create a new resource (deprecated, use /api/v2/resources instead)",
     *      deprecated= true,
     *     tags={"Resources"},
     *     description="This endpoint is deprecated and will be removed soon. Please use the new endpoint /api/v2/resources instead.
     *      Creates a new resource and returns the created resource",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"github_id", "title", "description", "url", "category", "type"},
     *             @OA\Property(property="github_id", type="integer", example=6729608, description="GitHub ID of an existing user role creating the resource"),
     *             @OA\Property(property="title", type="string", minLength=5, maxLength=255, example="Laravel Best Practices", description="Title of the resource"),
     *             @OA\Property(property="description", type="string", minLength=10, maxLength=1000, example="A collection of best practices for Laravel development", description="Description of the resource (10-1000 characters)"),
     *             @OA\Property(property="url", type="string", format="url", example="https://laravelbestpractices.com", description="URL of the resource"),
     *             @OA\Property(property="category", type="string", enum={"Node","React","Angular","JavaScript","Java","Fullstack PHP","Data Science","BBDD"}, example="React", description="Category of the resource"),
     *             @OA\Property(property="tags", type="array", @OA\Items(type="string", example="oop"), description="Array of tags validated against available options (nullable)"),
     *             @OA\Property(property="type", type="string", enum={"Video","Cursos","Blog"}, example="Video", description="Type of the resource")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Resource created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Resource")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object", example={
     *                 "github_id": {"The github_id field is required.", "The selected github_id is invalid."},
     *                 "title": {"The title field is required.", "Title must be at least 5 characters.", "Title must not exceed 255 characters."},
     *                 "description": {"The description field is required.", "Description must be at least 10 characters.", "Description must not exceed 1000 characters."},
     *                 "url": {"The url field is required.", "The url format is invalid."},
     *                 "category": {"The category field is required.", "The selected category is invalid."},
     *                 "tags": {"The tags field must be an array of strings.", "The selected tags are invalid."},
     *                 "type": {"The type field is required.", "The selected type is invalid."}
     *             })
     *         )
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
 * @OA\Post(
 *     path="/api/v2/resources",
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

     public function storeResource(StoreResourceV2Request $request)
    {
        $validated = $request->validated();
        $resource = Resource::create($validated);
        return response()->json($resource, 201);
    }

    /**
     * @OA\Get(
     *  path="/api/resources",
     *  summary="Get all resources",
     *  tags={"Resources"},
     *  description="Returns a list of all resources",
     *  @OA\Response(
     *     response=200,
     *     description="Resource list",
     *     @OA\JsonContent(
     *      type="array",
     *      @OA\Items(ref="#/components/schemas/Resource")
     *      )
     *     )
     * )
     */

    public function index()
    {
        $resources = Resource::all();
        return response()->json($resources, 200);
    }

}
