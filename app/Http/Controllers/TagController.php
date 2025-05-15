<?php

declare (strict_types= 1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use App\Models\Tag;
use App\Models\Resource;

class TagController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/tags",
     *     summary="Get all tags",
     *     tags={"Tags"},
     *     description="Tags used in resources",
     *     @OA\Response(
     *         response=200,
     *         description="A list of tags",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Tag")
     *         )
     *     )
     * )
    */

    public function index(): JsonResponse
    {
        $tags = Tag::all();
        return response()->json($tags, 200);
    }

    /**
     * @OA\Get(
     *     path="/api/tags/frequency",
     *     summary="Get tag frequencies",
     *     tags={"Tags"},
     *     description="Frequencies of tags used in resources",
     *     @OA\Response(
     *         response=200,
     *         description="An object with tag names as keys and frequencies as values",
     *         @OA\JsonContent(
     *              type="object",
     *              example={
     *              "mongodb": 3,
     *              "tdd": 10
     *              }
     *         )
     *     )
     * )
    */

    public function getTagsFrequency(): JsonResponse
    {
        $frequencies = Resource::all()
            ->pluck('tags')
            ->filter()
            ->flatten()
            ->countBy()
            ->all();

        return response()->json($frequencies, 200);
    }

    /**
     * @OA\Get(
     *     path="/api/tags/category-frequency",
     *     summary="Get tag frequencies grouped by category",
     *     tags={"Tags"},
     *     description="Frequencies of tags used in resources grouoped by category",
     *     @OA\Response(
     *         response=200,
     *         description="An object with tag names as keys and frequencies as values",
     *         @OA\JsonContent(
     *              type="object",
     *              example={
     *              "Fullstack PHP": {
     *                  "mongodb": 3,
     *                  "tdd": 10
     *              },
     *              "React": {
     *                  "hooks": 2,
     *                  "dependencies": 3
     *              }
     *              }
     *         )
     *     )
     * )
    */

    public function getCategoryTagsFrequency(): JsonResponse
    {
        $categorizedResources = Resource::all()->groupBy('category');
        foreach ($categorizedResources as $category => $resources) {
            $categoryFrequencies = $resources
                ->pluck('tags')
                ->filter()
                ->flatten()
                ->countBy()
                ->all();
            $frequencies[$category] = $categoryFrequencies;
        }

        return response()->json($frequencies, 200);
    }
}
