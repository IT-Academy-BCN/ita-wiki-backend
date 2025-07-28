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
{ /* ... */ }

    public function profile(Request $request) { /* ... */ }

    public function index() { /* listar usuarios */ }

    public function destroy(User $user) { /* eliminar usuario */ }

}
