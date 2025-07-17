<?php

declare(strict_types=1);

namespace App\Http\Controllers;
use App\Http\Requests\CreateRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Services\CreateRoleService;
use App\Services\UpdateRoleService;
use Illuminate\Http\Request;
use App\Models\Role;
use Illuminate\Http\JsonResponse;
use App\Rules\GithubIdRule;
use Illuminate\Support\Facades\Config;

/**
 * @deprecated 
 */
class RoleController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/roles",
     *     summary="Create a new role",
     *     tags={"Roles"},
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
     *     ),
     *    @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="La petición contiene un rol inexistente.")
     *         )
     *     )
     * )
     */
    
    public function createRole(CreateRoleRequest $request, CreateRoleService $createRoleService): JsonResponse
    {
        return $createRoleService($request->validated());
    }

    /**
     * @OA\Put(
     *     path="/api/roles",
     *     summary="Update an existing role",
     *     tags={"Roles"},
     *     description="Allows an authorized user to update the role of a specific GitHub ID.",
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
     *         response=200,
     *         description="Role updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Rol actualizado con éxito.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized: Cannot update a role of user of equal or higher role rank or make an update to an equal or higher rank than your own",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="No puedes actualizar un rol que ya es de orden igual o superior al tuyo.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="La petición contiene un rol inexistente.")
     *         )
     *     )
     * )
    */
    

    public function updateRole(UpdateRoleRequest $request, UpdateRoleService $updateRoleService): JsonResponse
    {
        return $updateRoleService($request->validated());
    }

    /**
    * @OA\Post(
    *     path="/api/login",
    *     summary="Retrieve a role by GitHub ID",
    *     tags={"Roles"},
    *     description="Fetches a role using the provided GitHub ID. If the role does not exist, it returns an error.",
    *     @OA\Parameter(
    *         name="github_id",
    *         in="query",
    *         description="GitHub ID of the user",
    *         required=true,
    *         @OA\Schema(type="integer", example=6729608)
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
    *             @OA\Property(property="message", type="string", example="Role not found."),
    *             @OA\Property(
    *                 property="role",
    *                 type="object",
    *                 nullable=true,
    *                 example=null
    *             )
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

    /**
    * @OA\Put(
    *     path="/api/feature-flags/role-self-assignment",
    *     summary="Role Self Assignment",
    *     tags={"Roles"},
    *     description="Updates a role using the provided GitHub ID. If the role does not exist, it returns an error.",
    *     @OA\RequestBody(
    *         required=true,
    *         @OA\JsonContent(
    *             type="object",
    *             required={"github_id", "role"},
    *             @OA\Property(property="github_id", type="integer", example=6729608),
    *             @OA\Property(property="role", type="string", example="admin")
    *         )
    *     ),
    *     @OA\Response(
    *         response=200,
    *         description="Role updated successfully",
    *         @OA\JsonContent(
    *             type="object",
    *             @OA\Property(property="message", type="string", example="El Rol se ha actualizado."),
    *             @OA\Property(
    *                 property="role",
    *                 type="object",
    *                 @OA\Property(property="github_id", type="integer", example=6729608),
    *                 @OA\Property(property="role", type="string", example="admin")
    *             )
    *         )
    *     ),
    *     @OA\Response(
    *         response=403,
    *         description="Unauthorized: The feature flag for role self-assignment is disabled",
    *         @OA\JsonContent(
    *             type="object",
    *             @OA\Property(property="message", type="string", example="La autoasignación de roles ha sido desactivada.")
    *         )
    *     ),
    *     @OA\Response(
    *         response=422,
    *         description="Validation error",
    *         @OA\JsonContent(
    *             type="object",
    *             @OA\Property(property="message", type="string", example="La petición contiene un github_id inexistente en nuestro sistema.")
    *         )
    *     )
    * )
    */


    // Feature Flag : Role Self Assignment
    public function roleSelfAssignment(Request $request)
    {
        $validated = $request->validate([
            'github_id' => 'required|integer|exists:users,github_id',
            'role' => ['required', 'string', 'in:superadmin,mentor,admin,student']
        ]);

        $user = \App\Models\User::where('github_id', $validated['github_id'])->first();

        if (!$user) {
            return response()->json([
                'message' => 'La petición contiene un github_id inexistente en nuestro sistema.'
            ], 404);
        }

        if (!\Illuminate\Support\Facades\Config::get('feature_flags.allow_role_self_assignment')) {
            return response()->json(['message' => 'La autoasignación de roles ha sido desactivada.'], 403);
        }

        $user->syncRoles([$validated['role']]);

        return response()->json([
            'message' => 'El Rol se ha actualizado.',
            'role' => [
               'github_id' => $user->github_id,
               'role' => $validated['role']
            ]
        ], 200);
    }
}