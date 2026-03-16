<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\UserResource;
use App\Http\Traits\ApiResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    use ApiResponses;

    /**
     * POST /api/v1/auth/login
     *
     * Body: { "email": "...", "password": "..." }
     * Respuesta: { "access_token": "...", "refresh_token": "...", "user": {...} }
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return $this->errorResponse('Credenciales inválidas.', 401);
        }

        // Revocar tokens anteriores del mismo tipo para este dispositivo (evitar acumulación)
        $user->tokens()
             ->where('name', 'access-token')
             ->where('last_used_at', '<', now()->subDays(30))
             ->delete();

        $access  = $user->createToken('access-token',  ['*'],       now()->addHours(8));
        $refresh = $user->createToken('refresh-token', ['refresh'], now()->addDays(30));

        return response()->json([
            'access_token'  => $access->plainTextToken,
            'refresh_token' => $refresh->plainTextToken,
            'user'          => new UserResource($user),
        ]);
    }

    /**
     * POST /api/v1/auth/logout
     * Guard: auth:sanctum
     */
    public function logout(Request $request): JsonResponse
    {
        // Revocar solo el token actual (no todos, para soportar multi-dispositivo)
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Sesión cerrada correctamente.']);
    }

    /**
     * POST /api/v1/auth/refresh
     * Middleware: EnsureRefreshToken
     *
     * Body: vacío (el refresh_token se pasa como Bearer)
     * Respuesta: { "access_token": "..." }
     */
    public function refresh(Request $request): JsonResponse
    {
        $user         = $request->user();
        $currentToken = $request->user()->currentAccessToken();

        // Revocar el refresh token usado
        $currentToken->delete();

        // Emitir nuevo access token
        $newAccess = $user->createToken('access-token', ['*'], now()->addHours(8));

        return response()->json([
            'access_token' => $newAccess->plainTextToken,
        ]);
    }

    /**
     * GET /api/v1/auth/me
     * Guard: auth:sanctum
     *
     * Respuesta: objeto user con permisos y talleres asignados
     */
    public function me(Request $request): JsonResponse
    {
        return $this->successResponse(new UserResource($request->user()));
    }
}
