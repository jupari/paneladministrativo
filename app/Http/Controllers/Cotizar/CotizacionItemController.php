<?php

namespace App\Http\Controllers\Cotizar;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cotizar\CotizacionItemRequest;
use App\Services\CotizacionItemService;
use App\Models\CotizacionItem;
use App\Models\CotizacionSubImtes;
use App\Models\UnidadMedida;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Exception;

class CotizacionItemController extends Controller
{
    protected $cotizacionItemService;

    public function __construct(CotizacionItemService $cotizacionItemService)
    {
        $this->cotizacionItemService = $cotizacionItemService;
    }

    /**
     * Obtener items de una cotización específica
     */
    public function getCotizacionItems($cotizacionId): JsonResponse
    {
        try {
            $items = $this->cotizacionItemService->obtenerItemsPorCotizacion($cotizacionId);

            return response()->json([
                'success' => true,
                'data' => $items
            ]);
        } catch (Exception $e) {
            Log::error('Error al obtener items de cotización: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los items de la cotización'
            ], 500);
        }
    }

    /**
     * Obtener subitems disponibles
     */
    public function getSubitems(): JsonResponse
    {
        try {
            $subitems = CotizacionSubImtes::with('unidadMedida')
                ->orderBy('codigo')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $subitems
            ]);
        } catch (Exception $e) {
            Log::error('Error al obtener subitems: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los subitems disponibles'
            ], 500);
        }
    }

    /**
     * Obtener unidades de medida
     */
    public function getUnidadesMedida(): JsonResponse
    {
        try {
            $unidades = UnidadMedida::where('active', 1)
                ->orderBy('nombre')
                ->get(['id', 'nombre', 'sigla']);

            return response()->json([
                'success' => true,
                'data' => $unidades
            ]);
        } catch (Exception $e) {
            Log::error('Error al obtener unidades de medida: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las unidades de medida'
            ], 500);
        }
    }

    /**
     * Guardar items de cotización
     */
    public function store(CotizacionItemRequest $request): JsonResponse
    {
        try {
            $resultado = $this->cotizacionItemService->guardarItems(
                $request->cotizacion_id,
                $request->items ?? []
            );

            return response()->json([
                'success' => true,
                'message' => $resultado['message'],
                'data' => $resultado['data']
            ]);
        } catch (Exception $e) {
            Log::error('Error al guardar items de cotización: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar los items: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear un item individual
     */
    public function createItem(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'cotizacion_id' => 'required|exists:ord_cotizacion,id',
                'nombre' => 'required|string|max:255',
                'active' => 'boolean'
            ]);

            $resultado = $this->cotizacionItemService->crearItem(
                $request->cotizacion_id,
                $request->only(['nombre', 'active'])
            );

            if ($resultado['success']) {
                return response()->json([
                    'success' => true,
                    'message' => $resultado['message'],
                    'data' => $resultado['data']
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $resultado['message']
                ], 422);
            }

        } catch (Exception $e) {
            Log::error('Error al crear item individual: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el item: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar items de cotización
     */
    public function update(CotizacionItemRequest $request, $cotizacionId): JsonResponse
    {
        try {
            $resultado = $this->cotizacionItemService->actualizarItems(
                $cotizacionId,
                $request->items ?? []
            );

            return response()->json([
                'success' => true,
                'message' => $resultado['message'],
                'data' => $resultado['data']
            ]);
        } catch (Exception $e) {
            Log::error('Error al actualizar items de cotización: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar los items: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar un item específico
     */
    public function destroy($id): JsonResponse
    {
        try {
            $this->cotizacionItemService->eliminarItem($id);

            return response()->json([
                'success' => true,
                'message' => 'Item eliminado exitosamente'
            ]);
        } catch (Exception $e) {
            Log::error('Error al eliminar item: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el item'
            ], 500);
        }
    }

    /**
     * Crear un nuevo subitem
     */
    public function createSubitem(Request $request): JsonResponse
    {
        try {
             $request->validate([
                'codigo' => 'required|string|max:50|unique:ord_cotizaciones_subitems,codigo',
                'nombre' => 'required|string|max:255',
                'unidad_medida_id' => 'required|exists:unidades_medida,id',
                'cantidad' => 'nullable|numeric|min:0',
                'observacion' => 'nullable|string|max:500',
                'cotizacion_item_id' => 'required|exists:ord_cotizaciones_items,id'
            ]);
            $subitem = new CotizacionSubImtes();
            $subitem->cotizacion_item_id = $request->cotizacion_item_id;
            $subitem->codigo = $request->codigo;
            $subitem->nombre = $request->nombre;
            $subitem->unidad_medida_id = $request->unidad_medida_id;
            $subitem->cantidad = $request->cantidad ?? 1;
            $subitem->orden = $this->getNextOrden();
            $subitem->observacion = $request->observacion;
            $subitem->save();

            $subitem->load('unidadMedida');

            return response()->json([
                'success' => true,
                'message' => 'Subitem creado exitosamente',
                'data' => $subitem
            ]);
        } catch (Exception $e) {
            Log::error('Error al crear subitem: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el subitem: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Editar/actualizar un subitem existente
     */
    public function updateSubitem(Request $request, $subitemId): JsonResponse
    {
        try {
            $request->validate([
                'codigo' => 'required|string|max:50',
                'nombre' => 'required|string|max:255',
                'unidad_medida_id' => 'required|exists:unidades_medida,id',
                'cantidad' => 'nullable|numeric|min:0',
                'observacion' => 'nullable|string|max:500'
            ]);

            $subitem = CotizacionSubImtes::findOrFail($subitemId);

            $subitem->codigo = $request->codigo;
            $subitem->nombre = $request->nombre;
            $subitem->unidad_medida_id = $request->unidad_medida_id;
            $subitem->cantidad = $request->cantidad ?? 1;
            $subitem->observacion = $request->observacion;
            $subitem->save();

            $subitem->load('unidadMedida');

            return response()->json([
                'success' => true,
                'message' => 'Subitem actualizado exitosamente',
                'data' => $subitem
            ]);
        } catch (Exception $e) {
            Log::error('Error al actualizar subitem: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el subitem: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar un subitem específico
     */
    public function destroySubitem($subitemId): JsonResponse
    {
        try {
            $subitem = CotizacionSubImtes::findOrFail($subitemId);
            $cotizacionItemId = $subitem->cotizacion_item_id;
            
            $subitem->delete();

            return response()->json([
                'success' => true,
                'message' => 'Subitem eliminado exitosamente',
                'cotizacion_item_id' => $cotizacionItemId
            ]);
        } catch (Exception $e) {
            Log::error('Error al eliminar subitem: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el subitem: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener un subitem específico para edición
     */
    public function getSubitem($subitemId): JsonResponse
    {
        try {
            $subitem = CotizacionSubImtes::with('unidadMedida')
                ->findOrFail($subitemId);

            return response()->json([
                'success' => true,
                'data' => $subitem
            ]);
        } catch (Exception $e) {
            Log::error('Error al obtener subitem: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el subitem'
            ], 500);
        }
    }

    /**
     * Obtener siguiente número de orden
     */
    private function getNextOrden(): int
    {
        $maxOrden = CotizacionSubImtes::max('orden');
        return ($maxOrden ?? 0) + 1;
    }

    /**
     * Obtener subitems de un item específico
     */
    public function getItemSubitems($itemId): JsonResponse
    {
        try {
            $subitems = CotizacionSubImtes::where('cotizacion_item_id', $itemId)
                ->with('unidadMedida')
                ->orderBy('orden', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $subitems
            ]);
        } catch (Exception $e) {
            Log::error('Error al obtener subitems del item: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los subitems del item'
            ], 500);
        }
    }
}
