<?php

namespace App\Http\Controllers;
use App\Http\Requests\UpdateResourceFormRequest;
use App\Models\Resource;


class ResourceEditController extends Controller
{
    public function update(UpdateResourceFormRequest $request, Resource $resource)
    {
        $validated = $request->validated();
        unset($validated['github_id']);
        
        $resource->update($validated);
        return response()->json($resource, 200);
    }
}
