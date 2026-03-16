<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

/**
 * Valida que la petición lleve un token de tipo "refresh-token".
 * Usado exclusivamente en POST /api/v1/auth/refresh.
 */
class EnsureRefreshToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $bearerToken = $request->bearerToken();

        if (!$bearerToken) {
            return response()->json(['message' => 'Token de refresh requerido.'], 401);
        }

        $token = PersonalAccessToken::findToken($bearerToken);

        if (!$token || !$token->can('refresh') || ($token->expires_at && $token->expires_at->isPast())) {
            return response()->json(['message' => 'Token de refresh inválido o expirado.'], 401);
        }

        // Hacer disponible el usuario en el request
        $request->setUserResolver(fn () => $token->tokenable);

        return $next($request);
    }
}
