<?php

namespace App\Services;

use App\Models\Cotizacion;
use App\Models\CotizacionProducto;
use App\Models\CotizacionConcepto;
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
    public function eliminarProducto(int $id): bool
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

            // Recalcular totales de la cotización
            $this->recalcularTotalesCotizacion($cotizacionId);

            // Reordenar productos restantes
            $this->reordenarProductos($cotizacionId);

            Log::info('Producto eliminado exitosamente', [
                'producto_id' => $id,
                'cotizacion_id' => $cotizacionId
            ]);

            return true;
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
            \Log::error('Error al obtener productos de cotización', [
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
            Log::info('🔍 INICIO - Obteniendo totales con utilidades aplicadas', ['cotizacion_id' => $cotizacionId]);

            // 1. VERIFICAR SI HAY UTILIDADES APLICADAS - Si las hay, usar totales de BD
            $cotizacion = Cotizacion::with('utilidades')->findOrFail($cotizacionId);
            
            if ($cotizacion->utilidades->isNotEmpty()) {
                Log::info('📊 Utilizando totales de BD (incluyen utilidades)', [
                    'cotizacion_id' => $cotizacionId,
                    'utilidades_count' => $cotizacion->utilidades->count()
                ]);
                
                return [
                    'subtotal' => round($cotizacion->subtotal ?? 0, 2),
                    'descuento' => round($cotizacion->descuento ?? 0, 2), 
                    'impuestos' => round($cotizacion->total_impuesto ?? 0, 2),
                    'total' => round($cotizacion->total ?? 0, 2),
                    'detalle' => [
                        'utilidades_aplicadas' => $cotizacion->utilidades->map(function($utilidad) {
                            return [
                                'categoria' => $utilidad->categoria->nombre ?? 'N/A',
                                'item_propio' => $utilidad->itemPropio->nombre ?? str_replace('cargo_', 'Cargo ID: ', $utilidad->item_propio_id),
                                'tipo' => $utilidad->tipo,
                                'valor' => $utilidad->valor,
                                'valor_calculado' => $utilidad->valor_calculado
                            ];
                        })
                    ]
                ];
            }

            // 2. SI NO HAY UTILIDADES, CALCULAR DESDE PRODUCTOS
            $productos = CotizacionProducto::where('cotizacion_id', $cotizacionId)
                ->where('active', true)
                ->get();

            Log::info('📦 Productos encontrados (sin utilidades)', [
                'cantidad_productos' => $productos->count()
            ]);

            // 3. CALCULAR TOTALES DE PRODUCTOS
            $subtotalProductos = 0;
            $descuentosProductos = 0;

            foreach ($productos as $producto) {
                $subtotalProducto = $producto->cantidad * $producto->valor_unitario;
                $descuentoProducto = $producto->descuento_valor ??
                    ($subtotalProducto * ($producto->descuento_porcentaje / 100));

                $subtotalProductos += $subtotalProducto;
                $descuentosProductos += $descuentoProducto;
            }

            // Subtotal base (productos menos sus descuentos)
            $subtotalBase = $subtotalProductos - $descuentosProductos;

            Log::info('Totales de productos calculados', [
                'subtotal_productos' => $subtotalProductos,
                'descuentos_productos' => $descuentosProductos,
                'subtotal_base' => $subtotalBase
            ]);

            // Obtener conceptos adicionales (impuestos y descuentos) de la cotización
            $conceptos = CotizacionConcepto::with('concepto')
                ->where('cotizacion_id', $cotizacionId)
                ->get();

            $totalImpuestosAdicionales = 0;
            $totalDescuentosAdicionales = 0;
            $detalleConceptos = [];

            // PRIMERO: Calcular todos los descuentos adicionales
            foreach ($conceptos as $cotizacionConcepto) {
                $concepto = $cotizacionConcepto->concepto;

                if (!$concepto) {
                    Log::warning('Concepto no encontrado', ['concepto_id' => $cotizacionConcepto->concepto_id]);
                    continue;
                }

                $valorConcepto = 0;
                $tipoConcepto = strtoupper($concepto->tipo); // Cambiar a uppercase para comparar

                // Solo calcular descuentos en esta pasada
                if (in_array($tipoConcepto, ['DESCUENTO', 'DISCOUNT', 'DES', 'DESC'])) {
                    if ($cotizacionConcepto->porcentaje && $cotizacionConcepto->porcentaje > 0) {
                        $valorConcepto = $subtotalProductos * ($cotizacionConcepto->porcentaje / 100);
                    } elseif ($cotizacionConcepto->valor && $cotizacionConcepto->valor > 0) {
                        $valorConcepto = $cotizacionConcepto->valor;
                    }

                    $totalDescuentosAdicionales += $valorConcepto;

                    Log::info('Descuento calculado', [
                        'concepto' => $concepto->nombre,
                        'tipo' => $concepto->tipo,
                        'porcentaje' => $cotizacionConcepto->porcentaje,
                        'base_calculo' => $subtotalProductos,
                        'valor_calculado' => $valorConcepto
                    ]);
                }
            }

            // Base para impuestos (productos - descuentos de productos - descuentos adicionales)
            $baseParaImpuestos = $subtotalProductos - $descuentosProductos - $totalDescuentosAdicionales;

            // SEGUNDO: Calcular todos los impuestos sobre la base correcta
            foreach ($conceptos as $cotizacionConcepto) {
                $concepto = $cotizacionConcepto->concepto;
                if (!$concepto) continue;

                $valorConcepto = 0;
                $tipoConcepto = strtoupper($concepto->tipo); // Cambiar a uppercase

                // Calcular valor basado en porcentaje o valor fijo
                if ($cotizacionConcepto->porcentaje && $cotizacionConcepto->porcentaje > 0) {
                    if (in_array($tipoConcepto, ['IMPUESTO', 'IVA', 'TAX', 'IMP'])) {
                        // Impuestos sobre base después de todos los descuentos
                        $valorConcepto = $baseParaImpuestos * ($cotizacionConcepto->porcentaje / 100);

                        Log::info('Impuesto calculado', [
                            'concepto' => $concepto->nombre,
                            'tipo' => $concepto->tipo,
                            'porcentaje' => $cotizacionConcepto->porcentaje,
                            'base_calculo' => $baseParaImpuestos,
                            'valor_calculado' => $valorConcepto
                        ]);
                    } else if (in_array($tipoConcepto, ['DESCUENTO', 'DISCOUNT', 'DES', 'DESC'])) {
                        // Descuentos ya calculados arriba
                        $valorConcepto = $subtotalProductos * ($cotizacionConcepto->porcentaje / 100);
                    }
                } elseif ($cotizacionConcepto->valor && $cotizacionConcepto->valor > 0) {
                    $valorConcepto = $cotizacionConcepto->valor;
                }

                // Clasificar por tipo de concepto
                if (in_array($tipoConcepto, ['IMPUESTO', 'IVA', 'TAX', 'IMP'])) {
                    $totalImpuestosAdicionales += $valorConcepto;
                } elseif (in_array($tipoConcepto, ['DESCUENTO', 'DISCOUNT', 'DES', 'DESC'])) {
                    // Ya sumado arriba
                } else {
                    // Si no está definido claramente, asumimos que es impuesto
                    $totalImpuestosAdicionales += $valorConcepto;
                    Log::info('Concepto sin tipo específico, asumido como impuesto', [
                        'concepto_tipo' => $concepto->tipo,
                        'concepto_nombre' => $concepto->nombre
                    ]);
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

            // Cálculos finales CORREGIDOS
            $subtotal = $subtotalProductos; // Subtotal sin descuentos
            $totalDescuentos = $descuentosProductos + $totalDescuentosAdicionales;
            $baseGravable = $subtotal - $totalDescuentos; // Base después de TODOS los descuentos
            $totalImpuestos = $totalImpuestosAdicionales; // Los impuestos ya se calcularon sobre la base correcta
            $totalFinal = $baseGravable + $totalImpuestos;

            Log::info('✅ CÁLCULOS FINALES CORREGIDOS', [
                '1_subtotal_productos' => $subtotalProductos,
                '2_descuentos_productos' => $descuentosProductos,
                '3_descuentos_adicionales' => $totalDescuentosAdicionales,
                '4_total_descuentos' => $totalDescuentos,
                '5_base_gravable' => $baseGravable,
                '6_impuestos_sobre_base' => $totalImpuestosAdicionales,
                '7_total_final' => $totalFinal,
                'formula_aplicada' => 'Subtotal - Descuentos + Impuestos = Total'
            ]);

            $resultadoFinal = [
                'subtotal' => round($subtotal, 2),
                'descuento' => round($totalDescuentos, 2),
                'impuestos' => round($totalImpuestos, 2),
                'total' => round($totalFinal, 2),
                'detalle' => [
                    'productos' => [
                        'cantidad' => $productos->count(),
                        'subtotal' => round($subtotalProductos, 2),
                        'descuentos' => round($descuentosProductos, 2)
                    ],
                    'conceptos_adicionales' => $detalleConceptos,
                    'resumen_conceptos' => [
                        'impuestos_adicionales' => round($totalImpuestosAdicionales, 2),
                        'descuentos_adicionales' => round($totalDescuentosAdicionales, 2)
                    ],
                    'base_gravable' => round($baseGravable, 2)
                ]
            ];


            Log::info('Totales finales de cotización', $resultadoFinal);

            return $resultadoFinal;

        } catch (\Exception $e) {
            Log::error('Error al calcular totales de cotización', [
                'cotizacion_id' => $cotizacionId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Retornar totales en cero en caso de error
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
}
