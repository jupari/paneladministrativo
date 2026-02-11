<?php

namespace App\Services\Cotizar;

use App\Models\ObservacionCotizacion;
use App\Models\Observacion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class ObservacionService
{
    /**
     * Obtener observaciones de una cotización
     */
    public function obtenerObservacionesPorCotizacion($cotizacionId)
    {
        try {
            return ObservacionCotizacion::porCotizacion($cotizacionId)
                ->activas()
                ->conObservacion()
                ->get();
        } catch (Exception $e) {
            Log::error('Error en ObservacionService::obtenerObservacionesPorCotizacion: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Guardar observaciones de una cotización
     */
    public function guardarObservaciones($cotizacionId, $observaciones = [])
    {
        try {
            DB::beginTransaction();

            // Desactivar observaciones existentes
            ObservacionCotizacion::where('cotizacion_id', $cotizacionId)
                ->update(['active' => 0]);

            // Si hay observaciones, crearlas
            if (!empty($observaciones)) {
                foreach ($observaciones as $observacion) {
                    $this->validarDatosObservacion($observacion);
                    
                    // Crear la observación
                    $obs = Observacion::create([
                        'texto' => $observacion['detalle'] ?? $observacion['nombre'],
                        'active' => 1
                    ]);

                    // Crear la relación
                    ObservacionCotizacion::create([
                        'cotizacion_id' => $cotizacionId,
                        'observacion_id' => $obs->id,
                        'active' => 1
                    ]);
                }
            }

            DB::commit();

            Log::info('Observaciones guardadas exitosamente', [
                'cotizacion_id' => $cotizacionId,
                'cantidad' => count($observaciones)
            ]);

            return [
                'success' => true,
                'message' => count($observaciones) > 0 ? 
                    'Observaciones guardadas exitosamente' : 
                    'Observaciones eliminadas exitosamente',
                'data' => $this->obtenerObservacionesPorCotizacion($cotizacionId)
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error en ObservacionService::guardarObservaciones: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Eliminar observación específica
     */
    public function eliminarObservacion($observacionId)
    {
        try {
            $observacionCotizacion = ObservacionCotizacion::findOrFail($observacionId);
            $cotizacionId = $observacionCotizacion->cotizacion_id;
            
            // Desactivar en lugar de eliminar
            $observacionCotizacion->update(['active' => 0]);

            Log::info('Observación eliminada exitosamente', [
                'observacion_id' => $observacionId,
                'cotizacion_id' => $cotizacionId
            ]);

            return [
                'success' => true,
                'message' => 'Observación eliminada exitosamente',
                'cotizacion_id' => $cotizacionId
            ];

        } catch (Exception $e) {
            Log::error('Error en ObservacionService::eliminarObservacion: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Validar datos de una observación
     */
    private function validarDatosObservacion($observacion)
    {
        if (empty($observacion['reg']) || !is_numeric($observacion['reg']) || $observacion['reg'] < 1) {
            throw new Exception('El campo Reg es requerido y debe ser un número mayor a 0');
        }

        if (empty($observacion['nombre']) || strlen(trim($observacion['nombre'])) === 0) {
            throw new Exception('El campo Nombre es requerido');
        }

        if (strlen($observacion['nombre']) > 255) {
            throw new Exception('El campo Nombre no puede exceder los 255 caracteres');
        }
    }
}