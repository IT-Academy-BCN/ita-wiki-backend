<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function updateRole(Request $request, User $user) 
{ /* ... */ 
    return response()->json(['message' => 'Role updated successfully', 'user' => $user], 200);
    }

    public function profile(Request $request) { /* ... */
    return response()->json(['message' => 'User profile retrieved successfully', 'user' => $request->user()], 200);
    }

    public function index() { /* listar usuarios */ 
    return response()->json(['message' => 'Users retrieved successfully', 'users' => User::all()], 200);
    }

    public function destroy(User $user) { /* eliminar usuario */ 
    $user->delete();
    return response()->json(['message' => 'User deleted successfully'], 200);
    }

}
