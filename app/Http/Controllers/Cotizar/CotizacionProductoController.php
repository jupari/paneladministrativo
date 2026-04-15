<?php

namespace App\Http\Controllers\Cotizar;

use App\Http\Controllers\Controller;
use App\Http\Requests\CotizacionProductoRequest;
use App\Models\Categoria;
use App\Models\Cotizacion;
use App\Services\CotizacionProductoService;
use App\Services\CotizacionTotalesService;
use App\Models\CotizacionProducto;
use App\Models\ItemPropio;
use App\Models\Novedad;
use App\Models\Parametrizacion;
use App\Models\ParametrizacionCosto;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Validator;

class CotizacionProductoController extends Controller
{
    protected $cotizacionProductoService;

    public function __construct(CotizacionProductoService $cotizacionProductoService)
    {
        $this->cotizacionProductoService = $cotizacionProductoService;
    }

    /**
     * Display a listing of products for a specific quotation.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $cotizacionId = $request->get('cotizacion_id');
            $soloActivos = $request->get('solo_activos', true);

            if (!$cotizacionId) {
                return response()->json([
                    'success' => false,
                    'message' => 'ID de cotización requerido'
                ], 400);
            }

            $productos = $this->cotizacionProductoService->obtenerProductosCotizacion(
                (int) $cotizacionId,
                (bool) $soloActivos
            );

            $estadisticas = $this->cotizacionProductoService->obtenerEstadisticas((int) $cotizacionId);

            return response()->json([
                'success' => true,
                'data' => $productos,
                'estadisticas' => $estadisticas,
                'message' => 'Productos obtenidos exitosamente'
            ]);

        } catch (Exception $e) {
            Log::error("Error en CotizacionProductoController@index: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener productos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created product in the quotation.
     */
    public function store(CotizacionProductoRequest $request): JsonResponse
    {
        try {
            $producto = $this->cotizacionProductoService->agregarProducto($request->validated());

            // Actualizar totales automáticamente después de agregar producto
            $this->cotizacionProductoService->actualizarTotalesAutomaticamente($producto->cotizacion_id);

            return response()->json([
                'success' => true,
                'data' => $producto->load('producto'),
                'message' => 'Producto agregado exitosamente'
            ], 201);

        } catch (Exception $e) {
            Log::error("Error en CotizacionProductoController@store: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al agregar producto: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified product.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $producto = CotizacionProducto::with('producto', 'cotizacion')->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $producto,
                'message' => 'Producto obtenido exitosamente'
            ]);

        } catch (Exception $e) {
            Log::error("Error en CotizacionProductoController@show: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Producto no encontrado'
            ], 404);
        }
    }

    /**
     * Update the specified product in the quotation.
     */
    public function update(CotizacionProductoRequest $request, int $id): JsonResponse
    {
        try {
            $producto = $this->cotizacionProductoService->actualizarProducto($id, $request->validated());

            // Actualizar totales automáticamente después de actualizar producto
            $this->cotizacionProductoService->actualizarTotalesAutomaticamente($producto->cotizacion_id);

            return response()->json([
                'success' => true,
                'data' => $producto->load('producto'),
                'message' => 'Producto actualizado exitosamente'
            ]);

        } catch (Exception $e) {
            Log::error("Error en CotizacionProductoController@update: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar producto: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified product from the quotation.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $resultado = $this->cotizacionProductoService->eliminarProducto($id);

            // Actualizar totales automáticamente después de eliminar producto
            if ($resultado['success'] && isset($resultado['cotizacion_id'])) {
                $this->cotizacionProductoService->actualizarTotalesAutomaticamente($resultado['cotizacion_id']);
            }

            return response()->json([
                'success' => true,
                'message' => 'Producto eliminado exitosamente'
            ]);

        } catch (Exception $e) {
            Log::error("Error en CotizacionProductoController@destroy: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar producto: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Deactivate a product (soft delete).
     */
    public function desactivar(int $id): JsonResponse
    {
        try {
            $producto = $this->cotizacionProductoService->desactivarProducto($id);

            return response()->json([
                'success' => true,
                'data' => $producto,
                'message' => 'Producto desactivado exitosamente'
            ]);

        } catch (Exception $e) {
            Log::error("Error en CotizacionProductoController@desactivar: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al desactivar producto: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Activate a product.
     */
    public function activar(int $id): JsonResponse
    {
        try {
            $producto = $this->cotizacionProductoService->activarProducto($id);

            return response()->json([
                'success' => true,
                'data' => $producto,
                'message' => 'Producto activado exitosamente'
            ]);

        } catch (Exception $e) {
            Log::error("Error en CotizacionProductoController@activar: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al activar producto: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reorder products in quotation.
     */
    public function reordenar(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'cotizacion_id' => 'required|exists:ord_cotizacion,id',
                'nuevos_ordenes' => 'nullable|array',
                'nuevos_ordenes.*' => 'integer|min:1'
            ]);

            $this->cotizacionProductoService->reordenarProductos(
                (int) $request->cotizacion_id,
                $request->nuevos_ordenes ?? []
            );

            return response()->json([
                'success' => true,
                'message' => 'Productos reordenados exitosamente'
            ]);

        } catch (Exception $e) {
            Log::error("Error en CotizacionProductoController@reordenar: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al reordenar productos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Duplicate products from one quotation to another.
     */
    public function duplicar(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'cotizacion_origen_id' => 'required|exists:ord_cotizacion,id',
                'cotizacion_destino_id' => 'required|exists:ord_cotizacion,id|different:cotizacion_origen_id'
            ]);

            $productos = $this->cotizacionProductoService->duplicarProductos(
                (int) $request->cotizacion_origen_id,
                (int) $request->cotizacion_destino_id
            );

            return response()->json([
                'success' => true,
                'data' => $productos,
                'message' => 'Productos duplicados exitosamente'
            ]);

        } catch (Exception $e) {
            Log::error("Error en CotizacionProductoController@duplicar: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al duplicar productos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search for products to add to quotation.
     */
    // public function buscarProductos(Request $request): JsonResponse
    // {
    //     try {
    //         $termino = $request->get('termino', '');
    //         $limite = min((int) $request->get('limite', 50), 100); // Máximo 100

    //         $productos = $this->cotizacionProductoService->buscarProductos($termino, $limite);

    //         return response()->json([
    //             'success' => true,
    //             'data' => $productos,
    //             'message' => 'Productos encontrados exitosamente'
    //         ]);

    //     } catch (Exception $e) {
    //         Log::error("Error en CotizacionProductoController@buscarProductos: " . $e->getMessage());
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Error al buscar productos: ' . $e->getMessage()
    //         ], 500);
    //     }
    // }

    /**
     * Get product details by ID for auto-fill.
     */
    public function obtenerDetallesProducto(int $productoId): JsonResponse
    {
        try {
            $producto = CotizacionProducto::findOrFail($productoId);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $producto->id,
                    'nombre' => $producto->nombre,
                    'descripcion' => $producto->descripcion,
                    'codigo' => $producto->codigo,
                    'unidad_medida' => $producto->unidad_medida,
                    'precio' => $producto->valor_unitario,
                    'categoria' => $producto->categoria
                ],
                'message' => 'Detalles del producto obtenidos exitosamente'
            ]);

        } catch (Exception $e) {
            Log::error("Error en CotizacionProductoController@obtenerDetallesProducto: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Producto no encontrado'
            ], 404);
        }
    }

    public function obtenerProductos(Request $request): JsonResponse
    {
        try {
            Log::info('=== INICIO obtenerProductos ===', [
                'request_all' => $request->all(),
                'method' => $request->method(),
                'url' => $request->fullUrl()
            ]);

            // Buscar items por categoría desde tablas de parametrización
            $query = ParametrizacionCosto::query()
                ->leftJoin('categorias', 'parametrizacion_costos.categoria_id', '=', 'categorias.id')
                ->select(
                    'parametrizacion_costos.id',
                    'parametrizacion_costos.item',
                    'parametrizacion_costos.item_nombre',
                    'parametrizacion_costos.unidad_medida',
                    'parametrizacion_costos.costo_unitario',
                    'parametrizacion_costos.costo_dia',
                    'parametrizacion_costos.categoria_id',
                    'parametrizacion_costos.active',
                    'categorias.nombre as categoria_nombre'
                )
                ->where('parametrizacion_costos.active', 1);

            // Filtro por término de búsqueda si se proporciona
            if ($request->filled('buscar')) {
                $termino = $request->buscar;
                $query->where(function($q) use ($termino) {
                    $q->where('parametrizacion_costos.item_nombre', 'like', "%{$termino}%")
                      ->orWhere('parametrizacion_costos.item', 'like', "%{$termino}%")
                      ->orWhere('categorias.nombre', 'like', "%{$termino}%");
                });
            }

            // Filtro por categoría
            if ($request->filled('categoria_id')) {
                $query->where('parametrizacion_costos.categoria_id', $request->categoria_id);
            }

            $items = $query->get();

            // Formatear datos para la respuesta
            $productos = $items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'codigo' => $item->item ?? 'ITEM' . str_pad($item->id, 3, '0', STR_PAD_LEFT),
                    'nombre' => $item->item_nombre,
                    'precio' => (float) ($item->costo_unitario ?? $item->costo_dia ?? 0),
                    'stock' => 0,
                    'categoria' => $item->categoria_nombre ?? 'Sin categoría',
                    'categoria_id' => $item->categoria_id,
                    'unidad' => $item->unidad_medida ?? 'Unidad',
                    'descripcion' => '',
                    'active' => $item->active,
                    'fuente' => 'parametrizacion_costos'
                ];
            });

            Log::info('Productos obtenidos exitosamente', [
                'count' => $productos->count(),
                'filtros' => [
                    'buscar' => $request->buscar,
                    'categoria_id' => $request->categoria_id
                ]
            ]);

            return response()->json([
                'success' => true,
                'data' => $productos->values()->toArray(),
                'message' => 'Productos obtenidos exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener productos', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener productos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Guardar productos seleccionados en la cotización
     */
    public function guardarProductosCotizacion(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'cotizacion_id' => 'required|integer',
                'productos' => 'required|array',
                'productos.*.id' => 'required|integer',
                'productos.*.cantidad' => 'required|numeric|min:0.01',
                'productos.*.precio' => 'required|numeric|min:0',
                'personal' => 'array',
                'personal.*.cargo_id' => 'required|integer',
                'personal.*.cantidad' => 'required|integer|min:1',
                'personal.*.dias' => 'required|integer|min:1',
                'personal.*.costo_total' => 'required|numeric|min:0'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de entrada inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }

            // TODO: Implementar guardado real en base de datos cuando las tablas estén creadas

            // Por ahora simulamos el guardado exitoso
            $cotizacionId = $request->cotizacion_id;
            $productos = $request->productos;
            $personal = $request->personal ?? [];

            // Aquí iría la lógica para:
            // 1. Crear registros en tabla cotizacion_productos
            // 2. Crear registros en tabla cotizacion_personal
            // 3. Actualizar totales de cotización

            return response()->json([
                'success' => true,
                'message' => 'Productos y personal guardados exitosamente',
                'data' => [
                    'cotizacion_id' => $cotizacionId,
                    'productos_count' => count($productos),
                    'personal_count' => count($personal),
                    'total_productos' => array_sum(array_column($productos, 'total')),
                    'total_personal' => array_sum(array_column($personal, 'costo_total'))
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar productos y personal: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Agregar productos a cotización (nuevo método)
     */
    public function agregarProductosCotizacion(CotizacionProductoRequest $request): JsonResponse
    {
        try {
            Log::info('agregarProductosCotizacion - Iniciando', $request->validated());

            // Verificar que la cotización existe
            $cotizacion = Cotizacion::find($request->cotizacion_id);
            if (!$cotizacion) {
                Log::error('agregarProductosCotizacion - Cotización no encontrada', ['cotizacion_id' => $request->cotizacion_id]);
                return response()->json([
                    'success' => false,
                    'message' => 'La cotización especificada no existe'
                ], 404);
            }

            // Usar el servicio para agregar el producto
            $validated = $request->validated();
            Log::info('agregarProductosCotizacion - Datos validados para persistir', [
                'parametrizacion_id' => $validated['parametrizacion_id'] ?? 'NO PRESENTE',
                'item_propio_id' => $validated['item_propio_id'] ?? 'NO PRESENTE',
                'tabla_precios_id' => $validated['tabla_precios_id'] ?? 'NO PRESENTE',
                'cotizacion_item_id' => $validated['cotizacion_item_id'] ?? 'NO PRESENTE',
                'cotizacion_subitem_id' => $validated['cotizacion_subitem_id'] ?? 'NO PRESENTE',
                'categoria_id' => $validated['categoria_id'] ?? 'NO PRESENTE',
                'cargo_id' => $validated['cargo_id'] ?? 'NO PRESENTE',
            ]);
            $cotizacionProducto = $this->cotizacionProductoService->agregarProducto($validated);

            // Guardar novedades operativas si existen (sólo categoría NOMINA)
            $novedades = $request->input('novedades', []);
            if (!empty($novedades) && is_array($novedades)) {
                foreach ($novedades as $nov) {
                    $cantidad = (float) ($nov['cantidad'] ?? 0);
                    if ($cantidad > 0) {
                        \App\Models\CotizacionLista::create([
                            'cotizacion_id'           => $cotizacionProducto->cotizacion_id,
                            'cotizacion_producto_id'  => $cotizacionProducto->id,
                            'novedad_detalle_id'      => (int) $nov['novedad_detalle_id'],
                            'valor'                   => (float) ($nov['valor'] ?? 0),
                            'cantidad'                => $cantidad,
                            'subtotal'                => $cantidad * (float) ($nov['valor'] ?? 0),
                        ]);
                    }
                }
            }

            Log::info('agregarProductosCotizacion - Producto agregado exitosamente', [
                'producto_id' => $cotizacionProducto->id,
                'cotizacion_id' => $request->cotizacion_id,
                'parametrizacion_id_guardado' => $cotizacionProducto->parametrizacion_id,
                'item_propio_id_guardado' => $cotizacionProducto->item_propio_id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Producto agregado correctamente a la cotización',
                'data' => [
                    'id' => $cotizacionProducto->id,
                    'cotizacion_id' => $cotizacionProducto->cotizacion_id,
                    'producto_id' => $cotizacionProducto->producto_id,
                    'nombre' => $cotizacionProducto->nombre,
                    'cantidad' => $cotizacionProducto->cantidad,
                    'valor_unitario' => $cotizacionProducto->valor_unitario,
                    'valor_total' => $cotizacionProducto->valor_total,
                    'tipo_costo' => $cotizacionProducto->tipo_costo,
                    'categoria_id' => $cotizacionProducto->categoria_id,
                    'cargo_id' => $cotizacionProducto->cargo_id
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('agregarProductosCotizacion - Error general', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al agregar producto: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener productos de una cotización específica
     */
    public function obtenerProductosCotizacion(Request $request): JsonResponse
    {
        Log::info('=== INICIO obtenerProductosCotizacion ===', [
            'request_all' => $request->all(),
            'headers' => $request->headers->all(),
            'method' => $request->method(),
            'url' => $request->url()
        ]);

        try {
            $cotizacionId = $request->input('cotizacion_id');

            if (!$cotizacionId) {
                Log::warning('ID de cotización no proporcionado');
                return response()->json([
                    'success' => false,
                    'message' => 'ID de cotización requerido'
                ], 400);
            }

            Log::info('Obteniendo productos de cotización', ['cotizacion_id' => $cotizacionId]);

            $productos = $this->cotizacionProductoService->obtenerProductosCotizacion($cotizacionId);

            Log::info('Productos obtenidos exitosamente', ['count' => $productos->count()]);

            $response = response()->json([
                'success' => true,
                'data' => $productos,
                'message' => 'Productos obtenidos exitosamente'
            ]);

            Log::info('Respuesta creada exitosamente');
            return $response;
        } catch (\Exception $e) {
            Log::error('Error al obtener productos de cotización', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'cotizacion_id' => $request->input('cotizacion_id')
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor al obtener productos'
            ], 500);
        }
    }

    /**
     * Quitar elementos de la cotización
     */
    public function quitarElementosCotizacion(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'cotizacion_id' => 'required|integer',
                'elementos' => 'required|array',
                'elementos.*.id' => 'required|integer',
                'elementos.*.tipo' => 'required|string|in:producto,personal'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de entrada inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }

            // TODO: Implementar eliminación real en base de datos cuando las tablas estén creadas

            $cotizacionId = $request->cotizacion_id;
            $elementos = $request->elementos;

            // Aquí iría la lógica para:
            // 1. Eliminar registros de cotizacion_productos y cotizacion_personal
            // 2. Actualizar totales de cotización

            return response()->json([
                'success' => true,
                'message' => 'Elementos removidos exitosamente',
                'data' => [
                    'cotizacion_id' => $cotizacionId,
                    'elementos_removidos' => count($elementos)
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al quitar elementos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener categorías del modelo real
     */
    public function obtenerCategorias(): JsonResponse
    {
        try {
            $categorias = Categoria::where('active', 1)
                ->select('id', 'nombre')
                ->orderBy('nombre')
                ->get()
                ->map(function ($categoria) {
                    $categoria->tipo = mb_strtoupper($categoria->nombre) === 'NOMINA' ? 'nomina' : 'estandar';
                    return $categoria;
                });

            return response()->json([
                'success' => true,
                'data' => $categorias
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener categorías: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener items propios por categoría
     */
    public function obtenerItemsPorCategoria(Request $request): JsonResponse
    {
        try {
            $categoriaIds = $request->input('categoria_ids', []);

            if (empty($categoriaIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Debe seleccionar al menos una categoría'
                ]);
            }

            // Traer categorías solicitadas (nombre y bandera costos)
            $categorias = Categoria::whereIn('id', $categoriaIds)
                ->where('active', 1)
                ->select('id', 'nombre', 'costos')
                ->get();

            // Detectar IDs de categoría NOMINA
            $categoriasNominaIds = $categorias
                ->filter(fn($c) => mb_strtoupper($c->nombre) === 'NOMINA')
                ->pluck('id')
                ->values()
                ->toArray();

            // Obtener items propios regulares
            $itemsPropios = collect(ItemPropio::whereIn('categoria_id', $categoriaIds)
                ->where('active', 1)
                ->with(['categoria:id,nombre', 'unidadMedida:sigla,nombre'])
                ->select('id', 'categoria_id', 'nombre', 'codigo', 'unidad_medida', 'orden')
                ->orderBy('categoria_id')
                ->orderBy('orden')
                ->orderBy('nombre')
                ->get()
                ->map(function($item) {
                    // Generar descripción dinámicamente ya que no existe en BD
                    $itemArray = $item->toArray();
                    $itemArray['descripcion'] = "Item propio de la categoría {$item->categoria->nombre}. Código: {$item->codigo}";
                    return $itemArray;
                }));

            // Verificar categorías con costos = 0 y obtener datos de parametrización
            $categoriasConCostosCero = $categorias
                ->where('costos', 0)
                ->pluck('id')
                ->toArray();

            $itemsParametrizacion = collect();

            if (!empty($categoriasConCostosCero)) {
                // Obtener datos de parametrización para categorías con costos = 0
                $parametrizaciones = Parametrizacion::whereIn('categoria_id', $categoriasConCostosCero)
                    ->where('active', 1)
                    ->with(['categoria:id,nombre', 'cargo:id,nombre'])
                    ->get();

                // Formatear datos de parametrización para que coincidan con la estructura de items propios
                $itemsParametrizacion = $parametrizaciones->map(function($param, $index) {
                    $cargoNombre = $param->cargo->nombre ?? 'Sin Cargo';
                    $categoriaNombre = $param->categoria->nombre ?? 'N/A';

                    return [
                        'id' =>$param->id, // Prefijo para distinguir de items propios
                        'categoria_id' => $param->categoria_id,
                        'cargo_id' => $param->cargo_id,
                        'nombre' => $cargoNombre . ' - ' . $categoriaNombre,
                        'codigo' => 'PARAM-' . $param->categoria_id . '-' . str_pad($param->id, 3, '0', STR_PAD_LEFT),
                        'unidad_medida' => 'Porcentaje',
                        'orden' => 999 + $index, // Ordenar después de items propios
                        'valor_porcentaje' => $param->valor_porcentaje,
                        'valor_admon' => $param->valor_admon,
                        'valor_obra' => $param->valor_obra,
                        'tipo' => 'parametrizacion',
                        'categoria' => [
                            'id' => $param->categoria_id,
                            'nombre' => $categoriaNombre
                        ],
                        'cargo' => [
                            'id' => $param->cargo_id,
                            'nombre' => $cargoNombre
                        ],
                        'descripcion' => "Cargo: {$cargoNombre} | {$param->valor_porcentaje}% | Admón: $" . number_format($param->valor_admon) . " | Obra: $" . number_format($param->valor_obra)
                    ];
                });
            }

            // Si la categoría NOMINA está presente, evitamos duplicar cargos: solo usamos tabla de precios
            if (!empty($categoriasNominaIds)) {
                $itemsParametrizacion = $itemsParametrizacion->reject(function ($item) use ($categoriasNominaIds) {
                    return in_array($item['categoria_id'], $categoriasNominaIds, true);
                });
            }

            // Para categorías NOMINA traer cargos desde cargos_tabla_precios
            $itemsNomina = collect();
            if (!empty($categoriasNominaIds) && Schema::hasTable('cargos_tabla_precios')) {
                $categoriaNominaId = $categoriasNominaIds[0];
                $categoriaNominaNombre = optional($categorias->firstWhere('id', $categoriaNominaId))->nombre ?? 'NOMINA';

                $cargosTabla = DB::table('cargos_tabla_precios as tp')
                    ->join('cargos as c', 'c.id', '=', 'tp.cargo_id')
                    ->where('c.active', 1)
                    ->select([
                        'tp.id as tabla_id',
                        'tp.cargo_id',
                        'c.nombre as cargo',
                        'tp.base_costo_dia',
                        'tp.base_costo_hora',
                        'tp.hora_ordinaria',
                        'tp.valor_dia_ordinario',
                    ])
                    ->orderBy('c.nombre')
                    ->get();

                $itemsNomina = $cargosTabla->map(function ($row) use ($categoriaNominaId, $categoriaNominaNombre) {
                    return [
                        'id' => $row->cargo_id,
                        'tabla_id' => $row->tabla_id,
                        'categoria_id' => $categoriaNominaId,
                        'nombre' => $row->cargo,
                        'codigo' => 'CARGO-' . $row->cargo_id,
                        'orden' => 0,
                        'unidad_medida' => 'SERVICIO',
                        'tipo' => 'cargo_tabla',
                        'precio' => (float) $row->hora_ordinaria,
                        'categoria' => [
                            'id' => $categoriaNominaId,
                            'nombre' => $categoriaNominaNombre,
                        ],
                        'cargo' => [
                            'id' => $row->cargo_id,
                            'nombre' => $row->cargo,
                        ],
                        'costo_hora' => (float) $row->hora_ordinaria,
                        'costo_dia' => (float) $row->valor_dia_ordinario,
                        'base_costo_hora' => (float) $row->base_costo_hora,
                        'base_costo_dia' => (float) $row->base_costo_dia,
                        'descripcion' => sprintf(
                            'Hora ordinaria: $%s | Día ordinario: $%s',
                            number_format($row->hora_ordinaria, 0, '.', ','),
                            number_format($row->valor_dia_ordinario, 0, '.', ',')
                        ),
                    ];
                });
            }

            // Combinar items propios con items de parametrización y nómina
            $todosLosItems = $itemsPropios
                ->concat($itemsParametrizacion)
                ->concat($itemsNomina)
                ->sortBy(['categoria_id', 'orden', 'nombre'])
                ->values();

            return response()->json([
                'success' => true,
                'data' => $todosLosItems,
                'info' => [
                    'items_propios' => $itemsPropios->count(),
                    'items_parametrizacion' => $itemsParametrizacion->count(),
                    'categorias_con_costos_cero' => $categoriasConCostosCero
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener items propios: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener elementos de cotización existente
     */
    public function obtenerElementosCotizacion($id): JsonResponse
    {
        try {
            // Simulando elementos existentes en una cotización
            $elementos = [
                [
                    'id' => 1,
                    'tipo' => 'Producto',
                    'descripcion' => 'Cemento Portland',
                    'cantidad' => 20,
                    'precio_unitario' => 25.50,
                    'costo_total' => 510.00
                ],
                [
                    'id' => 2,
                    'tipo' => 'Salario',
                    'categoria' => 'Ingeniería',
                    'descripcion' => 'Ingeniero Civil',
                    'tipo_costo' => 'COSTO_MES',
                    'cantidad_dias' => 30,
                    'valor_unitario' => 7000.00,
                    'costo_total' => 7000.00
                ],
                [
                    'id' => 3,
                    'tipo' => 'Producto',
                    'descripcion' => 'Arena Fina',
                    'cantidad' => 5,
                    'precio_unitario' => 15.00,
                    'costo_total' => 75.00
                ]
            ];

            return response()->json([
                'success' => true,
                'data' => $elementos
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener elementos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar producto específico en cotización
     */
    public function actualizarProducto(Request $request, $id): JsonResponse
    {
        try {
            Log::info('=== INICIO actualizarProducto ===', [
                'id' => $id,
                'request_data' => $request->all(),
                'user_id' => auth()->id()
            ]);

            // Validar datos de entrada
            $validator = Validator::make($request->all(), [
                'cantidad' => 'required|numeric|min:0.001',
                'valor_unitario' => 'required|numeric|min:0',
                'descuento_porcentaje' => 'nullable|numeric|min:0|max:100',
                'observaciones' => 'nullable|string|max:500'
            ]);

            if ($validator->fails()) {
                Log::warning('Validación fallida', ['errors' => $validator->errors()]);
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de validación incorrectos',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Buscar el producto en cotización
            $cotizacionProducto = CotizacionProducto::findOrFail($id);
            Log::info('Producto encontrado', [
                'producto_id' => $cotizacionProducto->id,
                'cotizacion_id' => $cotizacionProducto->cotizacion_id,
                'valores_actuales' => [
                    'cantidad' => $cotizacionProducto->cantidad,
                    'valor_unitario' => $cotizacionProducto->valor_unitario,
                    'descuento_porcentaje' => $cotizacionProducto->descuento_porcentaje
                ]
            ]);

            // Preparar datos para actualización
            $datos = [
                'cantidad' => $request->cantidad,
                'valor_unitario' => $request->valor_unitario,
                'descuento_porcentaje' => $request->descuento_porcentaje ?? 0,
                'observaciones' => $request->observaciones,
                'updated_at' => now()
            ];

            // Calcular valor total
            $subtotal = $datos['cantidad'] * $datos['valor_unitario'];
            $descuentoValor = $subtotal * ($datos['descuento_porcentaje'] / 100);
            $datos['valor_total'] = $subtotal - $descuentoValor;

            Log::info('Datos calculados', [
                'subtotal' => $subtotal,
                'descuento_valor' => $descuentoValor,
                'valor_total' => $datos['valor_total']
            ]);

            // Actualizar usando el servicio
            $productoActualizado = $this->cotizacionProductoService->actualizarProducto($id, $datos);

            Log::info('Producto actualizado exitosamente', [
                'producto_actualizado' => $productoActualizado->toArray()
            ]);

            // Recalcular totales de la cotización
            $this->cotizacionProductoService->recalcularTotalesCotizacion($cotizacionProducto->cotizacion_id);

            // Obtener totales actualizados para respuesta
            $totalesActualizados = $this->cotizacionProductoService->obtenerTotalesCotizacion($cotizacionProducto->cotizacion_id);

            Log::info('Totales de cotización recalculados', [
                'totales' => $totalesActualizados
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Producto actualizado correctamente',
                'data' => [
                    'producto' => $productoActualizado,
                    'totales_actualizados' => $totalesActualizados
                ],
                'totales' => $totalesActualizados // Para compatibilidad con frontend
            ]);

        } catch (ModelNotFoundException $e) {
            Log::error('Producto no encontrado', ['id' => $id]);
            return response()->json([
                'success' => false,
                'message' => 'Producto no encontrado'
            ], 404);

        } catch (\Exception $e) {
            Log::error('Error actualizando producto', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar producto: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar producto de cotización
     */
    public function eliminarProducto($id): JsonResponse
    {
        try {
            Log::info('Solicitud de eliminación de producto', ['producto_id' => $id]);

            // Obtener información del producto antes de eliminarlo
            $producto = \App\Models\CotizacionProducto::find($id);

            if (!$producto) {
                return response()->json([
                    'success' => false,
                    'message' => 'Producto no encontrado'
                ], 404);
            }

            $cotizacionId = $producto->cotizacion_id;
            $nombreProducto = $producto->nombre;

            // Eliminar el producto usando el servicio
            $resultado = $this->cotizacionProductoService->eliminarProducto($id);

            if ($resultado) {
                // Recalcular totales usando el servicio actualizado
                $this->cotizacionProductoService->recalcularTotalesCotizacion($cotizacionId);

                // Obtener los totales actualizados
                $totalesActualizados = $this->cotizacionProductoService->obtenerTotalesCotizacion($cotizacionId);

                Log::info('Producto eliminado exitosamente', [
                    'producto_id' => $id,
                    'producto_nombre' => $nombreProducto,
                    'cotizacion_id' => $cotizacionId
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Producto eliminado de la cotización correctamente',
                    'data' => [
                        'producto_eliminado' => [
                            'id' => $id,
                            'nombre' => $nombreProducto
                        ],
                        'totales_actualizados' => $totalesActualizados
                    ]
                ]);
            } else {
                throw new \Exception('No se pudo eliminar el producto');
            }

        } catch (\Exception $e) {
            Log::error('Error al eliminar producto', [
                'producto_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar producto: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reordenar productos en cotización
     */
    public function reordenarProductos(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'cotizacion_id' => 'required|integer',
                'productos' => 'required|array',
                'productos.*.id' => 'required|integer',
                'productos.*.orden' => 'required|integer|min:1'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de validación incorrectos',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Simulación de reordenamiento exitoso
            return response()->json([
                'success' => true,
                'message' => 'Productos reordenados correctamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al reordenar productos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Duplicar productos de una cotización a otra
     */
    public function duplicarProductos(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'cotizacion_origen_id' => 'required|integer',
                'cotizacion_destino_id' => 'required|integer',
                'productos_ids' => 'nullable|array'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de validación incorrectos',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Simulación de duplicación exitosa
            return response()->json([
                'success' => true,
                'message' => 'Productos duplicados correctamente',
                'data' => [
                    'productos_duplicados' => 3,
                    'cotizacion_destino_id' => $request->cotizacion_destino_id
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al duplicar productos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Buscar productos por término de búsqueda
     */
    public function buscarProductos(Request $request): JsonResponse
    {
        try {
            $termino = $request->get('q', '');
            $limite = $request->get('limite', 20);

            if (strlen($termino) < 2) {
                return response()->json([
                    'success' => false,
                    'message' => 'El término de búsqueda debe tener al menos 2 caracteres'
                ], 400);
            }

            // Simulación de búsqueda
            $productosEncontrados = [
                [
                    'id' => 1,
                    'codigo' => 'CEM001',
                    'nombre' => 'Cemento Portland Tipo I',
                    'precio' => 25.50,
                    'unidad' => 'Bulto',
                    'categoria' => 'Materiales',
                    'stock' => 100
                ],
                [
                    'id' => 9,
                    'codigo' => 'CEM002',
                    'nombre' => 'Cemento Portland Tipo III',
                    'precio' => 28.00,
                    'unidad' => 'Bulto',
                    'categoria' => 'Materiales',
                    'stock' => 75
                ]
            ];

            return response()->json([
                'success' => true,
                'data' => array_slice($productosEncontrados, 0, $limite),
                'total' => count($productosEncontrados)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error en búsqueda: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener totales calculados de la cotización (productos + utilidades + conceptos + listas)
     */
    public function obtenerTotales($cotizacionId): JsonResponse
    {
        try {
            $cotizacion = Cotizacion::with(['utilidades', 'conceptos.concepto', 'productos'])
                ->findOrFail($cotizacionId);

            $cotizacionActualizada = app(CotizacionTotalesService::class)->recalcular($cotizacion);

            return response()->json([
                'success' => true,
                'data' => [
                    'subtotal'          => (float) $cotizacionActualizada->subtotal,
                    'descuento_total'   => (float) $cotizacionActualizada->descuento,
                    'impuestos'         => (float) $cotizacionActualizada->total_impuesto,
                    'total'             => (float) $cotizacionActualizada->total,
                    'cantidad_productos' => $cotizacion->productos->where('active', true)->count(),
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error al calcular totales de cotización', [
                'cotizacion_id' => $cotizacionId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al calcular totales: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Aplicar descuento global a todos los productos
     */
    public function aplicarDescuentoGlobal(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'cotizacion_id' => 'required|integer',
                'descuento_porcentaje' => 'required|numeric|min:0|max:100'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de validación incorrectos',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Simulación de aplicación de descuento global
            return response()->json([
                'success' => true,
                'message' => 'Descuento global aplicado correctamente',
                'data' => [
                    'descuento_aplicado' => $request->descuento_porcentaje,
                    'productos_afectados' => 5
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al aplicar descuento: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar totales de la cotización (incluye utilidades, conceptos, retenciones y listas)
     */
    private function actualizarTotalesCotizacion($cotizacionId)
    {
        try {
            $cotizacion = Cotizacion::with(['utilidades', 'conceptos.concepto', 'productos'])
                ->findOrFail($cotizacionId);

            app(CotizacionTotalesService::class)->recalcular($cotizacion);

            Log::info('Totales actualizados para cotización', ['cotizacion_id' => $cotizacionId]);

        } catch (\Exception $e) {
            Log::error('Error actualizando totales de cotización', [
                'cotizacion_id' => $cotizacionId,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Obtener totales completos de cotización incluyendo conceptos
     */
    public function obtenerTotalesCotizacion(Request $request): JsonResponse
    {
        try {
            $cotizacionId = $request->input('cotizacion_id');

            if (!$cotizacionId) {
                return response()->json([
                    'success' => false,
                    'message' => 'ID de cotización requerido'
                ], 400);
            }

            Log::info('🔍 Iniciando cálculo de totales completos', ['cotizacion_id' => $cotizacionId]);

            $cotizacion = Cotizacion::with(['utilidades', 'conceptos.concepto', 'productos'])
                ->findOrFail($cotizacionId);

            $cotizacionActualizada = app(CotizacionTotalesService::class)->recalcular($cotizacion);

            $totales = [
                'subtotal'    => (float) $cotizacionActualizada->subtotal,
                'descuento'   => (float) $cotizacionActualizada->descuento,
                'impuestos'   => (float) $cotizacionActualizada->total_impuesto,
                'total'       => (float) $cotizacionActualizada->total,
                // Aliases para compatibilidad con el frontend
                'descuento_total' => (float) $cotizacionActualizada->descuento,
            ];

            Log::info('🎯 Totales calculados', ['cotizacion_id' => $cotizacionId, 'totales' => $totales]);

            return response()->json([
                'success' => true,
                'data' => $totales,
                'message' => 'Totales obtenidos exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener totales de cotización', [
                'error' => $e->getMessage(),
                'cotizacion_id' => $request->input('cotizacion_id')
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor al obtener totales'
            ], 500);
        }
    }

    /**
     * Obtener valores por defecto para un item según el tipo de costo
     */
    public function obtenerValoresPorDefecto(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'item_id' => 'required',
                'tipo_item' => 'required|in:propio,cargo,cargo_tabla',
                'tipo_costo' => 'required|in:unitario,hora,dia'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos inválidos',
                    'errors' => $validator->errors()
                ], 400);
            }

            $itemId = $request->input('item_id');
            $tipoItem = $request->input('tipo_item');
            $tipoCosto = $request->input('tipo_costo');

            $valores = [
                'costo' => 0,
                'unidad_medida' => '',
                'encontrado' => false
            ];

            if ($tipoItem === 'propio') {
                // Buscar el ItemPropio primero para obtener su código
                $itemPropio = \App\Models\ItemPropio::where('id', $itemId)
                    ->where('active', 1)
                    ->first();

                if ($itemPropio) {
                    // Buscar en ParametrizacionCosto usando el código del ItemPropio
                    $parametrizacionCosto = \App\Models\ParametrizacionCosto::where('item', $itemPropio->codigo)
                        ->where('active', 1)
                        ->first();

                    if ($parametrizacionCosto) {
                        $valores['encontrado'] = true;
                        $valores['unidad_medida'] = $parametrizacionCosto->unidad_medida ?? $itemPropio->unidad_medida ?? '';

                        $costoDia      = (float) ($parametrizacionCosto->costo_dia ?? 0);
                        $costoUnitario = (float) ($parametrizacionCosto->costo_unitario ?? 0);
                        $costoHora     = $costoDia > 0 ? round($costoDia / 8, 2) : 0;

                        // Todos los costos disponibles (para cache en el frontend)
                        $valores['costos_disponibles'] = [
                            'unitario' => $costoUnitario,
                            'dia'      => $costoDia,
                            'hora'     => $costoHora,
                        ];

                        // Costo para el tipo solicitado
                        switch ($tipoCosto) {
                            case 'unitario':
                                $valores['costo'] = $costoUnitario;
                                break;
                            case 'hora':
                                $valores['costo'] = $costoHora;
                                break;
                            case 'dia':
                                $valores['costo'] = $costoDia;
                                break;
                        }
                    } else {
                        // Si no hay parametrización de costos, al menos obtener unidad de medida
                        $valores['unidad_medida'] = $itemPropio->unidad_medida ?? '';
                    }
                }
            } elseif ($tipoItem === 'cargo') {
                // Buscar en Parametrizacion para cargos
                $parametrizacion = \App\Models\Parametrizacion::where('id', $itemId)
                    ->where('active', 1)
                    ->with('cargo')
                    ->first();

                if ($parametrizacion) {
                    $valores['encontrado'] = true;
                    $valores['unidad_medida'] = 'Porcentaje'; // Los cargos siempre usan porcentaje

                    // Para cargos, los valores dependen del contexto de obra/administración
                    switch ($tipoCosto) {
                        case 'unitario':
                        case 'hora':
                        case 'dia':
                            // Para cargos se puede usar el valor_porcentaje como base
                            $valores['costo'] = $parametrizacion->valor_porcentaje ?? 0;
                            break;
                    }
                }
            } elseif ($tipoItem === 'cargo_tabla') {
                $cargoPrecio = DB::table('cargos_tabla_precios')
                    ->where(function ($q) use ($itemId) {
                        $q->where('cargo_id', $itemId)->orWhere('id', $itemId);
                    })
                    ->first();

                if ($cargoPrecio) {
                    $valores['encontrado'] = true;
                    $valores['unidad_medida'] = 'SERVICIO';

                    switch ($tipoCosto) {
                        case 'hora':
                            $valores['costo'] = (float) $cargoPrecio->hora_ordinaria;
                            break;
                        case 'dia':
                            $valores['costo'] = (float) $cargoPrecio->valor_dia_ordinario;
                            break;
                        case 'unitario':
                        default:
                            $valores['costo'] = (float) $cargoPrecio->hora_ordinaria;
                            break;
                    }
                }
            }

            return response()->json([
                'success' => true,
                'data' => $valores
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener valores por defecto', [
                'error' => $e->getMessage(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener valores por defecto: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener novedades con grupo_cotiza = true para cotizaciones NOMINA
     */
    public function obtenerNovedadesGrupoCotiza(): JsonResponse
    {
        try {
            $novedades = Novedad::where('grupo_cotiza', true)
                ->where('active', true)
                ->with(['detalles' => function ($q) {
                    $q->select('id', 'novedad_id', 'nombre', 'valor_operativo')
                      ->where('valor_operativo', '>', 0)
                      ->orderBy('nombre');
                }])
                ->orderBy('nombre')
                ->get(['id', 'nombre']);

            return response()->json([
                'success' => true,
                'data'    => $novedades,
            ]);
        } catch (\Exception $e) {
            Log::error('obtenerNovedadesGrupoCotiza error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener novedades: ' . $e->getMessage(),
            ], 500);
        }
    }
}
