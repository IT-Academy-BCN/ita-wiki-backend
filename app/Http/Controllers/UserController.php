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
        // $this->middleware('auth:api');
        // $this->middleware('check.permission:manage users')->only(['destroy']);
        // $this->middleware('check.permission:edit user roles')->only(['updateRole']);
        // $this->middleware('check.permission:view users')->only(['index']);
    }

    public function updateRole(UpdateUserRoleRequest $request, $id)
    {     
        try {
            if (!in_array($request->role, ['superadmin', 'mentor', 'admin', 'student'])) {
                return response()->json([
                    'message' => 'The selected role is invalid.',
                    'errors' => ['role' => ['The selected role is invalid.']]
                ], 422);
            }

            $userToUpdate = User::find($id);

            if (!$userToUpdate) {
                return response()->json(['message' => 'User not found.'], 404);
            }
            
            $userToUpdate->syncRoles([$request->role]);
            return response()->json(['message' => 'User role updated successfully', 'user' => $userToUpdate], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error updating role',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function profile(Request $request) { /* ... */
       
        try {
            $user = auth('api')->user();

            return response()->json([
            'message' => 'User profile retrieved successfully',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'github_id' => $user->github_id,
                'roles' => $user->roles->toArray()
            ]], 200);

        } catch (\Exception $e) {
            
            return response()->json([
            'error' => 'Error retrieving user profile',
            'message' => $e->getMessage()
            ], 500);
        }
    }

    public function index() { /* listar usuarios */
        try {

            return response()->json([
                'message' => 'Users retrieved successfully',
                'users' => User::with('roles')->get()
            ], 200);

        } catch (\Exception $e) {

            return response()->json([
                'error' => 'Error retrieving users',
                'message' => $e->getMessage()
            ], 500);
        }
        
    }
         

    public function destroy($id) { /* eliminar usuario */
    
        try {
            $userToDelete = User::find($id);

            if (!$userToDelete) {
                return response()->json(['message' => 'User not found.'], 404);
            }

            $userToDelete->delete();
            return response()->json(
                ['message' => 'User deleted successfully'], 200
            );

        } catch (\Exception $e) {
            
            return response()->json([
            'error' => 'Error deleting user',
            'message' => $e->getMessage()
            ], 500);
        }
    }

}
