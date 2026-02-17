<?php

namespace App\Services;

use App\Models\Cotizacion;
use App\Models\CotizacionProducto;
use App\Models\CotizacionConcepto;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CotizacionTotalesService
{
    public function recalcular(Cotizacion $cotizacion): Cotizacion
    {
        // Asegurar que las utilidades estén cargadas
        if (!$cotizacion->relationLoaded('utilidades')) {
            $cotizacion->load('utilidades');
        }

        // Obtener productos con sus relaciones necesarias
        $productos = $cotizacion->productos()
            ->with(['itemPropio', 'producto'])
            ->get();

        $subtotalBase = $productos->sum('valor_total');

        Log::info('Recalculando totales', [
            'cotizacion_id' => $cotizacion->id,
            'subtotal_base' => $subtotalBase,
            'utilidades_count' => $cotizacion->utilidades->count()
        ]);

        // Calcular utilidades por categoría e item propio
        $utilidadTotal = $this->calcularUtilidadesPorCategoria($cotizacion, $productos);

        $subtotalConUtilidad = $subtotalBase + $utilidadTotal;

        // RECALCULAR CONCEPTOS SOBRE EL SUBTOTAL QUE INCLUYE UTILIDADES
        $conceptosRecalculados = $this->recalcularConceptos($cotizacion->id, $subtotalConUtilidad);

        $totalDescuentosRecalculados = $conceptosRecalculados['descuentos'];
        $totalImpuestosRecalculados = $conceptosRecalculados['impuestos'];

        // Actualizar la cotización con los nuevos valores de conceptos
        $cotizacion->update([
            'descuento' => $totalDescuentosRecalculados,
            'total_impuesto' => $totalImpuestosRecalculados,
        ]);

        $total = $subtotalConUtilidad
            - $totalDescuentosRecalculados
            + $totalImpuestosRecalculados;

        Log::info('Actualizando totales de cotización con conceptos recalculados', [
            'cotizacion_id' => $cotizacion->id,
            'subtotal_base' => $subtotalBase,
            'utilidad_total' => $utilidadTotal,
            'subtotal_con_utilidad' => $subtotalConUtilidad,
            'descuentos_recalculados' => $totalDescuentosRecalculados,
            'impuestos_recalculados' => $totalImpuestosRecalculados,
            'total_final' => $total
        ]);

        $cotizacion->update([
            'subtotal' => $subtotalConUtilidad,
            'total'    => $total,
        ]);

        return $cotizacion->fresh();
    }

    /**
     * Calcula las utilidades aplicadas por categoría e item propio
     */
    private function calcularUtilidadesPorCategoria(Cotizacion $cotizacion, Collection $productos): float
    {
        $utilidadTotal = 0;

        Log::info('Iniciando cálculo de utilidades', [
            'cotizacion_id' => $cotizacion->id,
            'utilidades_disponibles' => $cotizacion->utilidades->count()
        ]);

        foreach ($cotizacion->utilidades as $utilidad) {
            $valorUtilidad = 0;

            Log::info('Procesando utilidad', [
                'utilidad_id' => $utilidad->id,
                'categoria_id' => $utilidad->categoria_id,
                'item_propio_id' => $utilidad->item_propio_id,
                'cargo_id' => $utilidad->cargo_id,
                'tipo' => $utilidad->tipo,
                'valor' => $utilidad->valor
            ]);

            // Manejar utilidades por categoría + cargo_id
            if ($utilidad->categoria_id && $utilidad->cargo_id) {
                Log::info('Procesando utilidad con cargo_id', [
                    'categoria_id' => $utilidad->categoria_id,
                    'cargo_id' => $utilidad->cargo_id
                ]);

                // Filtrar productos por categoría Y cargo (parametrización)
                $productosUtilidad = $productos->filter(function($producto) use ($utilidad) {
                    // Verificar que coincida la categoría
                    if ($producto->categoria_id != $utilidad->categoria_id) {
                        return false;
                    }

                    // Verificar parametrización si existe
                    if (!$producto->parametrizacion_id) {
                        return false;
                    }

                    // Obtener cargo de la parametrización
                    $parametrizacion = DB::table('parametrizacion')
                        ->where('id', $producto->parametrizacion_id)
                        ->first();

                    $coincide = $parametrizacion && $parametrizacion->cargo_id == $utilidad->cargo_id;

                    Log::info('Verificando producto', [
                        'producto_id' => $producto->id,
                        'parametrizacion_id' => $producto->parametrizacion_id,
                        'cargo_en_parametrizacion' => $parametrizacion ? $parametrizacion->cargo_id : null,
                        'cargo_utilidad' => $utilidad->cargo_id,
                        'coincide' => $coincide
                    ]);

                    return $coincide;
                });

            }
            // Manejar utilidades por categoría + item_propio_id
            else if ($utilidad->categoria_id && $utilidad->item_propio_id) {

                // Verificar si es un cargo (con prefijo cargo_)
                if (strpos($utilidad->item_propio_id, 'cargo_') === 0) {
                    // Extraer el ID del cargo
                    $cargoId = str_replace('cargo_', '', $utilidad->item_propio_id);

                    // Filtrar productos por categoría Y cargo (parametrización)
                    $productosUtilidad = $productos->filter(function($producto) use ($utilidad, $cargoId) {
                        // Verificar que coincida la categoría
                        if ($producto->categoria_id != $utilidad->categoria_id) {
                            return false;
                        }

                        // Verificar parametrización si existe
                        if (!$producto->parametrizacion_id) {
                            return false;
                        }

                        // Obtener cargo de la parametrización
                        $parametrizacion = DB::table('parametrizacion')
                            ->where('id', $producto->parametrizacion_id)
                            ->first();

                        return $parametrizacion && $parametrizacion->cargo_id == $cargoId;
                    });

                } else {
                    // Filtrar productos por categoría Y item propio
                    $productosUtilidad = $productos->filter(function($producto) use ($utilidad) {
                        return $producto->categoria_id == $utilidad->categoria_id
                            && $producto->item_propio_id == $utilidad->item_propio_id;
                    });
                }
            } else {
                // Caso no válido
                Log::warning('Utilidad sin criterios válidos', [
                    'utilidad_id' => $utilidad->id,
                    'categoria_id' => $utilidad->categoria_id,
                    'item_propio_id' => $utilidad->item_propio_id,
                    'cargo_id' => $utilidad->cargo_id
                ]);
                continue;
            }

            $subtotalUtilidad = $productosUtilidad->sum('valor_total');

            Log::info('Utilidad por categoría e item/cargo', [
                'categoria_id' => $utilidad->categoria_id,
                'item_propio_id' => $utilidad->item_propio_id,
                'cargo_id' => $utilidad->cargo_id,
                'productos_encontrados' => $productosUtilidad->count(),
                'subtotal_utilidad' => $subtotalUtilidad
            ]);

            $valorUtilidad = $this->calcularValorUtilidad($utilidad, $subtotalUtilidad);

            // Actualizar cada producto con su proporción de utilidad
            $this->aplicarUtilidadAProductos($productosUtilidad, $valorUtilidad, $subtotalUtilidad);

            // Actualizar el valor calculado de la utilidad
            $utilidad->update(['valor_calculado' => $valorUtilidad]);
            $utilidadTotal += $valorUtilidad;

            Log::info('Utilidad calculada', [
                'categoria_id' => $utilidad->categoria_id,
                'item_propio_id' => $utilidad->item_propio_id,
                'valor_utilidad' => $valorUtilidad,
                'utilidad_total_acumulada' => $utilidadTotal
            ]);
        }

        Log::info('Total de utilidades calculado', [
            'cotizacion_id' => $cotizacion->id,
            'utilidad_total_final' => $utilidadTotal
        ]);

        return $utilidadTotal;
    }

    /**
     * Calcula el valor de utilidad según el tipo (porcentaje o valor fijo)
     */
    private function calcularValorUtilidad($utilidad, float $subtotal): float
    {
        if ($utilidad->tipo === 'porcentaje') {
            return ($subtotal * $utilidad->valor) / 100;
        }

        return $utilidad->valor;
    }

    /**
     * Aplica la utilidad proporcionalmente a los productos
     */
    private function aplicarUtilidadAProductos(Collection $productos, float $valorUtilidad, float $subtotalBase): void
    {
        if ($subtotalBase <= 0) {
            return;
        }

        foreach ($productos as $producto) {
            $proporcion = $subtotalBase > 0 ? $producto->valor_total / $subtotalBase : 0;
            $utilidadProducto = $valorUtilidad * $proporcion;

            // Aquí puedes agregar cualquier lógica adicional si necesitas guardar
            // la utilidad individual por producto en la base de datos
        }
    }

    /**
     * Recalcula conceptos adicionales (descuentos e impuestos) sobre la nueva base con utilidades
     */
    private function recalcularConceptos(int $cotizacionId, float $subtotalConUtilidad): array
    {
        $conceptos = CotizacionConcepto::with('concepto')
            ->where('cotizacion_id', $cotizacionId)
            ->get();

        $totalDescuentos = 0;
        $totalImpuestos = 0;

        Log::info('Recalculando conceptos con nueva base', [
            'subtotal_con_utilidad' => $subtotalConUtilidad,
            'conceptos_count' => $conceptos->count()
        ]);

        // PRIMERA PASADA: Calcular descuentos sobre subtotal con utilidades
        foreach ($conceptos as $cotizacionConcepto) {
            $concepto = $cotizacionConcepto->concepto;
            if (!$concepto) continue;

            $tipoConcepto = strtoupper($concepto->tipo);

            if (in_array($tipoConcepto, ['DESCUENTO', 'DISCOUNT', 'DES', 'DESC'])) {
                $valorConcepto = 0;

                if ($cotizacionConcepto->porcentaje && $cotizacionConcepto->porcentaje > 0) {
                    // APLICAR DESCUENTO SOBRE SUBTOTAL QUE INCLUYE UTILIDADES
                    $valorConcepto = $subtotalConUtilidad * ($cotizacionConcepto->porcentaje / 100);
                } elseif ($cotizacionConcepto->valor && $cotizacionConcepto->valor > 0) {
                    $valorConcepto = $cotizacionConcepto->valor;
                }

                $totalDescuentos += $valorConcepto;

                Log::info('Descuento recalculado sobre base con utilidades', [
                    'concepto' => $concepto->nombre,
                    'porcentaje' => $cotizacionConcepto->porcentaje,
                    'nueva_base' => $subtotalConUtilidad,
                    'valor_recalculado' => $valorConcepto
                ]);
            }
        }

        // Base para impuestos (subtotal con utilidades - descuentos)
        $baseParaImpuestos = $subtotalConUtilidad - $totalDescuentos;

        // SEGUNDA PASADA: Calcular impuestos sobre base después de descuentos
        foreach ($conceptos as $cotizacionConcepto) {
            $concepto = $cotizacionConcepto->concepto;
            if (!$concepto) continue;

            $tipoConcepto = strtoupper($concepto->tipo);

            if (in_array($tipoConcepto, ['IMPUESTO', 'IVA', 'TAX', 'IMP'])) {
                $valorConcepto = 0;

                if ($cotizacionConcepto->porcentaje && $cotizacionConcepto->porcentaje > 0) {
                    $valorConcepto = $baseParaImpuestos * ($cotizacionConcepto->porcentaje / 100);
                } elseif ($cotizacionConcepto->valor && $cotizacionConcepto->valor > 0) {
                    $valorConcepto = $cotizacionConcepto->valor;
                }

                $totalImpuestos += $valorConcepto;

                Log::info('Impuesto recalculado sobre nueva base', [
                    'concepto' => $concepto->nombre,
                    'porcentaje' => $cotizacionConcepto->porcentaje,
                    'base_con_descuentos' => $baseParaImpuestos,
                    'valor_recalculado' => $valorConcepto
                ]);
            }
        }

        Log::info('Conceptos recalculados finales', [
            'total_descuentos' => $totalDescuentos,
            'total_impuestos' => $totalImpuestos
        ]);

        return [
            'descuentos' => $totalDescuentos,
            'impuestos' => $totalImpuestos
        ];
    }
}

