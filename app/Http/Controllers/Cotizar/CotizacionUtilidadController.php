<?php

namespace App\Http\Controllers\Cotizar;

use App\Http\Controllers\Controller;
use App\Models\Cotizacion;
use App\Models\CotizacionProducto;
use App\Models\CotizacionUtilidad;
use App\Models\Categoria;
use App\Models\ItemPropio;
use App\Services\CotizacionTotalesService;
use Illuminate\Http\Request;

class CotizacionUtilidadController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'cotizacion_id' => 'required|exists:ord_cotizacion,id',
            'categoria_id'  => 'required|exists:categorias,id',
            'item_propio_id'=> 'required|string', // Permite tanto items propios como cargo_X
            'tipo'          => 'required|in:porcentaje,valor',
            'valor'         => 'required|numeric|min:0',
        ]);

        // Verificar que existan productos que coincidan con ambos criterios
        $cotizacion = Cotizacion::findOrFail($data['cotizacion_id']);
        $hayProductos = false;

        if (strpos($data['item_propio_id'], 'cargo_') === 0) {
            // Es un cargo, verificar en parametrización con categoría
            $cargoId = str_replace('cargo_', '', $data['item_propio_id']);
            $hayProductos = $cotizacion->productos()
                ->where('categoria_id', $data['categoria_id'])
                ->whereHas('parametrizacion', function($query) use ($cargoId) {
                    $query->where('cargo_id', $cargoId);
                })
                ->exists();
        } else {
            // Es un item propio normal, verificar ambos campos
            $hayProductos = $cotizacion->productos()
                ->where('categoria_id', $data['categoria_id'])
                ->where('item_propio_id', $data['item_propio_id'])
                ->exists();
        }

        if (!$hayProductos) {
            return response()->json([
                'success' => false,
                'message' => 'No hay productos en la cotización que coincidan con la categoría e item propio seleccionados'
            ], 422);
        }

        // Verificar utilidad duplicada (misma categoría + item propio)
        $existeUtilidad = CotizacionUtilidad::where('cotizacion_id', $data['cotizacion_id'])
            ->where('categoria_id', $data['categoria_id'])
            ->where('item_propio_id', $data['item_propio_id'])
            ->exists();

        if ($existeUtilidad) {
            return response()->json([
                'success' => false,
                'message' => 'Ya existe una utilidad aplicada para esta combinación de categoría e item propio'
            ], 422);
        }

        CotizacionUtilidad::create($data);

        // Recargar cotización con todas las relaciones necesarias
        $cotizacion = Cotizacion::with(['productos.itemPropio', 'productos.producto', 'utilidades.categoria', 'utilidades.itemPropio'])
            ->findOrFail($data['cotizacion_id']);

        \Log::info('Recalculando totales con utilidades', [
            'cotizacion_id' => $cotizacion->id,
            'utilidades_count' => $cotizacion->utilidades->count(),
            'productos_count' => $cotizacion->productos->count()
        ]);

        app(CotizacionTotalesService::class)->recalcular($cotizacion);

        return response()->json([
            'success' => true,
            'message' => 'Utilidad aplicada correctamente',
            'data'    => $cotizacion->fresh(['productos', 'utilidades'])
        ]);
    }

    public function destroy($id)
    {
        $utilidad = CotizacionUtilidad::findOrFail($id);
        $cotizacion = $utilidad->cotizacion;

        $utilidad->delete();

        app(CotizacionTotalesService::class)->recalcular(
            $cotizacion->load(['productos', 'utilidades'])
        );

        return response()->json([
            'success' => true,
            'message' => 'Utilidad eliminada correctamente'
        ]);
    }

    public function obtenerUtilidades($cotizacionId)
    {
        try {
            \Log::info('Obteniendo utilidades para cotización: ' . $cotizacionId);

            $utilidades = CotizacionUtilidad::with(['cotizacion', 'categoria', 'itemPropio'])
                ->where('cotizacion_id', $cotizacionId)
                ->get();

            \Log::info('Utilidades encontradas: ' . $utilidades->count());

            return response()->json([
                'success' => true,
                'data' => $utilidades
            ]);
        } catch (\Exception $e) {
            \Log::error('Error obteniendo utilidades: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las utilidades: ' . $e->getMessage()
            ], 500);
        }
    }

    public function obtenerCategorias($cotizacionId)
    {
        try {
            // Obtener categorías que tienen productos en esta cotización
            $categorias = Categoria::whereHas('itemsPropios', function($query) use ($cotizacionId) {
                $query->whereHas('cotizacionProductos', function($subQuery) use ($cotizacionId) {
                    $subQuery->where('cotizacion_id', $cotizacionId);
                });
            })
            ->orWhereIn('id', function($query) use ($cotizacionId) {
                $query->select('categoria_id')
                      ->from('ord_cotizacion_productos')
                      ->where('cotizacion_id', $cotizacionId)
                      ->whereNotNull('categoria_id');
            })
            ->orderBy('nombre')
            ->get(['id', 'nombre']);

            // Verificar si hay categoría nómina con productos de parametrización
            $categoriaNomina = Categoria::where('nombre', 'LIKE', '%nomina%')
                ->orWhere('nombre', 'LIKE', '%nómina%')
                ->orWhere('nombre', 'LIKE', '%NOMINA%')
                ->first();

            if ($categoriaNomina) {
                // Verificar si hay productos de nómina en la cotización (desde parametrización)
                $hayProductosNomina = \DB::table('ord_cotizacion_productos')
                    ->where('cotizacion_id', $cotizacionId)
                    ->where('categoria_id', $categoriaNomina->id)
                    ->whereNotNull('parametrizacion_id')
                    ->exists();

                if ($hayProductosNomina && !$categorias->contains('id', $categoriaNomina->id)) {
                    $categorias->push($categoriaNomina);
                }
            }

            return response()->json([
                'success' => true,
                'data' => $categorias
            ]);
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las categorías: ' . $th->getMessage()
            ], 500);
        }
    }

    public function obtenerItemsPropios($cotizacionId)
    {
        try {
            // Obtener items propios que tienen productos en esta cotización
            $itemsPropios = ItemPropio::whereIn('id', function($query) use ($cotizacionId) {
                $query->select('item_propio_id')
                      ->from('ord_cotizacion_productos')
                      ->where('cotizacion_id', $cotizacionId)
                      ->whereNotNull('item_propio_id');
            })
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'codigo']);

            // Verificar si hay categoría nómina con productos de parametrización (cargos)
            $categoriaNomina = Categoria::where('nombre', 'LIKE', '%nomina%')
                ->orWhere('nombre', 'LIKE', '%nómina%')
                ->orWhere('nombre', 'LIKE', '%NOMINA%')
                ->first();

            if ($categoriaNomina) {
                // Obtener cargos de parametrización que tienen productos en esta cotización
                $cargosParametrizacion = \DB::table('ord_cotizacion_productos as ocp')
                    ->join('parametrizacion as p', 'ocp.parametrizacion_id', '=', 'p.id')
                    ->join('cargos as c', 'p.cargo_id', '=', 'c.id')
                    ->where('ocp.cotizacion_id', $cotizacionId)
                    ->where('ocp.categoria_id', $categoriaNomina->id)
                    ->whereNotNull('ocp.parametrizacion_id')
                    ->select('c.id', 'c.nombre')
                    ->distinct()
                    ->get();

                // Convertir a formato consistent con items propios y agregar a la colección
                foreach ($cargosParametrizacion as $cargo) {
                    $itemsPropios->push((object)[
                        'id' => 'cargo_' . $cargo->id, // Prefijo para diferenciar de items propios
                        'nombre' => $cargo->nombre . ' (Cargo)',
                        'codigo' => $cargo->codigo,
                        'tipo' => 'cargo' // Campo adicional para identificar el tipo
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'data' => $itemsPropios
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los items propios: ' . $th->getMessage()
            ], 500);
        }
    }

    public function obtenerItemsPorCategoria($cotizacionId, $categoriaId)
    {
        try {
            // Obtener items propios de esta categoría que tienen productos en la cotización
            $itemsPropios = ItemPropio::where('categoria_id', $categoriaId)
                ->whereIn('id', function($query) use ($cotizacionId) {
                    $query->select('item_propio_id')
                          ->from('ord_cotizacion_productos')
                          ->where('cotizacion_id', $cotizacionId)
                          ->whereNotNull('item_propio_id');
                })
                ->orderBy('nombre')
                ->get(['id', 'nombre', 'codigo']);

            // Si es categoría nómina, agregar cargos
            $categoria = Categoria::find($categoriaId);
            if ($categoria && (
                stripos($categoria->nombre, 'nomina') !== false ||
                stripos($categoria->nombre, 'nómina') !== false
            )) {
                // Obtener cargos que tienen productos en esta cotización con esta categoría
                $cargosParametrizacion = \DB::table('ord_cotizacion_productos as ocp')
                    ->join('parametrizacion as p', 'ocp.parametrizacion_id', '=', 'p.id')
                    ->join('cargos as c', 'p.cargo_id', '=', 'c.id')
                    ->where('ocp.cotizacion_id', $cotizacionId)
                    ->where('ocp.categoria_id', $categoriaId)
                    ->whereNotNull('ocp.parametrizacion_id')
                    ->select('c.id', 'c.nombre')
                    ->distinct()
                    ->get();

                // Agregar cargos a la colección
                foreach ($cargosParametrizacion as $cargo) {
                    $itemsPropios->push((object)[
                        'id' => 'cargo_' . $cargo->id,
                        'nombre' => $cargo->nombre . ' (Cargo)',
                        'codigo' => $cargo->codigo ?? '',
                        'tipo' => 'cargo'
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'data' => $itemsPropios
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los items propios por categoría: ' . $th->getMessage()
            ], 500);
        }
    }
}


