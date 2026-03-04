<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class SetCompanySession
{
    /**
     * Handle an incoming request.
     *
     * Asegura que company_id esté siempre en la sesión cuando el usuario está autenticado.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();

            // Si no existe company_id en sesión, lo seteamos desde el usuario
            if (!session()->has('company_id') && $user->company_id) {
                session(['company_id' => (int) $user->company_id]);

                Log::info('Company ID seteado en sesión', [
                    'user_id' => $user->id,
                    'company_id' => $user->company_id
                ]);
            }

            // Si hay company_id en sesión pero no coincide con el del usuario, actualizar
            if (session('company_id') && $user->company_id && session('company_id') != $user->company_id) {
                session(['company_id' => (int) $user->company_id]);

                Log::warning('Company ID actualizado en sesión (no coincidía)', [
                    'user_id' => $user->id,
                    'old_company_id' => session('company_id'),
                    'new_company_id' => $user->company_id
                ]);
            }
        }

        return $next($request);
    }
}
