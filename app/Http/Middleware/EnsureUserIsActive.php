<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Bloquea el acceso a usuarios cuya cuenta fue desactivada (is_active = false),
 * aunque su token Sanctum siga siendo valido.
 */
class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! $user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Su cuenta se encuentra desactivada. Contacte al administrador.',
                'data' => null,
            ], 403);
        }

        return $next($request);
    }
}
