<?php

namespace App\Services;

use App\Models\Cotizacion;
use App\Models\CotizacionProducto;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

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

        \Log::info('Recalculando totales', [
            'cotizacion_id' => $cotizacion->id,
            'subtotal_base' => $subtotalBase,
            'utilidades_count' => $cotizacion->utilidades->count()
        ]);

        // Calcular utilidades por categoría e item propio
        $utilidadTotal = $this->calcularUtilidadesPorCategoria($cotizacion, $productos);

        $subtotalConUtilidad = $subtotalBase + $utilidadTotal;

        $total = $subtotalConUtilidad
            - $cotizacion->descuento
            + $cotizacion->total_impuesto;

        \Log::info('Actualizando totales de cotización', [
            'cotizacion_id' => $cotizacion->id,
            'subtotal_base' => $subtotalBase,
            'utilidad_total' => $utilidadTotal,
            'subtotal_con_utilidad' => $subtotalConUtilidad,
            'descuento' => $cotizacion->descuento,
            'total_impuesto' => $cotizacion->total_impuesto,
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

        \Log::info('Iniciando cálculo de utilidades', [
            'cotizacion_id' => $cotizacion->id,
            'utilidades_disponibles' => $cotizacion->utilidades->count()
        ]);

        foreach ($cotizacion->utilidades as $utilidad) {
            $valorUtilidad = 0;

            \Log::info('Procesando utilidad', [
                'utilidad_id' => $utilidad->id,
                'categoria_id' => $utilidad->categoria_id,
                'item_propio_id' => $utilidad->item_propio_id,
                'tipo' => $utilidad->tipo,
                'valor' => $utilidad->valor
            ]);

            // Ahora siempre se requieren ambos: categoria_id e item_propio_id
            if ($utilidad->categoria_id && $utilidad->item_propio_id) {
                
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
                        $parametrizacion = \DB::table('parametrizacion')
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

                $subtotalUtilidad = $productosUtilidad->sum('valor_total');

                \Log::info('Utilidad por categoría e item propio', [
                    'categoria_id' => $utilidad->categoria_id,
                    'item_propio_id' => $utilidad->item_propio_id,
                    'productos_encontrados' => $productosUtilidad->count(),
                    'subtotal_utilidad' => $subtotalUtilidad
                ]);

                $valorUtilidad = $this->calcularValorUtilidad($utilidad, $subtotalUtilidad);

                // Actualizar cada producto con su proporción de utilidad
                $this->aplicarUtilidadAProductos($productosUtilidad, $valorUtilidad, $subtotalUtilidad);
            }

            // Actualizar el valor calculado de la utilidad
            $utilidad->update(['valor_calculado' => $valorUtilidad]);
            $utilidadTotal += $valorUtilidad;

            \Log::info('Utilidad calculada', [
                'categoria_id' => $utilidad->categoria_id,
                'item_propio_id' => $utilidad->item_propio_id,
                'valor_utilidad' => $valorUtilidad,
                'utilidad_total_acumulada' => $utilidadTotal
            ]);
        }

        \Log::info('Total de utilidades calculado', [
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
}

