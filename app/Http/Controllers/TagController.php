<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Tag;
use App\Models\Resource;
use Illuminate\Http\JsonResponse;

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
     *         description="Successful operation"
     *     )
     * )
     */
    public function index(): JsonResponse
    {
        $tags = Tag::all();

        return response()->json([
            'message' => 'Tags retrieved successfully',
            'data' => $tags
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/tags/frequency",
     *     summary="Get tag frequencies",
     *     tags={"Tags"},
     *     description="Frequencies of tags used in resources",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation"
     *     )
     * )
     */
    public function getTagsFrequency(): JsonResponse
    {
        $resources = Resource::all();
        $tagFrequencies = [];

        foreach ($resources as $resource) {
            if (is_array($resource->tags)) {
                foreach ($resource->tags as $tag) {
                    $tagFrequencies[$tag] = ($tagFrequencies[$tag] ?? 0) + 1;
                }
            }
        }

        return response()->json([
            'message' => 'Tag frequencies retrieved successfully',
            'data' => $tagFrequencies
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/tags/category-frequency",
     *     summary="Get tag frequencies grouped by category",
     *     tags={"Tags"},
     *     description="Frequencies of tags used in resources grouped by category",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation"
     *     )
     * )
     */
    public function getCategoryTagsFrequency(): JsonResponse
    {
        $resources = Resource::all();
        $categoryTagFrequencies = [];

        foreach ($resources as $resource) {
            $category = $resource->category;

            if (!isset($categoryTagFrequencies[$category])) {
                $categoryTagFrequencies[$category] = [];
            }

            if (is_array($resource->tags)) {
                foreach ($resource->tags as $tag) {
                    $categoryTagFrequencies[$category][$tag] = ($categoryTagFrequencies[$category][$tag] ?? 0) + 1;
                }
            }
        }

        return response()->json([
            'message' => 'Category tag frequencies retrieved successfully',
            'data' => $categoryTagFrequencies
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/tags/by-category",
     *     summary="Get tag IDs grouped by category",
     *     tags={"Tags"},
     *     description="Returns the IDs of tags used in resources, grouped by resource category",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation"
     *     )
     * )
     */
    public function getCategoryTagsId(): JsonResponse
    {
        $resources = Resource::all();
        $categoryTags = [];

        foreach ($resources as $resource) {
            $category = $resource->category;

            if (!isset($categoryTags[$category])) {
                $categoryTags[$category] = [];
            }

            if (is_array($resource->tags)) {
                foreach ($resource->tags as $tagName) {
                    $tag = Tag::where('name', $tagName)->first();
                    if ($tag && !in_array($tag->id, $categoryTags[$category])) {
                        $categoryTags[$category][] = $tag->id;
                    }
                }
            }
        }

        return response()->json([
            'message' => 'Category tags retrieved successfully',
            'data' => $categoryTags
        ]);
    }
}