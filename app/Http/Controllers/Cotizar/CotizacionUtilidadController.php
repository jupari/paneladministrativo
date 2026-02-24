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
use Illuminate\Support\Facades\Log;

class CotizacionUtilidadController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'cotizacion_id' => 'required|exists:ord_cotizacion,id',
            'categoria_id'  => 'required|exists:categorias,id',
            'item_propio_id'=> 'sometimes|string', // Para compatibilidad con versión anterior (un solo item)
            'item_propio_ids'=> 'sometimes|array', // Para múltiples items
            'item_propio_ids.*'=> 'string', // Validar cada item del array
            'cargo_ids' => 'sometimes|array', // Para cargos en nómina
            'cargo_ids.*' => 'string', // Validar cada cargo del array
            'tipo'          => 'required|in:porcentaje,valor',
            'valor'         => 'required|numeric|min:0',
        ]);

        // Determinar si son múltiples items o uno solo (compatibilidad)
        $itemsPropiosIds = [];
        $cargoIds = [];
        if (isset($data['item_propio_ids']) && is_array($data['item_propio_ids'])) {
            $itemsPropiosIds = $data['item_propio_ids'];
        } elseif (isset($data['item_propio_id'])) {
            $itemsPropiosIds = [$data['item_propio_id']];
        } else {
            if (isset($data['cargo_ids']) && is_array($data['cargo_ids'])) {
                $cargoIds = array_map(function($cargoId) {
                    return $cargoId; // Prefijo para diferenciar cargos
                }, $data['cargo_ids']);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Debe seleccionar al menos un item propio o cargo'
                ], 422);
            }
        }

        $cotizacion = Cotizacion::findOrFail($data['cotizacion_id']);
        $utilidadesCreadas = [];
        $itemsSinProductos = [];
        $itemsDuplicados = [];

        if (!empty($itemsPropiosIds)) {
            foreach ($itemsPropiosIds as $itemPropioId) {
                // Verificar que existan productos que coincidan con ambos criterios
                $hayProductos = false;

                if (strpos($itemPropioId, 'cargo_') === 0) {
                    // Es un cargo, verificar en parametrización con categoría
                    $cargoId = str_replace('cargo_', '', $itemPropioId);
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
                        ->where('item_propio_id', $itemPropioId)
                        ->exists();
                }

                if (!$hayProductos) {
                    $itemsSinProductos[] = $itemPropioId;
                    continue;
                }

                // Verificar utilidad duplicada (misma categoría + item propio)
                $existeUtilidad = CotizacionUtilidad::where('cotizacion_id', $data['cotizacion_id'])
                    ->where('categoria_id', $data['categoria_id'])
                    ->where('item_propio_id', $itemPropioId)
                    ->exists();

                if ($existeUtilidad) {
                    $itemsDuplicados[] = $itemPropioId;
                    continue;
                }

                // Crear la utilidad
                $utilidad = CotizacionUtilidad::create([
                    'cotizacion_id' => $data['cotizacion_id'],
                    'categoria_id'  => $data['categoria_id'],
                    'item_propio_id'=> $itemPropioId,
                    'tipo'          => $data['tipo'],
                    'valor'         => $data['valor'],
                ]);

                $utilidadesCreadas[] = $utilidad;
            }
        }else if(!empty($cargoIds)){
            foreach ($cargoIds as $cargoId) {
                // Primero verificar productos en la cotización
                $productosEnCotizacion = $cotizacion->productos()
                    ->where('categoria_id', $data['categoria_id'])
                    ->get();

                // Verificar que existan productos que coincidan con ambos criterios
                $queryProductos = $cotizacion->productos()
                    ->where('categoria_id', $data['categoria_id'])
                    ->whereHas('parametrizacion', function($query) use ($cargoId) {
                        $query->where('cargo_id', $cargoId);
                    });

                $hayProductos = $queryProductos->exists();
                if (!$hayProductos) {
                    $itemsSinProductos[] = $cargoId;
                    continue;
                }

                // Verificar utilidad duplicada (misma categoría + cargo)
                $existeUtilidad = CotizacionUtilidad::where('cotizacion_id', $data['cotizacion_id'])
                    ->where('categoria_id', $data['categoria_id'])
                    ->where('cargo_id', $cargoId)
                    ->exists();

                if ($existeUtilidad) {
                    $itemsDuplicados[] = $cargoId;
                    continue;
                }

                // Crear la utilidad
                $utilidad = CotizacionUtilidad::create([
                    'cotizacion_id' => $data['cotizacion_id'],
                    'categoria_id'  => $data['categoria_id'],
                    'cargo_id'      => $cargoId,
                    'tipo'          => $data['tipo'],
                    'valor'         => $data['valor'],
                ]);

                $utilidadesCreadas[] = $utilidad;
            }
        }

        // Preparar mensaje de respuesta
        $mensaje = '';
        $success = count($utilidadesCreadas) > 0;

        if (count($utilidadesCreadas) > 0) {
            $mensaje .= "Se aplicaron " . count($utilidadesCreadas) . " utilidad(es) correctamente.";
        }

        if (count($itemsSinProductos) > 0) {
            $mensaje .= " " . count($itemsSinProductos) . " item(s) no tienen productos en la cotización.";
        }

        if (count($itemsDuplicados) > 0) {
            $mensaje .= " " . count($itemsDuplicados) . " item(s) ya tenían utilidades aplicadas.";
        }

        if (!$success) {
            return response()->json([
                'success' => false,
                'message' => 'No se pudo aplicar ninguna utilidad: ' . $mensaje
            ], 422);
        }

        // Recargar cotización con todas las relaciones necesarias
        $cotizacion = Cotizacion::with(['productos.itemPropio', 'productos.producto', 'utilidades.categoria', 'utilidades.itemPropio'])
            ->findOrFail($data['cotizacion_id']);

        Log::info('Recalculando totales con utilidades múltiples', [
            'cotizacion_id' => $cotizacion->id,
            'utilidades_creadas' => count($utilidadesCreadas),
            'utilidades_total' => $cotizacion->utilidades->count(),
            'productos_count' => $cotizacion->productos->count()
        ]);

        app(CotizacionTotalesService::class)->recalcular($cotizacion);

        return response()->json([
            'success' => true,
            'message' => trim($mensaje),
            'data'    => $cotizacion->fresh(['productos', 'utilidades']),
            'utilidades_creadas' => count($utilidadesCreadas)
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
                        'id' => $cargo->id, // Prefijo para diferenciar de items propios
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
                        'id' => $cargo->id,
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


