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
        // $this->middleware('auth:api');
        // $this->middleware('permission:view users')->only(['index', 'getUserRoles']);
        // $this->middleware('permission:edit user roles')->only(['assignRole', 'removeRole']);
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
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Role assigned successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Role assigned successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Unauthorized")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Forbidden")
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
     *     @OA\Parameter(
     *         name="user", 
     *         in="path", 
     *         required=true, 
     *         @OA\Schema(type="integer"),
     *         description="User ID"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User roles and permissions retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="user",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="John Doe"),
     *                     @OA\Property(property="github_id", type="string", example="999999999")
     *                 ),
     *                 @OA\Property(
     *                     property="roles",
     *                     type="array",
     *                     @OA\Items(type="string"),
     *                     example={"student", "mentor"}
     *                 ),
     *                 @OA\Property(
     *                     property="permissions",
     *                     type="array",
     *                     @OA\Items(type="string"),
     *                     example={"view resources", "create resources", "edit own resources"}
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Unauthorized")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Forbidden")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="User not found")
     *         )
     *     )
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