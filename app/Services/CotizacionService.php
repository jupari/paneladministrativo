<?php

namespace App\Services;

use App\Models\Autorizar;
use App\Models\Cotizacion;
use App\Models\CotizacionCondicionComercial;
use App\Models\ObservacionCotizacion;
use App\Models\CotizacionConcepto;
use App\Models\CotizacionItem;
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

            // Remover ID y timestamps
            unset($datos['id']);

            // Generar nuevo número de documento
            $datos['num_documento'] = $this->generarNumeroDocumento();

            // Incrementar versión
            $datos['version'] = ($datos['version'] ?? 1) + 1;

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

            // Duplicar items relacionados
            $itemsOriginales = CotizacionItem::where('cotizacion_id', $id)
                ->where('active', 1)
                ->get();
            foreach ($itemsOriginales as $item) {
                $datosItem = $item->toArray();
                unset($datosItem['id'], $datosItem['created_at'], $datosItem['updated_at']);
                $datosItem['cotizacion_id'] = $nuevaCotizacion->id;
                CotizacionItem::create($datosItem);
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
        $total = Cotizacion::count();
        $totalValor = Cotizacion::sum('total');
        $pendientes = Cotizacion::whereHas('estado', function($query) {
            $query->where('estado', 'like', '%Borrador%');
        })->count();

        $aprobadas = Cotizacion::whereHas('estado', function($query) {
            $query->where('estado', 'like', '%Terminado%');
        })->count();

        return [
            'total_cotizaciones' => $total,
            'valor_total' => $totalValor,
            'cotizaciones_pendientes' => $pendientes,
            'cotizaciones_aprobadas' => $aprobadas
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
