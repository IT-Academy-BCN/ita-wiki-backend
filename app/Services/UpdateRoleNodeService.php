<?php

declare(strict_types=1);

namespace App\Services;


use App\Models\RoleNode;
use Illuminate\Http\JsonResponse;

class UpdateRoleNodeService
{

    public function __invoke(array $validated)
    {
        $authorizedRole = (RoleNode::where('node_id', $validated['authorized_node_id'])->firstOrFail())->role;

        $nodeId       = $validated['node_id'];
        $roleToUpdate = $validated['role'];

        return $this->processRoleUpdate($authorizedRole, $roleToUpdate, $nodeId);
    }

    protected function getRoleLevel(string $role): int
    {
        $roles = ['superadmin' => 4, 'admin' => 3, 'mentor' => 2, 'student' => 1];
        return $roles[$role] ?? 0; // returns 0 if the role is not found
    }

    protected function processRoleUpdate(string $authorizedRole, string $roleToUpdate, string $nodeId): JsonResponse
    {
        $authorizedLevel = $this->getRoleLevel($authorizedRole);
        $currentLevel    = $this->getRoleLevel(RoleNode::where('node_id', $nodeId)->firstOrFail()->role);
        $updateLevel     = $this->getRoleLevel($roleToUpdate);

        if ($authorizedLevel <= $currentLevel) {
            return response()->json(['message' => 'You cannot update a role that is equal or higher than your own.'], 403);
        }

        if ($authorizedLevel == 0 || $updateLevel == 0) {
            return response()->json(['message' => 'The request contains an invalid role.'], 422);
        }

        if ($updateLevel >= $authorizedLevel) {
            return response()->json(['message' => 'You cannot update a role to an order equal or higher than your own.'], 403);
        }

        $role = RoleNode::where('node_id', $nodeId)->update([
            'role' => $roleToUpdate,
        ]);

        if (! $role) {
            return response()->json(['message' => "Could not update Role {$roleToUpdate} for node_id {$nodeId}"], 500);
        }

        return response()->json(['message' => "Role {$roleToUpdate} updated for node_id {$nodeId}"], 200);
    }

}
