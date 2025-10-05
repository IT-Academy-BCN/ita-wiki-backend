<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Spatie\Permission\Models\Role;
use App\Models\User;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
        $this->middleware('permission:view users')->only(['index', 'getUserRoles']);
        $this->middleware('permission:edit user roles')->only(['assignRole', 'removeRole']);
    }

    /**
     * @OA\Get(
     *     path="/api/roles",
     *     summary="Get all roles",
     *     tags={"Roles"},
     *     @OA\Response(response=200, description="List of all roles")
     * )
     */
    public function index(): JsonResponse
    {
        $roles = Role::where('guard_name', 'api')->get();
        
        return response()->json([
            'success' => true,
            'data' => $roles
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/roles/assign",
     *     summary="Assign role to user",
     *     tags={"Roles"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"user_id", "role"},
     *             @OA\Property(property="user_id", type="integer"),
     *             @OA\Property(property="role", type="string")
     *         )
     *     )
     * )
     */
    public function assignRole(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'required|exists:roles,name'
        ]);

        $user = User::findOrFail($request->user_id);
        $user->assignRole($request->role);

        return response()->json([
            'success' => true,
            'message' => 'Role assigned successfully'
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/users/{user}/roles",
     *     summary="Get user roles and permissions",
     *     tags={"Roles"},
     *     @OA\Parameter(name="user", in="path", required=true, @OA\Schema(type="integer"))
     * )
     */
    public function getUserRoles(User $user): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user->only(['id', 'name', 'github_id']),
                'roles' => $user->getRoleNames(),
                'permissions' => $user->getPermissionNames()
            ]
        ]);
    }
}