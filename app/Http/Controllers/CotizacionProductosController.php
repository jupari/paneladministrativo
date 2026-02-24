<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Categoria;
use App\Models\ItemPropio;
use App\Models\Parametrizacion;
use App\Models\Cargo;
use App\Models\Cotizacion;
use App\Models\CotizacionProducto;
use App\Http\Requests\CotizacionProductoRequest;
use App\Services\CotizacionProductoService;

class CotizacionProductosController extends Controller
{
    protected $cotizacionProductoService;

    public function __construct(CotizacionProductoService $cotizacionProductoService)
    {
        $this->cotizacionProductoService = $cotizacionProductoService;
    }
    /**
     * Obtener productos disponibles para cotización
     */
    public function obtenerProductos(Request $request): JsonResponse
    {
        try {
            Log::info('=== INICIO obtenerProductos ===', [
                'request_all' => $request->all(),
                'method' => $request->method(),
                'url' => $request->fullUrl()
            ]);

            // Obtener categorías y items propios desde la base de datos
            $query = ItemPropio::with(['categoria'])
                ->where('active', 1);

            // Filtro por término de búsqueda si se proporciona
            if ($request->has('buscar') && !empty($request->buscar)) {
                $termino = $request->buscar;
                $query->where(function($q) use ($termino) {
                    $q->where('nombre', 'like', "%{$termino}%")
                      ->orWhere('codigo', 'like', "%{$termino}%")
                      ->orWhere('descripcion', 'like', "%{$termino}%")
                      ->orWhereHas('categoria', function($catQuery) use ($termino) {
                          $catQuery->where('nombre', 'like', "%{$termino}%");
                      });
                });
            }

            // Filtro por categoría
            if ($request->has('categoria_id') && !empty($request->categoria_id)) {
                $query->where('categoria_id', $request->categoria_id);
            }

            $items = $query->get();

            // Formatear datos para la respuesta
            $productos = $items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'codigo' => $item->codigo ?? 'ITEM' . str_pad($item->id, 3, '0', STR_PAD_LEFT),
                    'nombre' => $item->nombre,
                    'precio' => (float) ($item->precio ?? 0),
                    'stock' => $item->stock ?? 0,
                    'categoria' => $item->categoria ? $item->categoria->nombre : 'Sin categoría',
                    'categoria_id' => $item->categoria_id,
                    'unidad' => $item->unidad_medida ?? 'Unidad',
                    'descripcion' => $item->descripcion ?? '',
                    'active' => $item->active
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
     * Obtener cargos disponibles para asignación de personal
     */
    // public function obtenerCargos(Request $request): JsonResponse
    // {
    //     try {
    //         // TODO: Reemplazar con consulta real a la base de datos cuando las tablas estén creadas
    //         // Datos simulados para demostración
    //         $cargos = [
    //             [
    //                 'id' => 1,
    //                 'nombre' => 'Ingeniero Civil',
    //                 'salario_base' => 3500.00,
    //                 'categoria' => 'Profesional',
    //                 'descripcion' => 'Ingeniero Civil con experiencia en obras'
    //             ],
    //             [
    //                 'id' => 2,
    //                 'nombre' => 'Arquitecto',
    //                 'salario_base' => 3200.00,
    //                 'categoria' => 'Profesional',
    //                 'descripcion' => 'Arquitecto especializado en diseño y supervisión'
    //             ],
    //             [
    //                 'id' => 3,
    //                 'nombre' => 'Maestro de Obra',
    //                 'salario_base' => 2500.00,
    //                 'categoria' => 'Técnico',
    //                 'descripcion' => 'Maestro de obra con experiencia en construcción'
    //             ],
    //             [
    //                 'id' => 4,
    //                 'nombre' => 'Oficial de Construcción',
    //                 'salario_base' => 1800.00,
    //                 'categoria' => 'Operativo',
    //                 'descripcion' => 'Oficial especializado en trabajos de construcción'
    //             ],
    //             [
    //                 'id' => 5,
    //                 'nombre' => 'Ayudante General',
    //                 'salario_base' => 1200.00,
    //                 'categoria' => 'Operativo',
    //                 'descripcion' => 'Ayudante general para apoyo en obras'
    //             ],
    //             [
    //                 'id' => 6,
    //                 'nombre' => 'Operador de Maquinaria',
    //                 'salario_base' => 2200.00,
    //                 'categoria' => 'Técnico',
    //                 'descripcion' => 'Operador de maquinaria pesada'
    //             ],
    //             [
    //                 'id' => 7,
    //                 'nombre' => 'Soldador',
    //                 'salario_base' => 2000.00,
    //                 'categoria' => 'Técnico',
    //                 'descripcion' => 'Soldador certificado para estructuras metálicas'
    //             ],
    //             [
    //                 'id' => 8,
    //                 'nombre' => 'Electricista',
    //                 'salario_base' => 2300.00,
    //                 'categoria' => 'Técnico',
    //                 'descripcion' => 'Electricista certificado para instalaciones'
    //             ],
    //             [
    //                 'id' => 9,
    //                 'nombre' => 'Plomero',
    //                 'salario_base' => 1900.00,
    //                 'categoria' => 'Técnico',
    //                 'descripcion' => 'Plomero especializado en instalaciones sanitarias'
    //             ],
    //             [
    //                 'id' => 10,
    //                 'nombre' => 'Supervisor de Obra',
    //                 'salario_base' => 2800.00,
    //                 'categoria' => 'Supervisión',
    //                 'descripcion' => 'Supervisor de obra con experiencia en gestión'
    //             ]
    //         ];

    //         return response()->json([
    //             'success' => true,
    //             'data' => $cargos,
    //             'message' => 'Cargos obtenidos exitosamente'
    //         ]);

    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Error al obtener cargos: ' . $e->getMessage()
    //         ], 500);
    //     }
    // }

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
            $cotizacionProducto = $this->cotizacionProductoService->agregarProducto($request->validated());

            Log::info('agregarProductosCotizacion - Producto agregado exitosamente', [
                'producto_id' => $cotizacionProducto->id,
                'cotizacion_id' => $request->cotizacion_id
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
                ->get();

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
            $categoriasConCostosCero = Categoria::whereIn('id', $categoriaIds)
                ->where('costos', 0)
                ->where('active', 1)
                ->pluck('id')
                ->toArray();

            $itemsParametrizacion = collect();

            // TODO: Remover este código de simulación cuando haya datos reales
            // Simulación temporal para pruebas
            if (empty($categoriasConCostosCero) && in_array(1, $categoriaIds)) {
                // Simular que la categoría 1 tiene costos = 0 para pruebas
                $categoriasConCostosCero = [1];

                // Simular datos de parametrización
                $parametrizacionesSimuladas = collect([
                    [
                        'id' => 1,
                        'categoria_id' => 1,
                        'cargo_id' => 1,
                        'valor_porcentaje' => 15.5,
                        'valor_admon' => 120000,
                        'valor_obra' => 850000,
                        'categoria' => ['id' => 1, 'nombre' => 'MATERIALES'],
                        'cargo' => ['id' => 1, 'nombre' => 'INGENIERO']
                    ],
                    [
                        'id' => 2,
                        'categoria_id' => 1,
                        'cargo_id' => 2,
                        'valor_porcentaje' => 8.2,
                        'valor_admon' => 95000,
                        'valor_obra' => 640000,
                        'categoria' => ['id' => 1, 'nombre' => 'MATERIALES'],
                        'cargo' => ['id' => 2, 'nombre' => 'SUPERVISOR']
                    ]
                ]);

                $itemsParametrizacion = $parametrizacionesSimuladas->map(function($param, $index) {
                    return [
                        'id' => 'param_' . $param['id'],
                        'categoria_id' => $param['categoria_id'],
                        'cargo_id' => $param['cargo_id'],
                        'nombre' => $param['cargo']['nombre'] . ' - ' . $param['categoria']['nombre'],
                        'codigo' => 'PARAM-' . $param['categoria_id'] . '-' . str_pad($param['id'], 3, '0', STR_PAD_LEFT),
                        'unidad_medida' => 'Porcentaje',
                        'orden' => 999 + $index,
                        'valor_porcentaje' => $param['valor_porcentaje'],
                        'valor_admon' => $param['valor_admon'],
                        'valor_obra' => $param['valor_obra'],
                        'tipo' => 'parametrizacion',
                        'categoria' => $param['categoria'],
                        'cargo' => $param['cargo'],
                        'descripcion' => "Cargo: {$param['cargo']['nombre']} | {$param['valor_porcentaje']}% | Admón: $" . number_format($param['valor_admon']) . " | Obra: $" . number_format($param['valor_obra'])
                    ];
                });
            } else
            // Fin de simulación temporal

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
                        'id' => 'param_' . $param->id, // Prefijo para distinguir de items propios
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

            // Combinar items propios con items de parametrización
            $todosLosItems = $itemsPropios->concat($itemsParametrizacion)
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
     * Obtener totales calculados de productos en cotización
     */
    public function obtenerTotales($cotizacionId): JsonResponse
    {
        try {
            // Simulación de cálculo de totales
            $totales = [
                'subtotal' => 1250.75,
                'descuento_total' => 62.54,
                'total' => 1188.21,
                'cantidad_productos' => 15,
                'cantidad_items' => 5
            ];

            return response()->json([
                'success' => true,
                'data' => $totales
            ]);

        } catch (\Exception $e) {
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
     * Actualizar totales de la cotización
     */
    private function actualizarTotalesCotizacion($cotizacionId)
    {
        try {
            $productos = CotizacionProducto::where('cotizacion_id', $cotizacionId)
                ->where('active', true)
                ->get();

            $subtotal = $productos->sum('valor_total');

            // Actualizar la cotización con los nuevos totales
            Cotizacion::where('id', $cotizacionId)->update([
                'subtotal' => $subtotal,
                'total' => $subtotal // Por ahora sin impuestos
            ]);

            Log::info('Totales actualizados para cotización', [
                'cotizacion_id' => $cotizacionId,
                'subtotal' => $subtotal,
                'productos_count' => $productos->count()
            ]);

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

            // DEBUG: Verificar datos básicos primero
            Log::info('🔍 DEBUG - Iniciando cálculo de totales', [
                'cotizacion_id' => $cotizacionId,
                'timestamp' => now()
            ]);

            $totales = $this->cotizacionProductoService->obtenerTotalesCotizacion($cotizacionId);

            // DEBUG: Mostrar resultado final
            Log::info('🎯 DEBUG - Resultado final del cálculo', [
                'cotizacion_id' => $cotizacionId,
                'totales_calculados' => $totales
            ]);

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
}
