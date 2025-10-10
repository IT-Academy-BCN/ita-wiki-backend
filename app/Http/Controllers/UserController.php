<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Users\UpdateUserRoleRequest;

class UserController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api');
        $this->middleware('check.permission:manage users')->only(['index', 'destroy']);
        $this->middleware('check.permission:edit user roles')->only(['updateRole']);
    }

    public function updateRole(UpdateUserRoleRequest $request, User $user)
    {     
        try {
            $user = auth('api')->user();

            if (!$user) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            if (!$user->hasRole('admin')) {
                return response()->json([
                    'error' => 'Forbidden',
                ], 403);
            }

            if (!in_array($request->role, ['superadmin', 'mentor', 'admin', 'student'])) {
                return response()->json([
                    'message' => 'The selected role is invalid.',
                    'errors' => ['role' => ['The selected role is invalid.']]
                ], 422);
            }

            $user->syncRoles([$request->role]);
            return response()->json(['message' => 'Role updated successfully', 'user' => $user], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error updating role',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function profile(Request $request) { /* ... */
        $user=auth('api')->user();
        
        return response()->json([
            'message' => 'User profile retrieved successfully',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'github_id' => $user->github_id,
                'roles' => $user->roles->toArray()
            ]
        ], 200);
    }

    public function index() { /* listar usuarios */
      
        return response()->json(['message' => 'Users retrieved successfully', 'users' => User::all()], 200);
    }

    public function destroy(User $user) { /* eliminar usuario */
    
        $user->delete();
        return response()->json(['message' => 'User deleted successfully'], 200);
    }

}
