<?php

namespace App\Http\Controllers\Cotizar;

use App\Http\Controllers\Controller;
use App\Models\CotizacionViatico;
use App\Models\Cotizacion;
use App\Services\CotizacionTotalesService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class CotizacionViaticoController extends Controller
{
    protected CotizacionTotalesService $totalesService;

    public function __construct(CotizacionTotalesService $totalesService)
    {
        $this->totalesService = $totalesService;
    }

    /**
     * Obtener los viáticos de una cotización
     */
    public function index(int $cotizacionId)
    {
        try {
            $cotizacion = Cotizacion::findOrFail($cotizacionId);

            $viaticos = CotizacionViatico::where('cotizacion_id', $cotizacion->id)
                ->orderBy('orden')
                ->get(['id', 'concepto', 'tipo_costo', 'cantidad', 'valor', 'orden']);

            return response()->json([
                'success' => true,
                'data'    => $viaticos,
                'total'   => $viaticos->sum('valor'),
            ]);
        } catch (Exception $e) {
            Log::error('Error al obtener viáticos: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al cargar viáticos'], 500);
        }
    }

    /**
     * Obtener la suma de cantidad de operarios por tipo_costo en una cotización
     */
    public function getCantidadByTipoCosto(int $cotizacionId, string $tipoCosto)
    {
        try {
            if (!in_array($tipoCosto, ['hora', 'dia'])) {
                return response()->json(['success' => false, 'message' => 'Tipo de costo inválido'], 422);
            }

            Cotizacion::findOrFail($cotizacionId);

            $cantidad = DB::table('ord_cotizacion_productos')
                ->where('cotizacion_id', $cotizacionId)
                ->where('tipo_costo', $tipoCosto)
                ->where('active', true)
                ->sum('cantidad');

            return response()->json([
                'success'  => true,
                'cantidad' => (float) $cantidad,
            ]);
        } catch (Exception $e) {
            Log::error('Error al obtener cantidad por tipo costo: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al obtener cantidad'], 500);
        }
    }

    /**
     * Crear un nuevo viático
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'cotizacion_id' => 'required|integer|exists:ord_cotizacion,id',
                'concepto'      => 'required|string|max:255',
                'tipo_costo'    => 'nullable|in:hora,dia',
                'cantidad'      => 'nullable|numeric|min:0',
                'valor'         => 'required|numeric|min:0',
            ]);

            $orden = CotizacionViatico::where('cotizacion_id', $validated['cotizacion_id'])->max('orden') + 1;

            $viatico = CotizacionViatico::create([
                'cotizacion_id' => $validated['cotizacion_id'],
                'concepto'      => $validated['concepto'],
                'tipo_costo'    => $validated['tipo_costo'] ?? null,
                'cantidad'      => $validated['cantidad'] ?? null,
                'valor'         => $validated['valor'],
                'orden'         => $orden,
            ]);

            $cotizacion = Cotizacion::findOrFail($validated['cotizacion_id']);
            $cotizacion = $this->totalesService->recalcular($cotizacion);

            return response()->json([
                'success'  => true,
                'message'  => 'Viático agregado correctamente',
                'data'     => $viatico,
                'totales'  => [
                    'subtotal'       => (float) $cotizacion->subtotal,
                    'descuento'      => (float) $cotizacion->descuento,
                    'total_impuesto' => (float) $cotizacion->total_impuesto,
                    'viaticos'       => (float) $cotizacion->viaticos,
                    'total'          => (float) $cotizacion->total,
                ],
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Datos inválidos', 'errors' => $e->errors()], 422);
        } catch (Exception $e) {
            Log::error('Error al guardar viático: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al guardar el viático'], 500);
        }
    }

    /**
     * Actualizar un viático existente
     */
    public function update(Request $request, int $id)
    {
        try {
            $viatico = CotizacionViatico::findOrFail($id);

            $validated = $request->validate([
                'concepto'   => 'sometimes|required|string|max:255',
                'tipo_costo' => 'sometimes|nullable|in:hora,dia',
                'cantidad'   => 'sometimes|nullable|numeric|min:0',
                'valor'      => 'sometimes|required|numeric|min:0',
            ]);

            $viatico->update($validated);

            $cotizacion = Cotizacion::findOrFail($viatico->cotizacion_id);
            $cotizacion = $this->totalesService->recalcular($cotizacion);

            return response()->json([
                'success' => true,
                'message' => 'Viático actualizado correctamente',
                'data'    => $viatico->fresh(),
                'totales' => [
                    'subtotal'       => (float) $cotizacion->subtotal,
                    'descuento'      => (float) $cotizacion->descuento,
                    'total_impuesto' => (float) $cotizacion->total_impuesto,
                    'viaticos'       => (float) $cotizacion->viaticos,
                    'total'          => (float) $cotizacion->total,
                ],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Datos inválidos', 'errors' => $e->errors()], 422);
        } catch (Exception $e) {
            Log::error('Error al actualizar viático: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al actualizar el viático'], 500);
        }
    }

    /**
     * Eliminar un viático
     */
    public function destroy(int $id)
    {
        try {
            $viatico = CotizacionViatico::findOrFail($id);
            $cotizacionId = $viatico->cotizacion_id;
            $viatico->delete();

            $cotizacion = Cotizacion::findOrFail($cotizacionId);
            $cotizacion = $this->totalesService->recalcular($cotizacion);

            return response()->json([
                'success' => true,
                'message' => 'Viático eliminado correctamente',
                'totales' => [
                    'subtotal'       => (float) $cotizacion->subtotal,
                    'descuento'      => (float) $cotizacion->descuento,
                    'total_impuesto' => (float) $cotizacion->total_impuesto,
                    'viaticos'       => (float) $cotizacion->viaticos,
                    'total'          => (float) $cotizacion->total,
                ],
            ]);
        } catch (Exception $e) {
            Log::error('Error al eliminar viático: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al eliminar el viático'], 500);
        }
    }
}
