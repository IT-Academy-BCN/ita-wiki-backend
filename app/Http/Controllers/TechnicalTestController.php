<?php

namespace App\Http\Controllers;
use App\Http\Requests\IndexTechnicalTestRequest;
use App\Http\Requests\StoreTechnicalTestRequest;
use App\Models\TechnicalTest;

use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Technical Tests",
 *     description="API Endpoints para gestión de pruebas técnicas"
 * )
 */

/* Basic index() to provide data to Frontend developers. 
*  to be completed whith filters when basic listing feature is 
*  tested and working
*/
class TechnicalTestController extends Controller
{
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
                'available_languages' => ['PHP', 'JavaScript', 'Java', 'React', 'TypeScript', 'Python', 'SQL'],
                'applied_filters' => $request->only(['search', 'language' ,'description'])        
            ] 
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/technicaltests",
     *     summary="Crear una nueva prueba técnica",
     *     description="Crea una nueva prueba técnica en el sistema",
     *     tags={"Technical Tests"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Datos de la prueba técnica",
     *         @OA\JsonContent(
     *             required={"title", "language"},
     *             @OA\Property(property="title", type="string", minLength=5, maxLength=255, example="Examen PHP Básico"),
     *             @OA\Property(property="language", type="string", enum={"PHP", "JavaScript", "Java", "React", "TypeScript", "Python", "SQL"}, example="PHP"),
     *             @OA\Property(property="description", type="string", maxLength=1000, example="Examen sobre conceptos básicos de PHP", nullable=true),
     *             @OA\Property(property="tags", type="array", @OA\Items(type="string"), example={"php", "backend"}, nullable=true)
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
        $technicalTest = TechnicalTest::create([
            'title' => $request->title,
            'language' => $request->language,
            'description' => $request->description,
            'tags' => $request->tags,
            // github_id lo agregaremos después con autenticación
        ]);

        return response()->json([
            'message' => 'Technical test created successfully',
            'data' => $technicalTest
        ], 201);
    }

   
}