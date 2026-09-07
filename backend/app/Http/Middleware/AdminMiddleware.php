<?php

namespace App\Http\Middleware;

use App\Services\SecurityAuditLogger;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user() ?: Auth::user();

        if (!$user || !$user->isAdmin()) {
            SecurityAuditLogger::adminAccessDenied($request);

            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'error' => 'Akses ditolak. Endpoint ini hanya dapat diakses oleh Administrator.',
                ], 403);
            }

            abort(403, 'Akses ditolak. Halaman ini hanya dapat diakses oleh Administrator.');
        }

        return $next($request);
    }
}
