<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureSysAdminRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Debe iniciar sesión para acceder a esta función.');
        }

        if (!Auth::user()->hasRole('sysadmin')) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Acceso denegado. Solo los usuarios con rol sysadmin pueden realizar esta acción.',
                    'message' => 'Insufficient permissions'
                ], 403);
            }
            
            // Mostrar vista personalizada en lugar de abort
            return response()->view('errors.sysadmin-required', [], 403);
        }

        return $next($request);
    }
}