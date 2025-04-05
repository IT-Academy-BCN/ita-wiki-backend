<?php

declare (strict_types= 1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreResourceRequest;
use App\Models\Resource;

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
     *     summary="Create a new resource",
     *     tags={"Resources"},
     *     description="Creates a new resource and returns the created resource",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"github_id", "title", "description", "url", "category", "theme", "type"},
     *             @OA\Property(property="github_id", type="integer", example=6729608, description="GitHub ID of an existing user role creating the resource"),
     *             @OA\Property(property="title", type="string", minLength=5, maxLength=255, example="Laravel Best Practices", description="Title of the resource"),
     *             @OA\Property(property="description", type="string", minLength=10, maxLength=1000, example="A collection of best practices for Laravel development", description="Description of the resource (10-1000 characters)"),
     *             @OA\Property(property="url", type="string", format="url", example="https://laravelbestpractices.com", description="URL of the resource"),
     *             @OA\Property(property="category", type="string", enum={"Node","React","Angular","JavaScript","Java","Fullstack PHP","Data Science","BBDD"}, example="React", description="Category of the resource"),
     *             @OA\Property(property="theme", type="string", enum={"All","Components","UseState & UseEffect","Eventos","Renderizado condicional","Listas","Estilos","Debugging","React Router"}, example="Components", description="Theme of the resource"),
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
     *                 "theme": {"The theme field is required.", "The selected theme is invalid."},
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
     * @OA\Get(
     *  path="/api/resources",
     *  summary="Get all resources",
     *  tags={"Resources"},
     *  description="return a list of all resources",
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
