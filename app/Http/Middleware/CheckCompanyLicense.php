<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckCompanyLicense
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Si el usuario no está autenticado, continuar (manejado por auth middleware)
        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();

        // Si el usuario no tiene empresa asignada
        if (!$user->company) {
            // Rutas de administrador exentas para configurar empresas
            $exemptRoutes = [
                'dashboard',
                'admin.companies.*',
                'admin.roles.*',
                'admin.permission.*',
                'logout',
                'user.profile'
            ];

            foreach ($exemptRoutes as $route) {
                if ($request->routeIs($route)) {
                    return $next($request);
                }
            }

            return redirect()->route('dashboard')->with('error', 'No tienes una empresa asignada. Contacta al administrador.');
        }

        $company = $user->company;

        // Verificar si la empresa está activa
        if (!$company->is_active) {
            Auth::logout();
            return redirect()->route('login')->with('error', 'La empresa está desactivada. Contacta al administrador.');
        }

        // Verificar si la licencia ha expirado
        if (!$company->isLicenseValid()) {
            // Rutas exentas para administradores para renovar licencia
            $renewalRoutes = [
                'dashboard',
                'admin.companies.*',
                'admin.roles.*',
                'admin.permission.*',
                'logout'
            ];

            foreach ($renewalRoutes as $route) {
                if ($request->routeIs($route) && $user->hasRole('Administrator')) {
                    return $next($request);
                }
            }

            Auth::logout();
            return redirect()->route('login')->with('error', 'La licencia de la empresa ha expirado. Contacta al administrador para renovarla.');
        }

        // Agregar información de la empresa al request para uso posterior
        $request->attributes->add(['company' => $company]);

        // Verificar advertencia de expiración próxima (solo mostrar, no bloquear)
        if ($company->isLicenseExpiringSoon()) {
            $daysRemaining = $company->daysUntilExpiration();
            session()->flash('warning', "La licencia expira en {$daysRemaining} días. Renueva pronto para evitar interrupciones.");
        }

        return $next($request);
    }
}
