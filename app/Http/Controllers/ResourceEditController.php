<?php

namespace App\Http\Controllers;
use App\Http\Requests\UpdateResourceFormRequest;
use App\Models\Resource;


class ResourceEditController extends Controller
{
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
     *             @OA\Property(property="url", type="string", format="url", example="https://laravelbestpractices.com")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Resource updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Resource")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object", example={
     *                 "title": {"The title field is required"},
     *                 "url": {"The url format is invalid"}
     *             })
     *         )
     *     )
     * )
    */

    public function update(UpdateResourceFormRequest $request, Resource $resource)
    {
        //Obtenemos los datos validados
        $validated = $request->validated();
        unset($validated['github_id']);
        //Actualizamos los datos
        $resource->update($validated);

        //Devolvemos la respuesta
        return response()->json($resource, 200);
    }
}
