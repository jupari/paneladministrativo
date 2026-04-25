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
                ->get();

            // Ordenamiento natural por el campo 'codigo'
            $subitemsArray = $subitems->toArray();
            usort($subitemsArray, function($a, $b) {
                return strnatcmp($a['codigo'], $b['codigo']);
            });

            return response()->json([
                'success' => true,
                'data' => $subitemsArray
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
            $resultado = $this->cotizacionItemService->eliminarItem($id);

            if (!$resultado['success']) {
                $status = !empty($resultado['blocked']) ? 422 : 500;
                return response()->json([
                    'success' => false,
                    'message' => $resultado['message'],
                ], $status);
            }

            return response()->json([
                'success' => true,
                'message' => $resultado['message'],
                'accion'  => $resultado['accion'],
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
                'codigo' => 'nullable|string|max:20',
                'nombre' => 'required|string|max:255',
                'unidad_medida_id' => 'required|exists:unidades_medida,id',
                'cantidad' => 'nullable|numeric|min:0',
                'observacion' => 'nullable|string|max:500',
                'cotizacion_item_id' => 'required|exists:ord_cotizaciones_items,id',
                'parent_codigo' => 'nullable|string|max:20'
            ]);
            $subitem = new CotizacionSubImtes();
            $subitem->cotizacion_item_id = $request->cotizacion_item_id;
            if (!$request->codigo) {
                $subitem->codigo = $this->getNextCodigo($request->cotizacion_item_id, $request->parent_codigo);
            } else {
                $subitem->codigo = $request->codigo;
            }
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
                'codigo' => 'required|string|max:20',
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
            $resultado = $this->cotizacionItemService->eliminarSubitem($subitemId);

            if (!$resultado['success']) {
                $status = !empty($resultado['blocked']) ? 422 : 500;
                return response()->json([
                    'success' => false,
                    'message' => $resultado['message'],
                ], $status);
            }

            return response()->json([
                'success' => true,
                'message' => $resultado['message'],
                'accion'  => $resultado['accion'],
            ]);
        } catch (Exception $e) {
            Log::error('Error al eliminar subitem: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el item'
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
     * Sugerir el siguiente código disponible para un subitem, tipo numeración Word, sin saltos
     * Si $parentCodigo es null, sugiere el siguiente principal (1, 2, 3...)
     * Si $parentCodigo es '1.1', sugiere '1.1.1', '1.1.2', etc., buscando el primer hueco
     */
    private function getNextCodigo($cotizacionItemId, $parentCodigo = null)
    {
        $query = CotizacionSubImtes::where('cotizacion_item_id', $cotizacionItemId);
        if ($parentCodigo) {
            $query = $query->where('codigo', 'like', $parentCodigo . '.%');
        }
        $codigos = $query->pluck('codigo')->toArray();

        $existentes = [];
        if ($parentCodigo) {
            foreach ($codigos as $codigo) {
                $parts = explode('.', $codigo);
                $last = intval(end($parts));
                $existentes[$last] = true;
            }
            $i = 1;
            while (isset($existentes[$i])) {
                $i++;
            }
            return $parentCodigo . '.' . $i;
        } else {
            // Buscar subitems directos (ej: 1.1, 1.2, 1.3) para el item principal
            $prefix = null;
            if (!empty($codigos)) {
                // Obtener el primer número del primer código existente (ej: 1.1 => 1)
                $first = explode('.', $codigos[0]);
                $prefix = $first[0];
            }
            if ($prefix) {
                // Buscar todos los subitems que empiezan con "$prefix."
                $subitems = CotizacionSubImtes::where('cotizacion_item_id', $cotizacionItemId)
                    ->where('codigo', 'like', $prefix . '.%')
                    ->pluck('codigo')->toArray();
                $existentes = [];
                foreach ($subitems as $codigo) {
                    $parts = explode('.', $codigo);
                    $last = intval(end($parts));
                    $existentes[$last] = true;
                }
                $i = 1;
                while (isset($existentes[$i])) {
                    $i++;
                }
                return $prefix . '.' . $i;
            } else {
                // Si no hay subitems, sugerir 1.1
                return '1.1';
            }
        }
    }

    /**
     * Endpoint para sugerir el siguiente código disponible para un subitem
     * GET /cotizacion/subitem/siguiente-codigo?cotizacion_item_id=...&parent_codigo=...
     */
    public function sugerirCodigoSubitem(Request $request): JsonResponse
    {
        $request->validate([
            'cotizacion_item_id' => 'required|exists:ord_cotizaciones_items,id',
            'parent_codigo' => 'nullable|string|max:20'
        ]);
        $codigo = $this->getNextCodigo($request->cotizacion_item_id, $request->parent_codigo);
        return response()->json([
            'success' => true,
            'codigo' => $codigo
        ]);
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
