<?php

namespace App\Http\Controllers\Cotizar;

use App\Http\Controllers\Controller;
use App\Models\CotizacionConcepto;
use App\Models\Concepto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class CotizacionConceptoController extends Controller
{
    /**
     * Obtener conceptos disponibles para impuestos y descuentos
     */
    public function getConceptos()
    {
        try {
            $conceptos = Concepto::select('id', 'nombre', 'tipo', 'porcentaje_defecto', 'active')
                ->where('active', 1)
                ->orderBy('tipo')
                ->orderBy('nombre')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $conceptos
            ]);

        } catch (Exception $e) {
            Log::error('Error al obtener conceptos: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al cargar los conceptos'
            ], 500);
        }
    }

    /**
     * Guardar conceptos de una cotización
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'cotizacion_id' => 'required|integer|exists:ord_cotizacion,id',
                'conceptos' => 'array', // Removemos required para permitir arrays vacíos
            ]);

            // Solo validar campos de conceptos si el array no está vacío
            if (!empty($request->conceptos)) {
                $request->validate([
                    'conceptos.*.concepto_id' => 'required|integer|exists:conceptos,id',
                    'conceptos.*.porcentaje' => 'nullable|numeric|min:0|max:100',
                    'conceptos.*.valor' => 'required|numeric|min:0'
                ]);
            }

            DB::beginTransaction();

            Log::info('Procesando conceptos para cotización', [
                'cotizacion_id' => $request->cotizacion_id,
                'cantidad_conceptos' => count($request->conceptos ?? [])
            ]);

            // Eliminar conceptos existentes de la cotización
            $eliminados = CotizacionConcepto::where('cotizacion_id', $request->cotizacion_id)->delete();

            Log::info('Conceptos eliminados', [
                'cotizacion_id' => $request->cotizacion_id,
                'eliminados' => $eliminados
            ]);

            // Insertar nuevos conceptos solo si hay conceptos en el array
            if (!empty($request->conceptos)) {
                foreach ($request->conceptos as $concepto) {
                    CotizacionConcepto::create([
                        'cotizacion_id' => $request->cotizacion_id,
                        'concepto_id' => $concepto['concepto_id'],
                        'porcentaje' => $concepto['porcentaje'] ?? null,
                        'valor' => $concepto['valor']
                    ]);
                }

                Log::info('Nuevos conceptos creados', [
                    'cotizacion_id' => $request->cotizacion_id,
                    'creados' => count($request->conceptos)
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => empty($request->conceptos) ?
                    'Conceptos eliminados exitosamente' :
                    'Conceptos guardados exitosamente'
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error al guardar conceptos: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al guardar los conceptos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener conceptos de una cotización específica
     */
    public function getCotizacionConceptos($cotizacionId)
    {
        try {
            $conceptos = CotizacionConcepto::with('concepto')
                ->where('cotizacion_id', $cotizacionId)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $conceptos
            ]);

        } catch (Exception $e) {
            Log::error('Error al obtener conceptos de cotización: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al cargar los conceptos de la cotización'
            ], 500);
        }
    }

    /**
     * Actualizar conceptos de una cotización
     */
    public function update(Request $request, $cotizacionId)
    {
        try {
            $request->validate([
                'conceptos' => 'required|array',
                'conceptos.*.concepto_id' => 'required|integer|exists:conceptos,id',
                'conceptos.*.porcentaje' => 'nullable|numeric|min:0|max:100',
                'conceptos.*.valor' => 'required|numeric|min:0'
            ]);

            DB::beginTransaction();

            // Eliminar conceptos existentes
            CotizacionConcepto::where('cotizacion_id', $cotizacionId)->delete();

            // Insertar nuevos conceptos
            foreach ($request->conceptos as $concepto) {
                CotizacionConcepto::create([
                    'cotizacion_id' => $cotizacionId,
                    'concepto_id' => $concepto['concepto_id'],
                    'porcentaje' => $concepto['porcentaje'] ?? null,
                    'valor' => $concepto['valor']
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Conceptos actualizados exitosamente'
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar conceptos: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar los conceptos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar un concepto específico de una cotización
     */
    public function destroy($id)
    {
        try {
            $concepto = CotizacionConcepto::findOrFail($id);
            $concepto->delete();

            return response()->json([
                'success' => true,
                'message' => 'Concepto eliminado exitosamente'
            ]);

        } catch (Exception $e) {
            Log::error('Error al eliminar concepto: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el concepto'
            ], 500);
        }
    }

    /**
     * Calcular totales de impuestos y descuentos para una cotización
     */
    public function calcularTotales(Request $request)
    {
        try {
            $request->validate([
                'subtotal' => 'required|numeric|min:0',
                'conceptos' => 'required|array',
                'conceptos.*.concepto_id' => 'required|integer',
                'conceptos.*.tipo' => 'required|in:impuesto,descuento',
                'conceptos.*.tipo_calculo' => 'required|in:porcentaje,valor',
                'conceptos.*.valor' => 'required|numeric|min:0'
            ]);

            $subtotal = $request->subtotal;
            $totalImpuestos = 0;
            $totalDescuentos = 0;

            foreach ($request->conceptos as $concepto) {
                $valorCalculado = 0;

                if ($concepto['tipo_calculo'] === 'porcentaje') {
                    $valorCalculado = ($subtotal * $concepto['valor']) / 100;
                } else {
                    $valorCalculado = $concepto['valor'];
                }

                if ($concepto['tipo'] === 'impuesto') {
                    $totalImpuestos += $valorCalculado;
                } else {
                    $totalDescuentos += $valorCalculado;
                }
            }

            $subtotalMenosDescuento = $subtotal - $totalDescuentos;
            $totalFinal = $subtotalMenosDescuento + $totalImpuestos;

            return response()->json([
                'success' => true,
                'data' => [
                    'subtotal' => $subtotal,
                    'total_descuentos' => $totalDescuentos,
                    'subtotal_menos_descuento' => $subtotalMenosDescuento,
                    'total_impuestos' => $totalImpuestos,
                    'total_final' => $totalFinal
                ]
            ]);

        } catch (Exception $e) {
            Log::error('Error al calcular totales: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al calcular los totales'
            ], 500);
        }
    }
}
