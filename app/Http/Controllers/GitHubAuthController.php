<?php

namespace App\Http\Controllers;

use App\Models\User;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class GitHubAuthController extends Controller
{
    public function redirect()
    {
        try {
            $redirectUrl = Socialite::driver('github')->stateless()->redirect()->getTargetUrl();
            return response()->json([
                'success' => true,
                'redirect_url' => $redirectUrl,
                'message' => 'Redirigiendo a GitHub para autenticación'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al generar URL de redirección: ' . $e->getMessage()
            ], 500);
        }
    }

    public function callback(Request $request)
    {
        try {
            $githubUser = Socialite::driver('github')->stateless()->user();
            
            $user = User::where('github_id', $githubUser->getId())->first();
            
            if (!$user) {
                $user = User::create([
                    'name' => $githubUser->getName() ?: $githubUser->getNickname(),
                    'email' => $githubUser->getEmail(),
                    'github_id' => $githubUser->getId(),
                    'github_user_name' => $githubUser->getNickname(),
                    'password' => Hash::make(Str::random(32)),
                ]);
            } else {
                $user->update([
                    'name' => $githubUser->getName() ?: $githubUser->getNickname(),
                    'email' => $githubUser->getEmail(),
                    'github_user_name' => $githubUser->getNickname(),
                ]);
            }

            $frontendUrl = config('app.frontend_url', 'http://localhost:5173');
            $redirectUrl = $frontendUrl . '/auth/callback?' . http_build_query([
                'success' => 'true',
                'github_id' => $user->github_id,
                'name' => $user->name,
                'email' => $user->email,
                'github_user_name' => $user->github_user_name,
            ]);

            return redirect($redirectUrl);

        } catch (\Exception $e) {
            $frontendUrl = config('app.frontend_url', 'http://localhost:5173');
            $redirectUrl = $frontendUrl . '/auth/callback?' . http_build_query([
                'success' => 'false',
                'error' => $e->getMessage(),
            ]);

            return redirect($redirectUrl);
        }
    }

    public function user(Request $request)
    {
        $githubId = $request->input('github_id');
        $user = User::where('github_id', $githubId)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no encontrado'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'user' => [
                'github_id' => $user->github_id,
                'name' => $user->name,
                'email' => $user->email,
                'github_user_name' => $user->github_user_name,
            ]
        ]);
    }

    public function getSessionUser(Request $request)
    {
        // Start session if not already started
        if (!session()->isStarted()) {
            session()->start();
        }

        $githubId = $request->input('github_id');

        if (!$githubId) {
            return response()->json([
                'success' => false,
                'message' => 'github_id is required',
                'php_session' => session()->getId()
            ], 400);
        }

        $user = User::where('github_id', $githubId)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
                'php_session' => session()->getId()
            ], 404);
        }

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'github_id' => $user->github_id,
                'name' => $user->name,
                'email' => $user->email,
                'github_user_name' => $user->github_user_name,
            ],
            'php_session' => session()->getId()
        ]);
    }
}