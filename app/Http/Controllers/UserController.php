<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Laravel\Passport\HasApiTokens;

class UserController extends Controller
{
    public function updateRole(Request $request, User $user) 
    {
        return response()->json(['message' => 'Role updated',
            'role' => $user->role
        ], 200);

    }

    public function profile(Request $request) {
        return response()->json(['message' => 'User profile accessed successfully',
            'user' => $request->user()
        ], 200);
    }

    public function index() {
        return response()->json(['message' => 'User list accessed successfully',
            'users' => User::all()
        ], 200);
    }

    public function destroy(User $user) {
           $user->delete();
           return response()->json(['message' => 'User deleted successfully'], 200);
    }

}
