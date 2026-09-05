<?php

namespace App\Http\Middleware;

use App\Services\AuthTokenService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApiAuthenticate
{
    /**
     * Handle an incoming request and ensure user is authenticated.
     */
    public function handle(Request $request, Closure $next)
    {
        // 1. Prioritize Bearer token if provided
        $bearer = $request->bearerToken();
        if ($bearer) {
            $user = AuthTokenService::resolveUser($bearer);
            if ($user) {
                Auth::setUser($user);
                $request->setUserResolver(fn ($guard = null) => $user);
                return $next($request);
            }

            return response()->json([
                'success' => false,
                'error' => 'Token autentikasi tidak valid atau sudah kedaluwarsa.',
            ], 401);
        }

        // 2. Fallback to existing session authentication
        if (Auth::check()) {
            return $next($request);
        }

        return response()->json([
            'success' => false,
            'error' => 'Unauthenticated. Silakan masuk terlebih dahulu.',
        ], 401);
    }
}
