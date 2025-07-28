<?php

declare (strict_types= 1);

namespace App\Services;

use App\Http\Requests\CreateRoleRequest;
use App\Models\OldRole;
use Illuminate\Http\JsonResponse;

class OldCreateRoleService
{
    public function __invoke(array $validated)
    {
        $authorizedRole = (OldRole::where('github_id', $validated['authorized_github_id'])->first())->role;

        $githubId = $validated['github_id'];
        $roleToCreate = $validated['role'];

        return $this->processRoleCreation($authorizedRole, $roleToCreate, $githubId);
    }

    protected function getRoleLevel(string $role): int
    {
        $roles = ['superadmin'=> 4,'admin'=> 3,'mentor'=> 2,'student'=> 1];
        return $roles[$role] ?? 0; // it returns 0 if the role is not found
    }    

    protected function processRoleCreation(string $authorizedRole, string $roleToCreate, int $githubId): JsonResponse
    {
        $authorizedLevel = $this->getRoleLevel($authorizedRole);
        $createLevel = $this->getRoleLevel($roleToCreate);

        if ($authorizedLevel ==  0 || $createLevel == 0) {
            return response()->json(['message' => 'La petición contiene un rol inexistente.'], 422);
        }

        if ($createLevel >= $authorizedLevel) {
            return response()->json(['message' => 'No puedes crear un rol igual o superior al tuyo.'], 403);
        }

        $role = OldRole::create([
            'github_id' => $githubId,
            'role' => $roleToCreate,
        ]);

        if(!$role){
            return response()->json(['message'=> "No se pudo crear el Rol {$roleToCreate} para el github_id {$githubId}"],500);
        }

        return response()->json(['message' => "Se ha creado el Role {$roleToCreate} para el github_id {$githubId}"], 201);
    }

}
