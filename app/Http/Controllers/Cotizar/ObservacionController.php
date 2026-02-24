<?php

namespace App\Http\Controllers\Cotizar;

use App\Http\Controllers\Controller;
use App\Models\ObservacionCotizacion;
use App\Models\Observacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class ObservacionController extends Controller
{
    /**
     * Obtener observaciones disponibles
     */
    public function getObservaciones()
    {
        try {
            $observaciones = Observacion::activas()
                ->orderBy('texto')
                ->get()
                ->map(function ($observacion) {
                    return [
                        'id' => $observacion->id,
                        'texto' => $observacion->texto
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $observaciones
            ]);

        } catch (Exception $e) {
            Log::error('Error al obtener observaciones: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al cargar las observaciones'
            ], 500);
        }
    }

    /**
     * Obtener observaciones de una cotización específica
     */
    public function getCotizacionObservaciones($cotizacionId)
    {
        try {
            $observaciones = ObservacionCotizacion::porCotizacion($cotizacionId)
                ->activas()
                ->conObservacion()
                ->get()
                ->map(function ($observacionCotizacion) {
                    return [
                        'id' => $observacionCotizacion->observacion_id,
                        'texto' => $observacionCotizacion->observacion ? $observacionCotizacion->observacion->texto : ''
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $observaciones
            ]);

        } catch (Exception $e) {
            Log::error('Error al obtener observaciones de cotización: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al cargar las observaciones de la cotización'
            ], 500);
        }
    }

    /**
     * Guardar observaciones de una cotización
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'cotizacion_id' => 'required|integer|exists:ord_cotizacion,id',
                'observaciones' => 'array'
            ]);

            // Solo validar campos de observaciones si el array no está vacío
            if (!empty($request->observaciones)) {
                $request->validate([
                    'observaciones.*.observacion_id' => 'required|integer|exists:ord_observaciones,id'
                ]);
            }

            DB::beginTransaction();

            Log::info('Procesando observaciones para cotización', [
                'cotizacion_id' => $request->cotizacion_id,
                'cantidad_observaciones' => count($request->observaciones ?? [])
            ]);

            // Desactivar observaciones existentes de la cotización
            ObservacionCotizacion::where('cotizacion_id', $request->cotizacion_id)
                ->update(['active' => 0]);
            
            Log::info('Observaciones existentes desactivadas', [
                'cotizacion_id' => $request->cotizacion_id
            ]);

            // Insertar nuevas observaciones solo si hay observaciones en el array
            if (!empty($request->observaciones)) {
                foreach ($request->observaciones as $observacion) {
                    // Crear la relación en ord_cotizaciones_observaciones
                    ObservacionCotizacion::create([
                        'cotizacion_id' => $request->cotizacion_id,
                        'observacion_id' => $observacion['observacion_id'],
                        'active' => 1
                    ]);
                }
                
                Log::info('Nuevas observaciones creadas', [
                    'cotizacion_id' => $request->cotizacion_id,
                    'creadas' => count($request->observaciones)
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => empty($request->observaciones) ? 
                    'Observaciones eliminadas exitosamente' : 
                    'Observaciones guardadas exitosamente'
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error al guardar observaciones: ' . $e->getMessage(), [
                'cotizacion_id' => $request->cotizacion_id ?? null,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al guardar las observaciones: ' . $e->getMessage()
            ], 500);
        }
    }    /**
     * Actualizar observaciones de una cotización
     */
    public function update(Request $request, $cotizacionId)
    {
        try {
            $request->validate([
                'observaciones' => 'array'
            ]);

            // Solo validar campos de observaciones si el array no está vacío
            if (!empty($request->observaciones)) {
                $request->validate([
                    'observaciones.*.reg' => 'required|integer|min:1',
                    'observaciones.*.nombre' => 'required|string|max:255',
                    'observaciones.*.detalle' => 'nullable|string'
                ]);
            }

            DB::beginTransaction();

            // Desactivar observaciones existentes
            ObservacionCotizacion::where('cotizacion_id', $cotizacionId)
                ->update(['active' => 0]);

            // Insertar nuevas observaciones
            if (!empty($request->observaciones)) {
                foreach ($request->observaciones as $observacion) {
                    // Crear o encontrar la observación en la tabla ord_observaciones
                    $obs = Observacion::create([
                        'texto' => $observacion['detalle'] ?? $observacion['nombre'],
                        'active' => 1
                    ]);

                    // Crear la relación en ord_cotizaciones_observaciones
                    ObservacionCotizacion::create([
                        'cotizacion_id' => $cotizacionId,
                        'observacion_id' => $obs->id,
                        'active' => 1
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Observaciones actualizadas exitosamente'
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar observaciones: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar las observaciones: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar una observación específica de una cotización
     */
    public function destroy($id)
    {
        try {
            $observacionCotizacion = ObservacionCotizacion::findOrFail($id);
            
            // Desactivar en lugar de eliminar
            $observacionCotizacion->update(['active' => 0]);

            return response()->json([
                'success' => true,
                'message' => 'Observación eliminada exitosamente'
            ]);

        } catch (Exception $e) {
            Log::error('Error al eliminar observación: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la observación'
            ], 500);
        }
    }
}
