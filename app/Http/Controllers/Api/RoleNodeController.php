<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateRoleNodeRequest;
use App\Http\Requests\UpdateRoleNodeRequest;
use App\Models\RoleNode;
use App\Services\CreateRoleNodeService;
use App\Services\UpdateRoleNodeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class RoleNodeController extends Controller
{

    /**
     * @OA\Post(
     *     path="/api/roles-node",
     *     summary="Create a new role using node_id",
     *     tags={"RolesNode"},
     *     description="Allows an authorized user to create a new role for a specific GitHub node_id.
     *
     *     Note: The 'node_id' field must be unique. If you receive a 'has already been taken' error, please use a different value for 'node_id'.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"node_id", "role", "authorized_node_id"},
     *             @OA\Property(
     *                 property="node_id",
     *                 type="string",
     *                 example="MDQ6VXNlcjUNIQUE=",
     *                 description="GitHub node_id of the user to assign the role. Must be unique and not already exist in the database."
     *             ),
     *             @OA\Property(
     *                 property="role",
     *                 type="string",
     *                 enum={"superadmin", "admin", "mentor", "student"},
     *                 example="mentor",
     *                 description="Role to be assigned"
     *             ),
     *             @OA\Property(
     *                 property="authorized_node_id",
     *                 type="string",
     *                 example="MDQ6VXNlcjE=",
     *                 description="GitHub node_id of the user making the request (must have permissions, e.g. superadmin)"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Role created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Role created successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized: Cannot create a role equal or higher than your own",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="You cannot create a role equal or higher than your own.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="authorized_node_id", type="array", @OA\Items(type="string"), example={"The selected authorized node id is invalid."}),
     *             @OA\Property(property="message", type="string", example="The request contains an invalid role.")
     *         )
     *     )
     * )
     */

    public function createRoleNode(CreateRoleNodeRequest $request, CreateRoleNodeService $createRoleNodeService): JsonResponse
    {
        return $createRoleNodeService($request->validated());
    }

    /**
     * @OA\Put(
     *     path="/api/roles-node",
     *     summary="Update an existing role using node_id",
     *     tags={"RolesNode"},
     *     description="Allows an authorized user to update the role of a specific GitHub node_id.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"node_id", "role", "authorized_node_id"},
     *             @OA\Property(property="node_id", type="string", example="MDQ6VXNlcjY3Mjk2MDg=", description="GitHub node_id of the user to update the role"),
     *             @OA\Property(property="role", type="string", enum={"superadmin", "admin", "mentor", "student"}, example="mentor", description="Role to be assigned"),
     *             @OA\Property(property="authorized_node_id", type="string", example="MDQ6VXNlcjE=", description="GitHub node_id of the user making the request (must have permissions, e.g. superadmin)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Role updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Role updated successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized: Cannot update a role of user of equal or higher role rank or make an update to an equal or higher rank than your own",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="You cannot update a role that is equal or higher than your own.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="The request contains an invalid role.")
     *         )
     *     )
     * )
     */
    public function updateRoleNode(UpdateRoleNodeRequest $request, UpdateRoleNodeService $updateRoleNodeService): JsonResponse
    {
        return $updateRoleNodeService($request->validated());
    }

    /**
     * @OA\Post(
     *     path="/api/login-node",
     *     summary="Retrieve a role by GitHub node_id",
     *     tags={"RolesNode"},
     *     description="Fetches a role using the provided GitHub node_id. If the role does not exist, it returns an error.",
     *     @OA\Parameter(
     *         name="node_id",
     *         in="query",
     *         description="GitHub node_id of the user",
     *         required=true,
     *         @OA\Schema(type="string", example="MDQ6VXNlcjY3Mjk2MDg=")
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
     *                 ref="#/components/schemas/RoleNode"
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
    public function getRoleByNodeId(Request $request)
    {
        $validated = $request->validate([
            'node_id' => 'required|string',
        ]);

        $role = \App\Models\RoleNode::where('node_id', $validated['node_id'])->first();

        if (! $role) {
            return response()->json([
                'message' => 'Role not found.',
            ], 404);
        }
        return response()->json([
            'message' => 'Role found.',
            'role'    => [
                'node_id' => $role->node_id,
                'role'    => $role->role,
            ],
        ], 200);
    }

    /**
     * @OA\Put(
     *     path="/api/feature-flags/role-self-assignment-node",
     *     summary="Role Self Assignment Node",
     *     tags={"RolesNode"},
     *     description="Updates a role using the provided GitHub node_id. If the role does not exist, it returns an error.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"node_id", "role"},
     *             @OA\Property(property="node_id", type="string", example="MDQ6VXNlcjY3Mjk2MDg="),
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
     *                 @OA\Property(property="node_id", type="string", example="MDQ6VXNlcjY3Mjk2MDg="),
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
     *             @OA\Property(property="message", type="string", example="La petición contiene un node_id inexistente en nuestro sistema.")
     *         )
     *     )
     * )
     */
    public function roleSelfAssignmentNode(Request $request)
    {

        $validated = $request->validate([
            'node_id' => ['required', 'string', new \App\Rules\NodeIdRule()],
            'role'    => ['required', 'string', 'in:superadmin,mentor,admin,student'],
        ]);

        $role = \App\Models\RoleNode::where('node_id', $validated['node_id'])->first();

        if (! $role) {
            return response()->json([
                'message' => 'The request contains a node_id that does not exist in our system.',
            ], 404);
        }

        // Checking a global feature flag
        if (! Config::get('feature_flags.allow_role_self_assignment')) {
            return response()->json(['message' => 'Role self-assignment has been disabled.'], 403);
        }

        $role->role = $validated['role'];
        $role->save();

        return response()->json([
            'message' => 'The role has been updated.',
            'role'    => [
                'node_id' => $role->node_id,
                'role'    => $role->role,
            ],
        ], 200);

    }

}
