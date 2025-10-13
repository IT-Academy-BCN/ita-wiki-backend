<?php

declare (strict_types= 1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission, ?string $ownershipField = null): Response
    {
        $user = Auth::guard('api')->user();
        
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Check basic permission
        if (!$user->can($permission)) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        // Check ownership for "own" permissions
        if ($ownershipField && str_contains($permission, 'own')) {
            $resourceId = $request->route($ownershipField);
            $model = $this->getModelFromRoute($request);
            
            if ($model && $model->github_id !== $user->github_id) {
                return response()->json(['error' => 'Forbidden - Not your resource'], 403);
            }
        }

        return $next($request);
    }
    
    private function getModelFromRoute(Request $request)
    {
        // Logic to get model from route parameters
        if ($request->route('resource')) {
            return \App\Models\Resource::find($request->route('resource'));
        }
        
        if ($request->route('technicalTest')) {
            return \App\Models\TechnicalTest::find($request->route('technicalTest'));
        }
        
        return null;
    }
}
