<?php

namespace App\Services;

use App\Models\CotizacionItem;
use App\Models\CotizacionSubImtes;
use App\Models\Cotizacion;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Exception;

class CotizacionItemService
{
    /**
     * Obtener items de una cotización con sus detalles
     */
    public function obtenerItemsPorCotizacion(int $cotizacionId): Collection
    {
        return CotizacionItem::with(['cotizacion','subitems.unidadMedida'])
            ->where('cotizacion_id', $cotizacionId)
            ->where('active', 1)
            ->get();

    }

    /**
     * Obtener detalle de un subitem
     */
    private function obtenerSubitemDetalle(int $subitemId): ?array
    {
        $subitem = CotizacionSubImtes::with('unidadMedida')->find($subitemId);

        if (!$subitem) {
            return null;
        }

        return [
            'id' => $subitem->id,
            'codigo' => $subitem->codigo,
            'nombre' => $subitem->nombre,
            'unidad_medida' => $subitem->unidadMedida ? [
                'id' => $subitem->unidadMedida->id,
                'nombre' => $subitem->unidadMedida->nombre,
                'sigla' => $subitem->unidadMedida->simbolo
            ] : null,
            'cantidad_base' => $subitem->cantidad,
            'observacion' => $subitem->observacion
        ];
    }

    /**
     * Crear un item individual (usado desde frontend para crear items uno por uno)
     */
    public function crearItem(int $cotizacionId, array $itemData): array
    {
        DB::beginTransaction();

        try {
            // Verificar que la cotización existe
            $cotizacion = Cotizacion::findOrFail($cotizacionId);

            // Validar datos mínimos requeridos
            if (!isset($itemData['nombre']) || empty(trim($itemData['nombre']))) {
                throw new Exception('El nombre del item es requerido');
            }

            if (strlen(trim($itemData['nombre'])) > 255) {
                throw new Exception('El nombre del item no puede exceder 255 caracteres');
            }

            // Crear el nuevo item
            $item = new CotizacionItem();
            $item->cotizacion_id = $cotizacionId;
            $item->nombre = trim($itemData['nombre']);
            $item->active = $itemData['active'] ?? true;
            $item->save();

            DB::commit();

            return [
                'success' => true,
                'message' => 'Item creado exitosamente',
                'data' => $item->toArray()
            ];

        } catch (Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Error al crear item: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Guardar items de cotización (crear nuevos y actualizar existentes)
     */
    public function guardarItems(int $cotizacionId, array $items): array
    {
        DB::beginTransaction();

        try {
            // Verificar que la cotización existe
            $cotizacion = Cotizacion::findOrFail($cotizacionId);

            // Obtener items existentes para esta cotización
            $itemsExistentes = CotizacionItem::where('cotizacion_id', $cotizacionId)->get();
            $idsItemsEnviados = collect($items)->pluck('id')->filter()->toArray();

            $itemsCreados = [];
            $itemsActualizados = [];
            $itemsDesactivados = [];
            $errores = [];

            // Procesar cada item enviado
            foreach ($items as $index => $itemData) {
                try {
                    // Validar datos mínimos requeridos
                    if (!isset($itemData['nombre']) || empty(trim($itemData['nombre']))) {
                        $errores[] = "Item en posición $index: el nombre es requerido";
                        continue;
                    }

                    if (isset($itemData['id']) && $itemData['id']) {
                        // Item existente - actualizar
                        $item = CotizacionItem::find($itemData['id']);

                        if ($item && $item->cotizacion_id == $cotizacionId) {
                            $nombreAnterior = $item->nombre;
                            $activoAnterior = $item->active;

                            $item->nombre = trim($itemData['nombre']);
                            $item->active = $itemData['active'] ?? true;

                            // Solo guardar si hay cambios
                            if ($item->isDirty()) {
                                $item->save();
                                $itemsActualizados[] = [
                                    'item' => $item,
                                    'cambios' => [
                                        'nombre' => ['anterior' => $nombreAnterior, 'nuevo' => $item->nombre],
                                        'active' => ['anterior' => $activoAnterior, 'nuevo' => $item->active]
                                    ]
                                ];
                            }
                        } else {
                            $errores[] = "Item con ID {$itemData['id']} no encontrado o no pertenece a esta cotización";
                        }
                    } else {
                        // Item nuevo - crear
                        $item = new CotizacionItem();
                        $item->cotizacion_id = $cotizacionId;
                        $item->nombre = trim($itemData['nombre']);
                        $item->active = $itemData['active'] ?? true;
                        $item->save();

                        $itemsCreados[] = $item;
                    }
                } catch (Exception $e) {
                    $errores[] = "Error procesando item en posición $index: " . $e->getMessage();
                }
            }

            // Manejar items que ya no están en la lista enviada
            $itemsParaEliminar = $itemsExistentes->filter(function($item) use ($idsItemsEnviados) {
                return !in_array($item->id, $idsItemsEnviados);
            });

            foreach ($itemsParaEliminar as $item) {
                try {
                    // Verificar si el item tiene subitems
                    $tieneSubitems = CotizacionSubImtes::where('cotizacion_item_id', $item->id)->exists();

                    if (!$tieneSubitems) {
                        // Solo eliminar si no tiene subitems
                        $item->delete();
                    } else {
                        // Si tiene subitems, marcarlo como inactivo en lugar de eliminarlo
                        $nombreAnterior = $item->nombre;
                        $item->active = false;
                        $item->save();

                        $itemsDesactivados[] = [
                            'item' => $item,
                            'razon' => 'Tiene subitems asociados'
                        ];
                    }
                } catch (Exception $e) {
                    $errores[] = "Error eliminando item ID {$item->id}: " . $e->getMessage();
                }
            }

            DB::commit();

            $totalProcesados = count($itemsCreados) + count($itemsActualizados);
            $mensaje = "Items procesados exitosamente";

            if (!empty($errores)) {
                $mensaje .= " con algunos errores";
            }

            return [
                'success' => true,
                'message' => $mensaje,
                'data' => [
                    'items_creados' => $itemsCreados,
                    'items_actualizados' => collect($itemsActualizados)->pluck('item')->toArray(),
                    'items_desactivados' => collect($itemsDesactivados)->pluck('item')->toArray(),
                ],
                'estadisticas' => [
                    'creados' => count($itemsCreados),
                    'actualizados' => count($itemsActualizados),
                    'desactivados' => count($itemsDesactivados),
                    'total_procesados' => $totalProcesados,
                    'errores' => count($errores)
                ],
                'errores' => $errores,
                'detalle_cambios' => [
                    'actualizaciones' => $itemsActualizados,
                    'desactivaciones' => $itemsDesactivados
                ]
            ];

        } catch (Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Error al procesar items: ' . $e->getMessage(),
                'errores' => [$e->getMessage()]
            ];
        }
    }

    /**
     * Actualizar items de cotización
     */
    public function actualizarItems(int $cotizacionId, array $items): array
    {
        return $this->guardarItems($cotizacionId, $items);
    }

    /**
     * Eliminar un item específico
     */
    public function eliminarItem(int $itemId): array
    {
        DB::beginTransaction();

        try {
            $item = CotizacionItem::findOrFail($itemId);
            $cotizacionId = $item->cotizacion_id;

            // Verificar si el item tiene subitems
            $tieneSubitems = CotizacionSubImtes::where('cotizacion_item_id', $itemId)->exists();

            if ($tieneSubitems) {
                // Si tiene subitems, no eliminar - marcar como inactivo
                $item->active = false;
                $item->save();

                $mensaje = 'Item desactivado porque tiene subitems asociados';
                $accion = 'desactivado';
            } else {
                // Si no tiene subitems, eliminar completamente
                $item->delete();

                $mensaje = 'Item eliminado exitosamente';
                $accion = 'eliminado';
            }

            DB::commit();

            return [
                'success' => true,
                'message' => $mensaje,
                'accion' => $accion,
                'item_id' => $itemId,
                'tenia_subitems' => $tieneSubitems
            ];

        } catch (Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Error al eliminar item: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ];
        }
    }







    public function duplicarItems(int $cotizacionOrigenId, int $cotizacionDestinoId): array
    {
        DB::beginTransaction();

        try {
            $itemsOrigen = CotizacionItem::where('cotizacion_id', $cotizacionOrigenId)
                ->where('active', 1)
                ->orderBy('orden')
                ->get();

            $itemsCreados = [];

            foreach ($itemsOrigen as $item) {
                $nuevoItem = new CotizacionItem();
                $nuevoItem->cotizacion_id = $cotizacionDestinoId;
                $nuevoItem->nombre = $item->nombre;
                $nuevoItem->active = $item->active;
                $nuevoItem->save();

                $itemsCreados[] = $nuevoItem;
            }

            DB::commit();

            return [
                'message' => 'Items duplicados exitosamente',
                'data' => $itemsCreados,
                'total_items' => count($itemsCreados)
            ];

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }


}
