<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\TagNode;
use App\Models\ResourceNode;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;

class TagNodeController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/tags-node",
     *     summary="Get all tags (node_id version)",
     *     tags={"TagsNode"},
     *     description="Tags used in resources_node",
     *     @OA\Response(
     *         response=200,
     *         description="A list of tags",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/TagNode")
     *         )
     *     )
     * )
     */
    public function index()
    {
        $tags = TagNode::all();
        return response()->json($tags, 200);
    }

    /**
     * @OA\Get(
     *     path="/api/tags-node/frequency",
     *     summary="Get tag-node frequencies",
     *     tags={"TagsNode"},
     *     description="Frequencies of tags used in resources_node",
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
    public function getTagsFrequency()
    {
        $frequencies = ResourceNode::all()
            ->pluck('tags')
            ->filter()
            ->flatten()
            ->countBy()
            ->all();

        return response()->json($frequencies, 200);
    }

    /**
     * @OA\Get(
     *     path="/api/tags-node/category-frequency",
     *     summary="Get tag-node frequencies grouped by category",
     *     tags={"TagsNode"},
     *     description="Frequencies of tags used in resources_node grouped by category",
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
    public function getCategoryTagsFrequency()
    {
        $categorizedResources = ResourceNode::all()->groupBy('category');
        $frequencies = [];

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

    /**
     * @OA\Get(
     *     path="/api/tags-node/by-category",
     *     summary="Get tag-node IDs grouped by category",
     *     tags={"TagsNode"},
     *     description="Returns the IDs of tags used in resources_node, grouped by resource_node category. This does not require a pivot table and is based on matching tag names from a JSON column.",
     *     @OA\Response(
     *         response=200,
     *         description="An object with category names as keys and arrays of tag-node IDs as values",
     *         @OA\JsonContent(
     *              type="object",
     *              example={
     *                  "Fullstack PHP": {1, 5, 6},
     *                  "React": {3, 7}
     *              }
     *         )
     *     )
     * )
     */
    public function getCategoryTagsId(): JsonResponse
    {
        $resourcesByCategory = ResourceNode::all()->groupBy('category');
        $tagsByCategory = [];

        foreach ($resourcesByCategory as $category => $resources) {
            $tagNames = $resources
                ->pluck('tags')
                ->filter()
                ->flatten()
                ->unique()
                ->values();

            $tagIds = TagNode::whereIn('name', $tagNames)->pluck('id')->all();
            $tagsByCategory[$category] = $tagIds;
        }

        return response()->json($tagsByCategory, 200);
    }
}
