<?php

namespace App\Services;

use App\Models\Autorizar;
use App\Models\Cotizacion;
use App\Models\CotizacionCondicionComercial;
use App\Models\ObservacionCotizacion;
use App\Models\CotizacionConcepto;
use App\Models\CotizacionItem;
use App\Models\CotizacionProducto;
use App\Models\CotizacionSubImtes;
use App\Models\CotizacionLista;
use App\Models\CotizacionUtilidad;
use App\Models\CotizacionViatico;
use App\Models\Tercero;
use App\Models\TerceroSucursal;
use App\Models\TerceroContacto;
use App\Models\EstadoCotizacion;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class CotizacionService
{
    /**
     * Obtener todas las cotizaciones con sus relaciones
     */
    public function obtenerCotizaciones(): Collection
    {
        return Cotizacion::with(['tercero', 'terceroSucursal', 'terceroContacto', 'estado', 'autorizacion'])
                        ->orderBy('id', 'desc')
                        ->get();
    }

    public function obtenerCotizacionesBorradorTerminado(): Collection
    {
        return Cotizacion::with(['tercero', 'terceroSucursal', 'terceroContacto', 'estado', 'autorizacion'])
                        ->whereHas('estado', function($query) {
                            $query->where('estado', 'like', '%Borrador%')
                                  ->orWhere('estado', 'like', '%Terminado%');
                        })
                        ->orderBy('created_at', 'desc')
                        ->get();
    }

    /**
     * Obtener cotización por ID con relaciones
     */
    public function obtenerCotizacionPorId(int $id): ?Cotizacion
    {
        return Cotizacion::with(['tercero', 'terceroSucursal', 'terceroContacto', 'estado', 'autorizacion','items'])
                        ->find($id);
    }

    /**
     * Crear nueva cotización
     */
    public function crearCotizacion(array $data): Cotizacion
    {
        DB::beginTransaction();

        try {
            // Si no se proporciona número de documento, generarlo automáticamente
            if (empty($data['num_documento'])) {
                $data['num_documento'] = $this->generarNumeroDocumento();
            }

            // Si no se proporciona versión, establecer como 1
            if (empty($data['version'])) {
                $data['version'] = 1;
            }

            // Calcular totales si no están definidos
            $data = $this->calcularTotales($data);

            $cotizacion = Cotizacion::create($data);

            DB::commit();

            return $this->obtenerCotizacionPorId($cotizacion->id);

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Actualizar cotización existente
     */
    public function actualizarCotizacion(int $id, array $data): Cotizacion
    {
        DB::beginTransaction();

        try {
            $cotizacion = Cotizacion::findOrFail($id);

            // Calcular totales si es necesario
            $data = $this->calcularTotales($data);

            $cotizacion->update($data);

            DB::commit();

            return $this->obtenerCotizacionPorId($cotizacion->id);

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Eliminar cotización
     */
    public function eliminarCotizacion(int $id): bool
    {
        DB::beginTransaction();

        try {
            $cotizacion = Cotizacion::findOrFail($id);
            $cotizacion->estado_id = EstadoCotizacion::where('estado', 'Anulado')->value('id');
            $cotizacion->save();

            DB::commit();

            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Obtener clientes activos
     */
    public function obtenerClientes(): Collection
    {
        return Tercero::where('tercerotipo_id', 2) // Tipo cliente
                     ->where('active', 1)
                     ->orderBy('nombres')
                     ->orderBy('nombre_establecimiento')
                     ->get();
    }

    /**
     * Obtener sucursales de un tercero
     */
    public function obtenerSucursalesPorTercero(int $terceroId): Collection
    {
        return TerceroSucursal::where('tercero_id', $terceroId)
                             ->with('ciudades')
                             ->get();
    }

    /**
     * Obtener contactos de un tercero
     */
    public function obtenerContactosPorTercero(int $terceroId): Collection
    {
        return TerceroContacto::where('tercero_id', $terceroId)->get();
    }

    /**
     * Obtener estados disponibles
     */
    public function obtenerEstados(): Collection
    {
        return EstadoCotizacion::where('active', 1)->orderBy('estado')->get();
    }

    public function obtenerAutorizaciones(): Collection
    {
        return Autorizar::where('active', 1)->orderBy('nombre')->get();
    }

    public function autorizarCotizacion(int $cotizacionId): bool
    {
        $autorizarId=null;
        $cotizacion = Cotizacion::findOrFail($cotizacionId);
        if(!$cotizacion) {
            throw new \Exception('Cotización no encontrada.');
        }

        if($cotizacion->autorizacion_id==1) {
            $autorizarId=2; // Aprobada
        }else{
            $autorizarId=1; // Rechazada
        }
        $cotizacion->autorizacion_id = $autorizarId;
        return $cotizacion->save();
    }
    /**
     * Generar siguiente número de documento
     */
    public function generarNumeroDocumento(): string
    {
        $lastCotizacion = Cotizacion::orderBy('id', 'desc')->first();
        $nextId = $lastCotizacion ? ($lastCotizacion->id + 1) : 1;

        return 'COT-' . str_pad($nextId, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Duplicar cotización
     */
    public function duplicarCotizacion(int $id): Cotizacion
    {
        DB::beginTransaction();

        try {
            $cotizacionOriginal = Cotizacion::findOrFail($id);

            $datos = $cotizacionOriginal->toArray();

            // Remover ID, timestamps y campos del flujo de aprobación.
            // token_aprobacion tiene restricción unique — debe ser nulo en la nueva versión
            // hasta que se envíe al cliente.
            unset(
                $datos['id'],
                $datos['created_at'],
                $datos['updated_at'],
                $datos['token_aprobacion'],
                $datos['token_expira_en'],
                $datos['fecha_envio'],
                $datos['fecha_respuesta'],
                $datos['motivo_rechazo'],
                $datos['respondido_por']
            );

            // Mantener el mismo número de documento
            // Calcular la nueva versión: buscar la máxima versión para ese num_documento y sumarle 1
            $numDocumentoOriginal = $cotizacionOriginal->num_documento;
            $maxVersion = Cotizacion::where('num_documento', $numDocumentoOriginal)->max('version');
            $datos['num_documento'] = $numDocumentoOriginal;
            $datos['doc_origen'] = $numDocumentoOriginal;
            $datos['version'] = ($maxVersion ? $maxVersion : 1) + 1;

            // Crear la nueva cotización
            $nuevaCotizacion = Cotizacion::create($datos);

            // Duplicar conceptos (impuestos/descuentos) relacionados
            $conceptosOriginales = CotizacionConcepto::where('cotizacion_id', $id)->get();
            foreach ($conceptosOriginales as $concepto) {
                $datosConcepto = $concepto->toArray();
                unset($datosConcepto['id'], $datosConcepto['created_at'], $datosConcepto['updated_at']);
                $datosConcepto['cotizacion_id'] = $nuevaCotizacion->id;
                CotizacionConcepto::create($datosConcepto);
            }

            // Duplicar observaciones relacionadas
            $observacionesOriginales = ObservacionCotizacion::where('cotizacion_id', $id)
                ->where('active', 1)
                ->get();
            foreach ($observacionesOriginales as $observacion) {
                $datosObservacion = $observacion->toArray();
                unset($datosObservacion['id'], $datosObservacion['created_at'], $datosObservacion['updated_at']);
                $datosObservacion['cotizacion_id'] = $nuevaCotizacion->id;
                ObservacionCotizacion::create($datosObservacion);
            }

            // Duplicar condiciones comerciales relacionadas
            $condicionesOriginales = CotizacionCondicionComercial::where('cotizacion_id', $id)->first();
            if ($condicionesOriginales) {
                $datosCondiciones = $condicionesOriginales->toArray();
                unset($datosCondiciones['id'], $datosCondiciones['created_at'], $datosCondiciones['updated_at']);
                $datosCondiciones['cotizacion_id'] = $nuevaCotizacion->id;
                CotizacionCondicionComercial::create($datosCondiciones);
            }

            // Duplicar items y subitems manteniendo el mapeo de IDs
            $itemIdMap    = [];  // old_item_id    => new_item_id
            $subitemIdMap = [];  // old_subitem_id => new_subitem_id

            $itemsOriginales = CotizacionItem::where('cotizacion_id', $id)
                ->where('active', 1)
                ->get();
            foreach ($itemsOriginales as $item) {
                $datosItem = $item->toArray();
                $oldItemId = $datosItem['id'];
                unset($datosItem['id'], $datosItem['created_at'], $datosItem['updated_at']);
                $datosItem['cotizacion_id'] = $nuevaCotizacion->id;
                $nuevoItem = CotizacionItem::create($datosItem);
                $itemIdMap[$oldItemId] = $nuevoItem->id;

                // Duplicar subitems de este item
                $subitemsOriginales = CotizacionSubImtes::where('cotizacion_item_id', $oldItemId)->get();
                foreach ($subitemsOriginales as $subitem) {
                    $datosSubitem = $subitem->toArray();
                    $oldSubitemId = $datosSubitem['id'];
                    unset($datosSubitem['id'], $datosSubitem['created_at'], $datosSubitem['updated_at']);
                    $datosSubitem['cotizacion_item_id'] = $nuevoItem->id;
                    $nuevoSubitem = CotizacionSubImtes::create($datosSubitem);
                    $subitemIdMap[$oldSubitemId] = $nuevoSubitem->id;
                }
            }

            // Duplicar productos reasignando item_id y subitem_id al nuevo mapeo,
            // y sus novedades operativas (CotizacionLista) vinculadas a cada producto.
            $productosOriginales = CotizacionProducto::where('cotizacion_id', $id)->get();
            foreach ($productosOriginales as $producto) {
                $oldProductoId = $producto->id;

                $datosProducto = $producto->toArray();
                unset($datosProducto['id'], $datosProducto['created_at'], $datosProducto['updated_at']);
                $datosProducto['cotizacion_id'] = $nuevaCotizacion->id;

                if (!empty($datosProducto['cotizacion_item_id']) && isset($itemIdMap[$datosProducto['cotizacion_item_id']])) {
                    $datosProducto['cotizacion_item_id'] = $itemIdMap[$datosProducto['cotizacion_item_id']];
                }

                if (!empty($datosProducto['cotizacion_subitem_id']) && isset($subitemIdMap[$datosProducto['cotizacion_subitem_id']])) {
                    $datosProducto['cotizacion_subitem_id'] = $subitemIdMap[$datosProducto['cotizacion_subitem_id']];
                }

                $nuevoProducto = CotizacionProducto::create($datosProducto);

                // Duplicar novedades operativas vinculadas a este producto
                $novedadesOriginales = CotizacionLista::where('cotizacion_producto_id', $oldProductoId)->get();
                foreach ($novedadesOriginales as $novedad) {
                    $datosNovedad = $novedad->toArray();
                    unset($datosNovedad['id'], $datosNovedad['created_at'], $datosNovedad['updated_at']);
                    $datosNovedad['cotizacion_id']          = $nuevaCotizacion->id;
                    $datosNovedad['cotizacion_producto_id'] = $nuevoProducto->id;
                    CotizacionLista::create($datosNovedad);
                }
            }

            // Duplicar viáticos
            $viaticosOriginales = CotizacionViatico::where('cotizacion_id', $id)->get();
            foreach ($viaticosOriginales as $viatico) {
                $datosViatico = $viatico->toArray();
                unset($datosViatico['id'], $datosViatico['created_at'], $datosViatico['updated_at']);
                $datosViatico['cotizacion_id'] = $nuevaCotizacion->id;
                CotizacionViatico::create($datosViatico);
            }

            // Duplicar utilidades
            $utilidadesOriginales = CotizacionUtilidad::where('cotizacion_id', $id)->get();
            foreach ($utilidadesOriginales as $utilidad) {
                $datosUtilidad = $utilidad->toArray();
                unset($datosUtilidad['id']);
                $datosUtilidad['cotizacion_id'] = $nuevaCotizacion->id;
                CotizacionUtilidad::create($datosUtilidad);
            }

            DB::commit();

            return $this->obtenerCotizacionPorId($nuevaCotizacion->id);

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Calcular totales de la cotización
     */
    private function calcularTotales(array $data): array
    {
        $subtotal = $data['subtotal'] ?? 0;
        $descuento = $data['descuento'] ?? 0;
        $totalImpuesto = $data['total_impuesto'] ?? 0;

        // Si no se proporciona total, calcularlo
        if (!isset($data['total']) || $data['total'] == 0) {
            $data['total'] = ($subtotal - $descuento) + $totalImpuesto;
        }

        return $data;
    }

    /**
     * Obtener estadísticas de cotizaciones
     */
    public function obtenerEstadisticas(): array
    {
        $estados = ['Borrador', 'Enviado', 'Aprobado', 'Rechazado', 'Terminado', 'Anulado'];

        $conteos = [];
        $totales = [];

        foreach ($estados as $nombre) {
            $query = Cotizacion::whereHas('estado', fn($q) => $q->where('estado', $nombre));
            $conteos[$nombre] = $query->count();
            $totales[$nombre] = $query->sum('total');
        }

        // Pendientes de respuesta: enviadas sin fecha_respuesta
        $pendientesRespuesta = Cotizacion::whereHas('estado', fn($q) => $q->where('estado', 'Enviado'))
            ->whereNull('fecha_respuesta')
            ->count();

        return [
            'total_cotizaciones'      => Cotizacion::count(),
            'valor_total'             => Cotizacion::sum('total'),
            'conteos'                 => $conteos,
            'totales'                 => $totales,
            'pendientes_respuesta'    => $pendientesRespuesta,
            // compatibilidad con código anterior
            'cotizaciones_pendientes' => $conteos['Borrador'] ?? 0,
            'cotizaciones_aprobadas'  => $conteos['Aprobado'] ?? 0,
        ];
    }

    /**
     * Buscar cotizaciones por criterios
     */
    public function buscarCotizaciones(array $criterios): Collection
    {
        $query = Cotizacion::with(['tercero', 'estado', 'autorizacion', 'terceroSucursal', 'terceroContacto']);

        if (!empty($criterios['num_documento'])) {
            $query->where('num_documento', 'like', '%' . $criterios['num_documento'] . '%');
        }

        if (!empty($criterios['tercero_id'])) {
            $query->where('tercero_id', $criterios['tercero_id']);
        }

        if (!empty($criterios['estado_id'])) {
            $query->where('estado_id', $criterios['estado_id']);
        }

        if (!empty($criterios['fecha_desde'])) {
            $query->whereDate('created_at', '>=', $criterios['fecha_desde']);
        }

        if (!empty($criterios['fecha_hasta'])) {
            $query->whereDate('created_at', '<=', $criterios['fecha_hasta']);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }


    public function obtenerUltimaVersion(int $cotizacionId): ?int
    {
        $cotizacion = Cotizacion::find($cotizacionId);
        return $cotizacion ? $cotizacion->version : null;
    }

    public function obtenerConsecutivoDocumento(string $prefix = 'COT'): string
    {
        $lastCotizacion = Cotizacion::where('tipo',$prefix)->orderBy('id', 'desc')->first();
        $nextId = $lastCotizacion ? ($lastCotizacion->id + 1) : 1;

        return $prefix .'-'. str_pad($nextId, 6, '0', STR_PAD_LEFT);
    }



    /**
     * Calcular totales desde items y conceptos
     */
    private function calcularTotalesDesdItems(array $data, array $items, array $conceptos): array
    {
        $subtotal = 0;
        $descuentoTotal = 0;
        $impuestosTotal = 0;

        // Calcular subtotal desde items
        foreach ($items as $item) {
            $valorItem = ($item['cantidad'] ?? 1) * ($item['valor_unitario'] ?? 0);
            $descuentoItem = $item['descuento_valor'] ?? 0;
            $subtotal += ($valorItem - $descuentoItem);
            $descuentoTotal += $descuentoItem;
        }

        // Calcular impuestos desde conceptos
        foreach ($conceptos as $concepto) {
            $valor = $concepto['valor'] ?? 0;
            $impuestosTotal += $valor;
        }

        $data['subtotal'] = $subtotal;
        $data['descuento'] = $descuentoTotal;
        $data['total_impuesto'] = $impuestosTotal;
        $data['total'] = $subtotal + $impuestosTotal - $data['descuento'];

        return $data;
    }

    /**
     * Crear items de cotización
     */
    private function crearItems(int $cotizacionId, array $items): void
    {
        foreach ($items as $index => $item) {
            $item['cotizacion_id'] = $cotizacionId;
            $item['orden'] = $index + 1;

            // Calcular valor total del item
            $cantidad = $item['cantidad'] ?? 1;
            $valorUnitario = $item['valor_unitario'] ?? 0;
            $descuentoValor = $item['descuento_valor'] ?? 0;
            $item['valor_total'] = ($cantidad * $valorUnitario) - $descuentoValor;

            CotizacionItem::create($item);
        }
    }

    /**
     * Crear conceptos de cotización
     */
    private function crearConceptos(int $cotizacionId, array $conceptos): void
    {
        foreach ($conceptos as $index => $concepto) {
            $concepto['cotizacion_id'] = $cotizacionId;
            $concepto['orden'] = $index + 1;

            CotizacionConcepto::create($concepto);
        }
    }

    /**
     * Crear observaciones de cotización
     */
    // private function crearObservaciones(int $cotizacionId, array $observaciones): void
    // {
    //     foreach ($observaciones as $index => $observacion) {
    //         $observacion['cotizacion_id'] = $cotizacionId;
    //         $observacion['orden'] = $index + 1;

    //         CotizacionObservacion::create($observacion);
    //     }
    // }

    /**
     * Crear condiciones comerciales de cotización
     */
    private function crearCondicionesComerciales(int $cotizacionId, array $condiciones): void
    {
        foreach ($condiciones as $index => $condicion) {
            $condicion['cotizacion_id'] = $cotizacionId;
            $condicion['orden'] = $index + 1;

            CotizacionCondicionComercial::create($condicion);
        }
    }

}
