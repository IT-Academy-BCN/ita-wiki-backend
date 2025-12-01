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
                'message' => 'Redirecting to GitHub for authentication'
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
                    'github_id' => $githubUser->getId(),
                    'github_user_name' => $githubUser->getNickname(),
                    'name' => $githubUser->getName() ?: $githubUser->getNickname(),
                    'email' => $githubUser->getEmail() ?? $githubUser->getNickname() . '@github.local',                   
                    'password' => Hash::make(Str::random(32)),
                ]);
            } else {
                $user->update([
                    'github_user_name' => $githubUser->getNickname(),
                    'name' => $githubUser->getName() ?: $githubUser->getNickname(),
                    'email' => $githubUser->getEmail() ?? $user->email,                   
                ]);
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            $frontendUrl = config('app.frontend_url', 'http://localhost:5173');

            $redirectUrl = $frontendUrl . '/auth/callback?token=' . urlencode($token);

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
        return response()->json([
            'success' => true,
            'user' => [
                'id' => $request->user()->id,
                'github_id' => $request->user()->github_id,
                'github_user_name' => $request->user()->github_user_name,
                'name' => $request->user()->name,
                'email' => $request->user()->email,               
            ]
        ]);
    }
}