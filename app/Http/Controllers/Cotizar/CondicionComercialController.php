<?php

namespace App\Http\Controllers\Cotizar;

use App\Http\Controllers\Controller;
use App\Http\Requests\CotizacionCondicionComercialRequest;
use App\Models\CotizacionCondicionComercial;
use App\Services\Cotizar\CondicionComercialService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

class CondicionComercialController extends Controller
{
    protected $condicionComercialService;

    public function __construct(CondicionComercialService $condicionComercialService)
    {
        $this->condicionComercialService = $condicionComercialService;
    }

    /**
     * Obtener condiciones comerciales de una cotización específica
     */
    public function getCotizacionCondiciones($cotizacionId)
    {
        try {
            $condiciones = CotizacionCondicionComercial::where('cotizacion_id', $cotizacionId)->first();

            return response()->json([
                'success' => true,
                'data' => $condiciones ? [
                    'id' => $condiciones->id,
                    'tiempo_entrega' => $condiciones->tiempo_entrega,
                    'lugar_obra' => $condiciones->lugar_obra,
                    'duracion_oferta' => $condiciones->duracion_oferta,
                    'garantia' => $condiciones->garantia,
                    'forma_pago' => $condiciones->forma_pago
                ] : null
            ]);

        } catch (Exception $e) {
            Log::error('Error al obtener condiciones comerciales de cotización: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al cargar las condiciones comerciales de la cotización'
            ], 500);
        }
    }

    /**
     * Guardar o actualizar condiciones comerciales de una cotización
     */
    public function store(CotizacionCondicionComercialRequest $request)
    {
        try {
            $result = $this->condicionComercialService->guardarCondiciones(
                $request->cotizacion_id,
                $request->validated()
            );

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data' => $result['data']
            ]);

        } catch (Exception $e) {
            Log::error('Error al guardar condiciones comerciales: ' . $e->getMessage(), [
                'cotizacion_id' => $request->cotizacion_id ?? null,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al guardar las condiciones comerciales: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar condiciones comerciales de una cotización
     */
    public function update(CotizacionCondicionComercialRequest $request, $cotizacionId)
    {
        try {
            $result = $this->condicionComercialService->actualizarCondiciones(
                $cotizacionId,
                $request->validated()
            );

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data' => $result['data']
            ]);

        } catch (Exception $e) {
            Log::error('Error al actualizar condiciones comerciales: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar las condiciones comerciales: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar condiciones comerciales de una cotización
     */
    public function destroy($cotizacionId)
    {
        try {
            $result = $this->condicionComercialService->eliminarCondiciones($cotizacionId);

            return response()->json([
                'success' => true,
                'message' => $result['message']
            ]);

        } catch (Exception $e) {
            Log::error('Error al eliminar condiciones comerciales: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar las condiciones comerciales'
            ], 500);
        }
    }

    /**
     * Validar campos de condiciones comerciales
     */
    public function validarCondiciones(Request $request)
    {
        try {
            $request->validate([
                'tiempo_entrega' => 'nullable|string|max:255',
                'lugar_obra' => 'nullable|string|max:500',
                'duracion_oferta' => 'nullable|string|max:255',
                'garantia' => 'nullable|string|max:500',
                'forma_pago' => 'nullable|string|max:500'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Datos válidos'
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error en la validación',
                'errors' => $e->errors ?? []
            ], 422);
        }
    }
}