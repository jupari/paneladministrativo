<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Verifica que el usuario autenticado tenga acceso al taller
 * indicado en el parámetro de ruta {workshop} o {workshopId}.
 */
class EnsureWorkshopAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $workshopId = $request->route('workshop') ?? $request->route('workshopId');

        if (!$workshopId) {
            return response()->json(['message' => 'Taller no especificado.'], 400);
        }

        $user = $request->user();

        // Verifica que el taller esté asignado al usuario Y pertenezca a su compañía
        $hasAccess = $user->workshops()
            ->where('workshops.id', $workshopId)
            ->whereHas('companies', fn ($q) => $q->where('companies.id', $user->company_id))
            ->exists();

        if (!$hasAccess) {
            return response()->json(['message' => 'Sin acceso a este taller.'], 403);
        }

        return $next($request);
    }
}
