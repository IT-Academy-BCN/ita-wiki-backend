<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateRoleRequest;
use Illuminate\Http\Request;
use App\Models\Role;
use Illuminate\Http\JsonResponse;

class RoleController extends Controller
{
    const ROLE_SUPERADMIN = 'superadmin';
    const ROLE_ADMIN = 'admin';
    const ROLE_MENTOR = 'mentor';
    const ROLE_STUDENT = 'student';

    protected $roleHierarchy = [
        self::ROLE_SUPERADMIN => 4,
        self::ROLE_ADMIN => 3,
        self::ROLE_MENTOR => 2,
        self::ROLE_STUDENT => 1,
    ];
    
    protected function getRoleLevel(string $role): int
    {
        return $this->roleHierarchy[$role] ?? 0; // it returns 0 if the role is not found
    }

    /**
     * @OA\Post(
     *     path="/api/roles",
     *     summary="Create a new role",
     *     description="Allows an authorized user to create a new role for a specific GitHub ID.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"github_id", "role", "authorized_github_id"},
     *             @OA\Property(property="github_id", type="integer", example=12345, description="GitHub ID of the user to assign the role"),
     *             @OA\Property(property="role", type="string", example="mentor", description="Role to be assigned"),
     *             @OA\Property(property="authorized_github_id", type="integer", example=1, description="GitHub ID of the user making the request (must have permissions)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Role created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Rol creado con éxito.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized: Cannot create a role equal or higher than your own",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="No puedes crear un rol igual o superior al tuyo.")
     *         )
     *     )
     * )
     */
    
    public function createRole(CreateRoleRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $authorizedRole = (Role::where('github_id', $validated['authorized_github_id'])->first())->role;

        $githubId = $validated['github_id'];
        $roleToCreate = $validated['role'];

        return $this->processRoleCreation($authorizedRole, $roleToCreate, $githubId);
    }

    protected function processRoleCreation(string $authorizedRole, string $roleToCreate, string $githubId): JsonResponse
    {
        $authorizedLevel = $this->getRoleLevel($authorizedRole);
        $createLevel = $this->getRoleLevel($roleToCreate);

        if ($createLevel >= $authorizedLevel) {
            return response()->json(['message' => 'No puedes crear un rol igual o superior al tuyo.'], 403);
        }

        Role::create([
            'github_id' => $githubId,
            'role' => $roleToCreate,
        ]);

        return response()->json(['message' => 'Rol creado con éxito.'], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/users/user-signedin-as",
     *     summary="Retrieve a role by GitHub ID",
     *     description="Fetches a role using the provided GitHub ID.",
     *     @OA\Parameter(
     *         name="github_id",
     *         in="query",
     *         required=true,
     *         description="GitHub ID of the user",
     *         @OA\Schema(type="integer", example=12345)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Role found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Role found."),
     *             @OA\Property(
     *                 property="role",
     *                 type="object",
     *                 ref="#/components/schemas/Role"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Role not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Role not found.")
     *         )
     *     )
     * )
     */

    public function getRoleByGithubId(Request $request)
    {
        $validated = $request->validate([
            'github_id' => 'required|integer'
        ]);

        $role = Role::where('github_id', $validated['github_id'])->first();

        if (!$role) {
            return response()->json([
                'message' => 'Role not found.'
            ], 404);
        }

        return response()->json([
            'message' => 'Role found.',
            'role' => [
                'github_id' => $role->github_id,
                'role' => $role->role
            ]
        ], 200);
    }
}
