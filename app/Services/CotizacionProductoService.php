<?php

namespace App\Services;

use App\Models\Cotizacion;
use App\Models\CotizacionProducto;
use App\Models\CotizacionConcepto;
use App\Models\CotizacionUtilidad;
use App\Models\CotizacionSubImtes;
use App\Models\Producto;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class CotizacionProductoService
{
    /**
     * Agregar producto a cotización
     */
    public function agregarProducto(array $datos): CotizacionProducto
    {
        return DB::transaction(function () use ($datos) {
            // Si se proporciona producto_id, copiar datos del producto
            if (isset($datos['producto_id']) && $datos['producto_id']) {
                $producto = CotizacionSubImtes::findOrFail($datos['producto_id']);
                $datos = array_merge([
                    'nombre' => $producto->nombre,
                    'descripcion' => $producto->nombre,
                    'codigo' => $producto->codigo,
                    'unidad_medida' => $datos['unidad_medida'],
                    'valor_unitario' => $datos['valor_unitario'],
                ], $datos);
            }

            // Calcular valor unitario basado en configuración de costos si está disponible
            $datos = $this->calcularValorPorConfiguracion($datos);

            // Establecer orden automático si no se proporciona
            if (!isset($datos['orden']) || !$datos['orden']) {
                $datos['orden'] = $this->obtenerSiguienteOrden($datos['cotizacion_id']);
            }

            // Crear el producto en la cotización
            $cotizacionProducto = CotizacionProducto::create($datos);

            // Recalcular totales de la cotización
            $this->recalcularTotalesCotizacion($datos['cotizacion_id']);

            return $cotizacionProducto;
        });
    }

    /**
     * Actualizar producto en cotización
     */
    public function actualizarProducto(int $id, array $datos): CotizacionProducto
    {
        return DB::transaction(function () use ($id, $datos) {
            $cotizacionProducto = CotizacionProducto::findOrFail($id);

            // Calcular valor total si se proporcionan cantidad y valor unitario
            if (isset($datos['cantidad']) && isset($datos['valor_unitario'])) {
                $cantidad = $datos['cantidad'];
                $valorUnitario = $datos['valor_unitario'];
                $descuentoPorcentaje = $datos['descuento_porcentaje'] ?? 0;
                $descuentoValor = $datos['descuento_valor'] ?? 0;

                // Calcular subtotal
                $subtotal = $cantidad * $valorUnitario;

                // Calcular descuento por porcentaje si no hay descuento fijo
                if ($descuentoValor == 0 && $descuentoPorcentaje > 0) {
                    $descuentoValor = $subtotal * ($descuentoPorcentaje / 100);
                    $datos['descuento_valor'] = $descuentoValor;
                }

                // Calcular valor total
                $datos['valor_total'] = $subtotal - $descuentoValor;

                Log::info('Calculando valores de producto actualizado', [
                    'producto_id' => $id,
                    'cantidad' => $cantidad,
                    'valor_unitario' => $valorUnitario,
                    'descuento_porcentaje' => $descuentoPorcentaje,
                    'descuento_valor' => $descuentoValor,
                    'valor_total' => $datos['valor_total']
                ]);
            }

            $cotizacionProducto->update($datos);

            // Recalcular totales de la cotización
            //$this->recalcularTotalesCotizacion($cotizacionProducto->cotizacion_id);

            Log::info('Producto actualizado exitosamente', [
                'producto_id' => $id,
                'cotizacion_id' => $cotizacionProducto->cotizacion_id
            ]);

            return $cotizacionProducto->fresh();
        });
    }

    /**
     * Eliminar producto de cotización
     */
    public function eliminarProducto(int $id): array
    {
        return DB::transaction(function () use ($id) {
            // Verificar que el producto existe
            $cotizacionProducto = CotizacionProducto::find($id);

            if (!$cotizacionProducto) {
                throw new \Exception("Producto con ID {$id} no encontrado");
            }

            $cotizacionId = $cotizacionProducto->cotizacion_id;

            Log::info('Iniciando eliminación de producto', [
                'producto_id' => $id,
                'cotizacion_id' => $cotizacionId,
                'producto_nombre' => $cotizacionProducto->nombre
            ]);

            // Eliminar el producto
            $eliminado = $cotizacionProducto->delete();

            if (!$eliminado) {
                throw new \Exception("No se pudo eliminar el producto de la base de datos");
            }

            // Reordenar productos restantes
            $this->reordenarProductos($cotizacionId);

            Log::info('Producto eliminado exitosamente', [
                'producto_id' => $id,
                'cotizacion_id' => $cotizacionId
            ]);

            return ['success' => true, 'cotizacion_id' => $cotizacionId];
        });
    }

    /**
     * Desactivar producto (soft delete)
     */
    public function desactivarProducto(int $id): CotizacionProducto
    {
        return DB::transaction(function () use ($id) {
            $cotizacionProducto = CotizacionProducto::findOrFail($id);
            $cotizacionProducto->update(['active' => false]);

            // Recalcular totales de la cotización
            $this->recalcularTotalesCotizacion($cotizacionProducto->cotizacion_id);

            return $cotizacionProducto;
        });
    }

    /**
     * Activar producto
     */
    public function activarProducto(int $id): CotizacionProducto
    {
        return DB::transaction(function () use ($id) {
            $cotizacionProducto = CotizacionProducto::findOrFail($id);
            $cotizacionProducto->update(['active' => true]);

            // Recalcular totales de la cotización
            $this->recalcularTotalesCotizacion($cotizacionProducto->cotizacion_id);

            return $cotizacionProducto;
        });
    }

    /**
     * Obtener productos de una cotización
     */
    public function obtenerProductosCotizacion(int $cotizacionId, bool $soloActivos = true): Collection
    {
        try {
            $query = CotizacionProducto::where('cotizacion_id', $cotizacionId)
                ->orderBy('orden', 'asc');

            if ($soloActivos) {
                $query->where('active', true);
            }

            return $query->get();
        } catch (\Exception $e) {
            Log::error('Error al obtener productos de cotización', [
                'cotizacion_id' => $cotizacionId,
                'error' => $e->getMessage()
            ]);

            return collect(); // Retornar colección vacía en caso de error
        }
    }

    /**
     * Duplicar productos de una cotización a otra
     */
    public function duplicarProductos(int $cotizacionOrigenId, int $cotizacionDestinoId): Collection
    {
        return DB::transaction(function () use ($cotizacionOrigenId, $cotizacionDestinoId) {
            $productosOrigen = $this->obtenerProductosCotizacion($cotizacionOrigenId);
            $productosNuevos = collect();

            foreach ($productosOrigen as $producto) {
                $datosNuevos = $producto->toArray();
                unset($datosNuevos['id'], $datosNuevos['created_at'], $datosNuevos['updated_at']);
                $datosNuevos['cotizacion_id'] = $cotizacionDestinoId;

                $productoNuevo = CotizacionProducto::create($datosNuevos);
                $productosNuevos->push($productoNuevo);
            }

            // Recalcular totales de la cotización destino
            $this->recalcularTotalesCotizacion($cotizacionDestinoId);

            return $productosNuevos;
        });
    }

    /**
     * Reordenar productos de una cotización
     */
    public function reordenarProductos(int $cotizacionId, array $nuevosOrdenes = []): bool
    {
        return DB::transaction(function () use ($cotizacionId, $nuevosOrdenes) {
            if (empty($nuevosOrdenes)) {
                // Reordenar automáticamente
                $productos = CotizacionProducto::where('cotizacion_id', $cotizacionId)
                    ->orderBy('orden', 'asc')
                    ->get();

                $orden = 1;
                foreach ($productos as $producto) {
                    $producto->update(['orden' => $orden]);
                    $orden++;
                }
            } else {
                // Aplicar ordenes específicos
                foreach ($nuevosOrdenes as $productId => $orden) {
                    CotizacionProducto::where('id', $productId)
                        ->where('cotizacion_id', $cotizacionId)
                        ->update(['orden' => $orden]);
                }
            }

            return true;
        });
    }

    /**
     * Buscar productos disponibles para agregar
     */
    public function buscarProductos(string $termino = '', int $limite = 50): Collection
    {
        return Producto::where('active', true)
            ->where(function ($query) use ($termino) {
                if ($termino) {
                    $query->where('nombre', 'like', "%{$termino}%")
                        ->orWhere('codigo', 'like', "%{$termino}%")
                        ->orWhere('descripcion', 'like', "%{$termino}%");
                }
            })
            ->limit($limite)
            ->get();
    }

    /**
     * Obtener siguiente orden para un producto en cotización
     */
    private function obtenerSiguienteOrden(int $cotizacionId): int
    {
        $ultimoOrden = CotizacionProducto::where('cotizacion_id', $cotizacionId)
            ->max('orden');

        return ($ultimoOrden ?? 0) + 1;
    }

    /**
     * Recalcular totales de la cotización
     */
    public function recalcularTotalesCotizacion(int $cotizacionId): void
    {
        $totales = $this->obtenerTotalesCotizacion($cotizacionId);

        if (!isset($totales['error'])) {
            // Actualizar la tabla ord_cotizacion
            DB::table('ord_cotizacion')
                ->where('id', $cotizacionId)
                ->update([
                    'subtotal' => $totales['subtotal'],
                    'descuento' => $totales['descuento'],
                    'total_impuesto' => $totales['impuestos'],
                    'total' => $totales['total'],
                    'updated_at' => now()
                ]);
        }
    }

    /**
     * Obtener totales de cotización incluyendo productos, conceptos adicionales y utilidades
     */
    public function obtenerTotalesCotizacion(int $cotizacionId): array
    {
        try {
            Log::info('🔍 INICIO - Calculando totales completos de cotización', ['cotizacion_id' => $cotizacionId]);

            // Cargar cotización con sus relaciones
            $cotizacion = Cotizacion::with(['utilidades', 'conceptos.concepto'])->findOrFail($cotizacionId);

            // PASO 1: CALCULAR SUBTOTAL BASE DE PRODUCTOS
            $productos = CotizacionProducto::where('cotizacion_id', $cotizacionId)
                ->where('active', true)
                ->get();

            // VALIDACIÓN ESPECIAL: Si no hay productos, todos los totales deben ser cero
            if ($productos->count() === 0) {
                Log::info('🚫 No hay productos - Estableciendo totales en cero', ['cotizacion_id' => $cotizacionId]);
                
                // Actualizar inmediatamente la cotización con totales en cero
                $cotizacion->update([
                    'subtotal' => 0,
                    'descuento' => 0,
                    'total_impuesto' => 0,
                    'total' => 0
                ]);

                return [
                    'subtotal' => 0.00,
                    'descuento' => 0.00,
                    'impuestos' => 0.00,
                    'total' => 0.00,
                    'detalle' => [
                        'productos' => [
                            'cantidad' => 0,
                            'subtotal_bruto' => 0.00,
                            'descuentos_productos' => 0.00
                        ],
                        'utilidades' => [
                            'total_utilidades' => 0.00,
                            'subtotal_con_utilidades' => 0.00,
                            'detalle_utilidades' => []
                        ],
                        'conceptos' => [
                            'descuentos_adicionales' => 0.00,
                            'impuestos' => 0.00,
                            'retenciones' => 0.00,
                            'detalle_conceptos' => []
                        ],
                        'calculo_final' => [
                            'base_gravable' => 0.00,
                            'formula' => 'Sin productos - Totales en cero'
                        ]
                    ]
                ];
            }

            $subtotalProductos = 0;
            $descuentosProductos = 0;

            foreach ($productos as $producto) {
                $subtotalProducto = $producto->cantidad * $producto->valor_unitario;
                $descuentoProducto = $producto->descuento_valor ?? 
                    ($subtotalProducto * ($producto->descuento_porcentaje / 100));

                $subtotalProductos += $subtotalProducto;
                $descuentosProductos += $descuentoProducto;
            }

            Log::info('📦 Subtotal productos', [
                'cantidad_productos' => $productos->count(),
                'subtotal_bruto' => $subtotalProductos,
                'descuentos_productos' => $descuentosProductos
            ]);

            // PASO 2: APLICAR UTILIDADES AL SUBTOTAL (antes de descuentos adicionales)
            $subtotalConUtilidades = $subtotalProductos;
            $totalUtilidades = 0;
            $detalleUtilidades = [];

            if ($cotizacion->utilidades->isNotEmpty()) {
                foreach ($cotizacion->utilidades as $utilidad) {
                    $valorUtilidad = 0;
                    
                    if ($utilidad->tipo === 'porcentaje') {
                        $valorUtilidad = $subtotalProductos * ($utilidad->valor / 100);
                    } else {
                        $valorUtilidad = $utilidad->valor;
                    }

                    $totalUtilidades += $valorUtilidad;

                    $detalleUtilidades[] = [
                        'categoria' => $utilidad->categoria->nombre ?? 'N/A',
                        'item_propio' => $utilidad->itemPropio->nombre ?? 'N/A',
                        'tipo' => $utilidad->tipo,
                        'valor' => $utilidad->valor,
                        'valor_calculado' => $valorUtilidad
                    ];

                    Log::info('💰 Utilidad aplicada', [
                        'tipo' => $utilidad->tipo,
                        'valor_config' => $utilidad->valor,
                        'valor_calculado' => $valorUtilidad,
                        'base_calculo' => $subtotalProductos
                    ]);
                }

                $subtotalConUtilidades = $subtotalProductos + $totalUtilidades;
            }

            // PASO 3: PROCESAR CONCEPTOS (DESCUENTOS, IMPUESTOS, RETENCIONES)
            $totalDescuentosConceptos = 0;
            $totalImpuestos = 0;
            $totalRetenciones = 0;
            $detalleConceptos = [];

            foreach ($cotizacion->conceptos as $cotizacionConcepto) {
                $concepto = $cotizacionConcepto->concepto;
                if (!$concepto) continue;

                $valorConcepto = 0;
                $tipoConcepto = strtoupper(trim($concepto->tipo));

                // Calcular valor del concepto
                if ($cotizacionConcepto->porcentaje && $cotizacionConcepto->porcentaje > 0) {
                    // Para descuentos se aplica sobre subtotal con utilidades
                    // Para impuestos se aplica sobre base gravable (después de descuentos)
                    if (in_array($tipoConcepto, ['DESCUENTO', 'DISCOUNT', 'DES', 'DESC'])) {
                        $valorConcepto = $subtotalConUtilidades * ($cotizacionConcepto->porcentaje / 100);
                    } else {
                        // Para impuestos y retenciones, se calculará después de descuentos
                        $valorConcepto = $cotizacionConcepto->porcentaje;
                    }
                } elseif ($cotizacionConcepto->valor && $cotizacionConcepto->valor > 0) {
                    $valorConcepto = $cotizacionConcepto->valor;
                }

                // Clasificar conceptos
                if (in_array($tipoConcepto, ['DESCUENTO', 'DISCOUNT', 'DES', 'DESC'])) {
                    $totalDescuentosConceptos += $valorConcepto;
                } elseif (in_array($tipoConcepto, ['RETENCION', 'RETENTION', 'RET', 'RETE'])) {
                    $totalRetenciones += $valorConcepto;
                } else {
                    // Asumir que es impuesto (IVA, TAX, etc)
                    $totalImpuestos += $valorConcepto;
                }

                $detalleConceptos[] = [
                    'concepto_id' => $concepto->id,
                    'nombre' => $concepto->nombre,
                    'tipo' => $concepto->tipo,
                    'porcentaje' => $cotizacionConcepto->porcentaje,
                    'valor_fijo' => $cotizacionConcepto->valor,
                    'valor_calculado' => $valorConcepto
                ];
            }

            // PASO 4: CALCULAR BASE GRAVABLE Y APLICAR IMPUESTOS/RETENCIONES
            $baseGravable = $subtotalConUtilidades - $descuentosProductos - $totalDescuentosConceptos;

            // Recalcular impuestos y retenciones sobre la base gravable correcta
            $totalImpuestosCalculados = 0;
            $totalRetencionesCalculadas = 0;

            foreach ($cotizacion->conceptos as $cotizacionConcepto) {
                $concepto = $cotizacionConcepto->concepto;
                if (!$concepto) continue;

                $tipoConcepto = strtoupper(trim($concepto->tipo));
                
                // Solo recalcular si es porcentaje y no es descuento
                if ($cotizacionConcepto->porcentaje && $cotizacionConcepto->porcentaje > 0 && 
                    !in_array($tipoConcepto, ['DESCUENTO', 'DISCOUNT', 'DES', 'DESC'])) {
                    
                    $valorCalculado = $baseGravable * ($cotizacionConcepto->porcentaje / 100);
                    
                    if (in_array($tipoConcepto, ['RETENCION', 'RETENTION', 'RET', 'RETE'])) {
                        $totalRetencionesCalculadas += $valorCalculado;
                    } else {
                        $totalImpuestosCalculados += $valorCalculado;
                    }
                }
            }

            // PASO 5: CALCULAR TOTALES FINALES
            $subtotalFinal = $subtotalConUtilidades;
            $descuentosFinal = $descuentosProductos + $totalDescuentosConceptos;
            $impuestosFinal = $totalImpuestosCalculados;
            $retencionesFinal = $totalRetencionesCalculadas;
            $totalFinal = $baseGravable + $impuestosFinal - $retencionesFinal;

            // PASO 6: ACTUALIZAR CAMPOS EN LA TABLA COTIZACION
            $cotizacion->update([
                'subtotal' => round($subtotalFinal, 2),
                'descuento' => round($descuentosFinal, 2),
                'total_impuesto' => round($impuestosFinal, 2),
                'total' => round($totalFinal, 2)
            ]);

            Log::info('✅ Totales actualizados en BD', [
                'subtotal_bd' => $cotizacion->subtotal,
                'descuento_bd' => $cotizacion->descuento,
                'total_impuesto_bd' => $cotizacion->total_impuesto,
                'total_bd' => $cotizacion->total
            ]);

            // PASO 7: PREPARAR RESPUESTA DETALLADA
            $resultadoFinal = [
                'subtotal' => round($subtotalFinal, 2),
                'descuento' => round($descuentosFinal, 2),
                'impuestos' => round($impuestosFinal, 2),
                'total' => round($totalFinal, 2),
                'detalle' => [
                    'productos' => [
                        'cantidad' => $productos->count(),
                        'subtotal_bruto' => round($subtotalProductos, 2),
                        'descuentos_productos' => round($descuentosProductos, 2)
                    ],
                    'utilidades' => [
                        'total_utilidades' => round($totalUtilidades, 2),
                        'subtotal_con_utilidades' => round($subtotalConUtilidades, 2),
                        'detalle_utilidades' => $detalleUtilidades
                    ],
                    'conceptos' => [
                        'descuentos_adicionales' => round($totalDescuentosConceptos, 2),
                        'impuestos' => round($impuestosFinal, 2),
                        'retenciones' => round($retencionesFinal, 2),
                        'detalle_conceptos' => $detalleConceptos
                    ],
                    'calculo_final' => [
                        'base_gravable' => round($baseGravable, 2),
                        'formula' => 'Subtotal + Utilidades - Descuentos + Impuestos - Retenciones = Total'
                    ]
                ]
            ];

            Log::info('🎯 Totales finales calculados', $resultadoFinal);

            return $resultadoFinal;

        } catch (\Exception $e) {
            Log::error('❌ Error al calcular totales de cotización', [
                'cotizacion_id' => $cotizacionId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'subtotal' => 0,
                'descuento' => 0,
                'impuestos' => 0,
                'total' => 0,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Obtener estadísticas de productos por cotización
     */
    public function obtenerEstadisticas(int $cotizacionId): array
    {
        $productos = CotizacionProducto::where('cotizacion_id', $cotizacionId);

        return [
            'total_productos' => $productos->count(),
            'productos_activos' => $productos->where('active', true)->count(),
            'productos_inactivos' => $productos->where('active', false)->count(),
            'valor_total_productos' => $productos->where('active', true)->sum('valor_total'),
            'cantidad_total_items' => $productos->where('active', true)->sum('cantidad'),
        ];
    }

    /**
     * Calcular valor unitario basado en configuración de costos
     */
    private function calcularValorPorConfiguracion(array $datos): array
    {
        // Si ya tiene valor_unitario específico, no recalcular
        if (isset($datos['valor_unitario']) && $datos['valor_unitario'] > 0) {
            return $datos;
        }

        $valorCalculado = 0;

        // Calcular según tipo de costo
        if (isset($datos['tipo_costo'])) {
            switch ($datos['tipo_costo']) {
                case 'unitario':
                    $valorCalculado = $datos['costo_unitario'] ?? 0;
                    break;

                case 'hora':
                    $costoHora = $datos['costo_hora'] ?? 0;
                    $horasRemuneradas = $datos['horas_remuneradas'] ?? 0;
                    $valorCalculado = $costoHora * $horasRemuneradas;
                    break;

                case 'dia':
                    $costoDia = $datos['costo_dia'] ?? 0;
                    $diasDiurnos = $datos['dias_diurnos'] ?? 0;
                    $diasNocturnos = $datos['dias_nocturnos'] ?? 0;
                    $diasRemuneradosDiurnos = $datos['dias_remunerados_diurnos'] ?? 0;
                    $diasRemuneradosNocturnos = $datos['dias_remunerados_nocturnos'] ?? 0;

                    $totalDias = $diasDiurnos + $diasNocturnos + $diasRemuneradosDiurnos + $diasRemuneradosNocturnos;

                    // Agregar dominicales si están incluidos
                    if (isset($datos['incluir_dominicales']) && $datos['incluir_dominicales']) {
                        $dominicalesDiurnos = $datos['dominicales_diurnos'] ?? 0;
                        $dominicalesNocturnos = $datos['dominicales_nocturnos'] ?? 0;
                        $totalDias += $dominicalesDiurnos + $dominicalesNocturnos;
                    }

                    $valorCalculado = $costoDia * $totalDias;
                    break;
            }
        }

        // Solo asignar si se calculó un valor válido
        if ($valorCalculado > 0) {
            $datos['valor_unitario'] = $valorCalculado;
        }

        return $datos;
    }

    /**
     * Actualizar automaticamente totales de cotización
     * Se ejecuta después de cambios en productos, utilidades o conceptos
     */
    public function actualizarTotalesAutomaticamente(int $cotizacionId): void
    {
        try {
            Log::info('🔄 Actualizando totales automáticamente', ['cotizacion_id' => $cotizacionId]);

            // Llamar al método de cálculo de totales
            $this->obtenerTotalesCotizacion($cotizacionId);

            Log::info('✅ Totales actualizados automáticamente completado', ['cotizacion_id' => $cotizacionId]);
        } catch (\Exception $e) {
            Log::error('❌ Error al actualizar totales automáticamente', [
                'cotizacion_id' => $cotizacionId,
                'error' => $e->getMessage()
            ]);
        }
    }
}
