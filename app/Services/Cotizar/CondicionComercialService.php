<?php

namespace App\Services\Cotizar;

use App\Models\CotizacionCondicionComercial;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class CondicionComercialService
{
    /**
     * Obtener condiciones comerciales por cotización
     */
    public function obtenerCondicionesPorCotizacion($cotizacionId)
    {
        try {
            return CotizacionCondicionComercial::where('cotizacion_id', $cotizacionId)->first();
        } catch (Exception $e) {
            Log::error('Error en CondicionComercialService::obtenerCondicionesPorCotizacion: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Guardar condiciones comerciales
     */
    public function guardarCondiciones($cotizacionId, $datos)
    {
        try {
            DB::beginTransaction();

            // Verificar si ya existen condiciones para esta cotización
            $condicionesExistentes = CotizacionCondicionComercial::where('cotizacion_id', $cotizacionId)->first();

            if ($condicionesExistentes) {
                // Actualizar condiciones existentes
                $condicionesExistentes->update([
                    'tiempo_entrega' => $datos['tiempo_entrega'] ?? null,
                    'lugar_obra' => $datos['lugar_obra'] ?? null,
                    'duracion_oferta' => $datos['duracion_oferta'] ?? null,
                    'garantia' => $datos['garantia'] ?? null,
                    'forma_pago' => $datos['forma_pago'] ?? null
                ]);

                $condiciones = $condicionesExistentes;
                $mensaje = 'Condiciones comerciales actualizadas exitosamente';
            } else {
                // Crear nuevas condiciones
                $condiciones = CotizacionCondicionComercial::create([
                    'cotizacion_id' => $cotizacionId,
                    'tiempo_entrega' => $datos['tiempo_entrega'] ?? null,
                    'lugar_obra' => $datos['lugar_obra'] ?? null,
                    'duracion_oferta' => $datos['duracion_oferta'] ?? null,
                    'garantia' => $datos['garantia'] ?? null,
                    'forma_pago' => $datos['forma_pago'] ?? null
                ]);

                $mensaje = 'Condiciones comerciales guardadas exitosamente';
            }

            DB::commit();

            Log::info('Condiciones comerciales procesadas exitosamente', [
                'cotizacion_id' => $cotizacionId,
                'accion' => $condicionesExistentes ? 'actualizar' : 'crear'
            ]);

            return [
                'success' => true,
                'message' => $mensaje,
                'data' => $condiciones
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error en CondicionComercialService::guardarCondiciones: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Actualizar condiciones comerciales
     */
    public function actualizarCondiciones($cotizacionId, $datos)
    {
        try {
            DB::beginTransaction();

            $condiciones = CotizacionCondicionComercial::where('cotizacion_id', $cotizacionId)->first();

            if (!$condiciones) {
                // Si no existen, crear nuevas
                $condiciones = CotizacionCondicionComercial::create([
                    'cotizacion_id' => $cotizacionId,
                    'tiempo_entrega' => $datos['tiempo_entrega'] ?? null,
                    'lugar_obra' => $datos['lugar_obra'] ?? null,
                    'duracion_oferta' => $datos['duracion_oferta'] ?? null,
                    'garantia' => $datos['garantia'] ?? null,
                    'forma_pago' => $datos['forma_pago'] ?? null
                ]);
            } else {
                // Actualizar existentes
                $condiciones->update([
                    'tiempo_entrega' => $datos['tiempo_entrega'] ?? null,
                    'lugar_obra' => $datos['lugar_obra'] ?? null,
                    'duracion_oferta' => $datos['duracion_oferta'] ?? null,
                    'garantia' => $datos['garantia'] ?? null,
                    'forma_pago' => $datos['forma_pago'] ?? null
                ]);
            }

            DB::commit();

            Log::info('Condiciones comerciales actualizadas exitosamente', [
                'cotizacion_id' => $cotizacionId
            ]);

            return [
                'success' => true,
                'message' => 'Condiciones comerciales actualizadas exitosamente',
                'data' => $condiciones
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error en CondicionComercialService::actualizarCondiciones: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Eliminar condiciones comerciales
     */
    public function eliminarCondiciones($cotizacionId)
    {
        try {
            DB::beginTransaction();

            $condiciones = CotizacionCondicionComercial::where('cotizacion_id', $cotizacionId)->first();

            if ($condiciones) {
                $condiciones->delete();
                $mensaje = 'Condiciones comerciales eliminadas exitosamente';
            } else {
                $mensaje = 'No se encontraron condiciones comerciales para eliminar';
            }

            DB::commit();

            Log::info('Condiciones comerciales eliminadas', [
                'cotizacion_id' => $cotizacionId,
                'eliminadas' => $condiciones ? 1 : 0
            ]);

            return [
                'success' => true,
                'message' => $mensaje
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error en CondicionComercialService::eliminarCondiciones: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Validar que todos los campos estén completos
     */
    public function validarCompletitud($cotizacionId)
    {
        try {
            $condiciones = $this->obtenerCondicionesPorCotizacion($cotizacionId);

            if (!$condiciones) {
                return [
                    'completo' => false,
                    'campos_faltantes' => ['Todas las condiciones comerciales']
                ];
            }

            $camposFaltantes = [];

            if (empty($condiciones->tiempo_entrega)) {
                $camposFaltantes[] = 'Tiempo de entrega';
            }

            if (empty($condiciones->lugar_obra)) {
                $camposFaltantes[] = 'Lugar de obra';
            }

            if (empty($condiciones->duracion_oferta)) {
                $camposFaltantes[] = 'Duración de la oferta';
            }

            if (empty($condiciones->garantia)) {
                $camposFaltantes[] = 'Garantía';
            }

            if (empty($condiciones->forma_pago)) {
                $camposFaltantes[] = 'Forma de pago';
            }

            return [
                'completo' => empty($camposFaltantes),
                'campos_faltantes' => $camposFaltantes
            ];

        } catch (Exception $e) {
            Log::error('Error en CondicionComercialService::validarCompletitud: ' . $e->getMessage());
            return [
                'completo' => false,
                'campos_faltantes' => ['Error al validar']
            ];
        }
    }
}