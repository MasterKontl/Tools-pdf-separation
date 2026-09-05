<?php

namespace App\Http\Middleware;

use App\Services\AuthTokenService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApiOptionalAuthenticate
{
    /**
     * Handle an incoming request and optionally resolve user from Bearer token or session.
     */
    public function handle(Request $request, Closure $next)
    {
        $bearer = $request->bearerToken();
        if ($bearer) {
            $user = AuthTokenService::resolveUser($bearer);
            if ($user) {
                Auth::setUser($user);
                $request->setUserResolver(fn ($guard = null) => $user);
                return $next($request);
            }
        }

        if (Auth::check()) {
            return $next($request);
        }

        return $next($request);
    }
}
