<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Exercises\StoreExerciseRequest;
use App\Http\Requests\Exercises\UpdateExerciseRequest;
use App\Models\Exercise;
use Illuminate\Http\JsonResponse;

class ExerciseController extends Controller
{
    public function __construct()
    {
    }

    /**
     * @OA\Get(
     *     path="/api/exercises",
     *     summary="List all exercises",
     *     tags={"Exercises"},
     *     @OA\Parameter(
     *         name="technical_test_id",
     *         in="query",
     *         description="Filter by technical test ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="List of exercises")
     * )
     */
    public function index(): JsonResponse
    {
        $query = Exercise::query();

        if (request()->has('technical_test_id')) {
            $query->where('technical_test_id', request('technical_test_id'));
        }

        $exercises = $query->orderBy('order')->get();

        return response()->json([
            'data' => $exercises
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/exercises",
     *     summary="Create a new exercise",
     *     tags={"Exercises"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"technical_test_id","title"},
     *             @OA\Property(property="technical_test_id", type="integer", example=1),
     *             @OA\Property(property="title", type="string", example="Crear clase Person"),
     *             @OA\Property(property="description", type="string", example="Implemente una clase Person con..."),
     *             @OA\Property(property="order", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(response=201, description="Exercise created successfully")
     * )
     */
    public function store(StoreExerciseRequest $request): JsonResponse
    {
        $exercise = Exercise::create($request->validated());

        return response()->json([
            'message' => 'Exercise created successfully',
            'data' => $exercise
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/exercises/{id}",
     *     summary="Get an exercise",
     *     tags={"Exercises"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Exercise details")
     * )
     */
    public function show(Exercise $exercise): JsonResponse
    {
        return response()->json([
            'data' => $exercise
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/exercises/{id}",
     *     summary="Update an exercise",
     *     tags={"Exercises"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Exercise updated successfully")
     * )
     */
    public function update(UpdateExerciseRequest $request, Exercise $exercise): JsonResponse
    {
        $exercise->update($request->validated());

        return response()->json([
            'message' => 'Exercise updated successfully',
            'data' => $exercise
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/exercises/{id}",
     *     summary="Delete an exercise",
     *     tags={"Exercises"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Exercise deleted successfully")
     * )
     */
    public function destroy(Exercise $exercise): JsonResponse
    {
        $exercise->delete();

        return response()->json([
            'success' => true,
            'message' => 'Exercise deleted successfully'
        ]);
    }
}
