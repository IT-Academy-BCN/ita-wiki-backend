<?php

namespace App\Http\Controllers;
use App\Http\Requests\IndexTechnicalTestRequest;
use App\Http\Requests\StoreTechnicalTestRequest;
use App\Models\TechnicalTest;
use App\Enums\LanguageEnum;

/**
 * @OA\Tag(
 *     name="Technical Tests",
 *     description="API Endpoints for Technical Tests"
 * )
 */

class TechnicalTestController extends Controller
{
    /**
     * @OA\Get(
     *      path="/api/technicaltests",
     *      summary = "List technical tests with or w/out filters",
     *      description="Lists available technical test with options to filter by language and any part of the title and the description",
     *      operationId="getTechnicalTests",
     *      tags={"Technical Tests"},
     *    
     *      @OA\Parameter(
     *          name="search",
     *          in="query",
     *          description="Search by title",
     *          required=false,
     *          @OA\Schema(type="String", example="Basic PHP exam"),
     *      ),
     *      @OA\Parameter(
     *          name="language",
     *          in="query",
     *          description="Filter by language",
     *          required=false,
     *          @OA\Schema(type="String", enum={"PHP", "JavaScript", "Java", "React", "TypeScript", "Python", "SQL"}),
     *      ),
     *      @OA\Parameter(
     *          name="description",
     *          in="query",
     *          description="Search by any part of the description",
     *          required=false,
     *          @OA\Schema(type="String", example="SOLID"),
     *      ),
     * 
     *      @OA\Response(
     *          response=200,
     *          description="Technical Tests successfully listed",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="data",
     *                  type="array",
     *                  @OA\Items(
     *                      type="object",
     *                      @OA\Property(property="id", type="integer", example=123),
     *                      @OA\Property(property="github_id", type="integer", example=6729608, nullable=true),
     *                      @OA\Property(property="node_id", type="string", example="MDQ6VXNlcjY3Mjk2MDg=", nullable=true),
     *                      @OA\Property(property="title", type="string", example="Basic PHP exam"),
     *                      @OA\Property(property="language", type="string", example="PHP"),
     *                      @OA\Property(property="description", type="string", example="Solve this exam paying atention to SOLID principles"),
     *                      @OA\Property(property="file_path", type="string", example="technical_tests/exam.pdf", nullable=true),
     *                      @OA\Property(property="file_original_name", type="string", example="exam.pdf", nullable=true),
     *                      @OA\Property(property="file_size", type="integer", example=1024, nullable=true),
     *                      @OA\Property(property="tags", type="array", 
     *                              @OA\Items(type="string"), example={"php", "backend"}, nullable=true),
     *                      @OA\Property(property="bookmark_count", type="integer", example=5),
     *                      @OA\Property(property="like_count", type="integer", example=12),
     *                      @OA\Property(property="created_at", type="string", format="date-time"),
     *                      @OA\Property(property="updated_at", type="string", format="date-time"),
     *                      @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true)
     *                  )         
     *              ),
     *              @OA\Property(property="message", type="string", nullable=true, example="No se han encontrado tests con esos criterios"),
     *              @OA\Property(
     *                  property="filters",
     *                  type="object",
     *                  @OA\Property(property="available_languages", type="array", @OA\Items(type="string"), example={"PHP", "JavaScript", "Java", "React", "TypeScript", "Python", "SQL"}),
     *                  @OA\Property(property="applied_filters", type="object", example={"search": "Examen de PHP básico", "language": "PHP"})
     *              )
     *          )
     *      ),
     * 
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                  property="message", 
     *                  type="string", 
     *                  example="El título no debe exceder los 255 caracteres."
     *             ),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                      property="search", 
     *                      type="array", 
     *                      @OA\Items(type="string"), 
     *                      example={"El título no debe exceder los 255 caracteres."}
     *                 ),
     *                 @OA\Property(
     *                      property="language", 
     *                      type="array", 
     *                      @OA\Items(type="string"), 
     *                      example={"El lenguaje seleccionado no es válido."}
     *                 ),
     *                 @OA\Property(
     *                      property="description", 
     *                      type="array", 
     *                      @OA\Items(type="string"), 
     *                      example={"El campo descripción no debe exceder los 1000 caracteres."}
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Internal server error")
     *         )
     *     )
     *)
     * 
     */
    public function index(IndexTechnicalTestRequest $request)
    {
        $query = TechnicalTest::query();

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('language')) {
            $query->where('language', $request->language);
        }

        if ($request->filled('description')) {
            $query->where('description', 'like', '%' . $request->description . '%');
        }
         
        $technicalTests = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'data' => $technicalTests,
            'message' => $technicalTests->isEmpty()? 'No se han encontrado tests con esos criterios' : null,
            'filters' => [
                'available_languages' => LanguageEnum::values(),
                'applied_filters' => $request->only(['search', 'language' ,'description'])        
            ] 
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/technicaltests",
     *     summary="Crear una nueva prueba técnica",
     *     description="Crea una nueva prueba técnica en el sistema. Permite adjuntar un archivo PDF opcional.",
     *     tags={"Technical Tests"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Datos de la prueba técnica y archivo opcional",
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"title", "language"},
     *                 @OA\Property(property="title", type="string", minLength=5, maxLength=255, example="Examen PHP Básico"),
     *                 @OA\Property(property="language", type="string", enum={"PHP", "JavaScript", "Java", "React", "TypeScript", "Python", "SQL"}, example="PHP"),
     *                 @OA\Property(property="description", type="string", maxLength=1000, example="Examen sobre conceptos básicos de PHP", nullable=true),
     *                 @OA\Property(property="tags", type="array", @OA\Items(type="string"), example={"php", "backend"}, nullable=true),
     *                 @OA\Property(property="file", type="string", format="binary", description="Archivo PDF opcional")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Prueba técnica creada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Technical test created successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=123),
     *                 @OA\Property(property="title", type="string", example="Examen PHP Básico"),
     *                 @OA\Property(property="language", type="string", example="PHP"),
     *                 @OA\Property(property="description", type="string", example="Examen sobre conceptos básicos de PHP"),
     *                 @OA\Property(property="tags", type="array", @OA\Items(type="string"), example={"php", "backend"}),
     *                 @OA\Property(property="file_path", type="string", example="technical-tests/1625678900_prueba.pdf"),
     *                 @OA\Property(property="file_original_name", type="string", example="prueba.pdf"),
     *                 @OA\Property(property="file_size", type="integer", example=102400),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The title field is required."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function store(StoreTechnicalTestRequest $request)
    {
        $data = [
            'title' => $request->title,
            'language' => $request->language,
            'description' => $request->description,
            'tags' => $request->tags,
            // github_id lo agregaremos después con autenticación
        ];

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('technical-tests', $fileName, 'local');
            
            $data['file_path'] = $filePath;
            $data['file_original_name'] = $file->getClientOriginalName();
            $data['file_size'] = $file->getSize();
        }

        $technicalTest = TechnicalTest::create($data);

        return response()->json([
            'message' => 'Technical test created successfully',
            'data' => $technicalTest
        ], 201);
    }

   
}