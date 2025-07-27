<?php

declare(strict_types=1);

namespace App\Services;


use App\Models\RoleNode;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\CreateRoleNodeRequest;

class CreateRoleNodeService
{
   
    public function __invoke(array $data): JsonResponse
    {
    $authorizedRole = RoleNode::where('node_id', $data['authorized_node_id'])->first();

        if (!$authorizedRole) {
            return response()->json([
                'message' => 'Unauthorized.'
            ], 403);
        }

        $roleHierarchy = ['superadmin' => 4, 'admin' => 3, 'mentor' => 2, 'student' => 1];
        
        if ($roleHierarchy[$data['role']] >= $roleHierarchy[$authorizedRole->role]) {
            return response()->json([
                'message' => 'You cannot create a role equal or higher than your own.'
            ], 403);
        }

        RoleNode::create([
            'node_id' => $data['node_id'],
            'role' => $data['role'],
        ]);

        return response()->json(['message' => 'Role created successfully.'], 201);
    }
    

    
}

