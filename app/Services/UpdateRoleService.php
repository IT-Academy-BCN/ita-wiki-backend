<?php

declare (strict_types= 1);

namespace App\Services;

use App\Models\User;
use Illuminate\Http\JsonResponse;

class UpdateRoleService
{
    public function __invoke(array $validated)
    {
        $authorizedUser = User::where('github_id', $validated['authorized_github_id'])->firstOrFail();
        $user = User::where('github_id', $validated['github_id'])->firstOrFail();

        $roles = ['superadmin' => 4, 'admin' => 3, 'mentor' => 2, 'student' => 1];
        $authorizedLevel = $this->getRoleLevel($authorizedUser, $roles);
        $currentLevel = $this->getRoleLevel($user, $roles);
        $updateLevel = $roles[$validated['role']] ?? 0;

        if ($authorizedLevel <= $currentLevel) {
            return response()->json(['message' => 'No puedes actualizar un rol que ya es de orden igual o superior al tuyo.'], 403);
        }
        if ($authorizedLevel == 0 || $updateLevel == 0) {
            return response()->json(['message' => 'La petición contiene un rol inexistente.'], 422);
        }
        if ($updateLevel >= $authorizedLevel) {
            return response()->json(['message' => 'No puedes actualizar un rol a un orden igual o superior al tuyo.'], 403);
        }

        $user->syncRoles([$validated['role']]);

        return response()->json(['message' => "Se ha actualizado el Role {$validated['role']} para el github_id {$validated['github_id']}"], 200);
    }

    private function getRoleLevel($user, $roles): int
    {
        foreach ($roles as $role => $level) {
            if ($user->hasRole($role)) {
                return $level;
            }
        }
        return 0;
    }
}