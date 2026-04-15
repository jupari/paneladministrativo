<?php

namespace App\Http\Controllers\Cotizar;

use App\Http\Controllers\Controller;
use App\Services\NominaLiquidacionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NominaLiquidacionController extends Controller
{
    /**
     * Calcula la liquidación de nómina para un cargo con novedades estimadas.
     *
     * POST /admin/cotizaciones/nomina/calcular
     */
    public function calcular(Request $request, NominaLiquidacionService $service): JsonResponse
    {
        $validated = $request->validate([
            'cargo_id'                   => 'required|integer|exists:cargos,id',
            'año'                        => 'required|integer|min:2020|max:2035',
            'cantidad_personas'          => 'required|integer|min:1|max:9999',
            'dias_diurnos'               => 'required|integer|min:0|max:30',
            'dias_nocturnos'             => 'nullable|integer|min:0|max:30',
            'dominicales_diurnos'        => 'nullable|integer|min:0|max:30',
            'dominicales_nocturnos'      => 'nullable|integer|min:0|max:30',
            'horas_extra_diurnas'        => 'nullable|integer|min:0|max:999',
            'horas_extra_nocturnas'      => 'nullable|integer|min:0|max:999',
            'horas_extra_dom_diurnas'    => 'nullable|integer|min:0|max:999',
            'horas_extra_dom_nocturnas'  => 'nullable|integer|min:0|max:999',
        ]);

        $resultado = $service->calcular($validated);

        return response()->json([
            'success' => true,
            'data'    => $resultado,
        ]);
    }
}
