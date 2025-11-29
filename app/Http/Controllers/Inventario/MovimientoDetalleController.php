<?php

namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\Controller;
use App\Http\Requests\MovimientoDetalleRequest;
use App\Models\InventarioMovimiento;
use App\Models\Producto;
use App\Models\Bodega;
use App\Models\Movimiento;
use App\Models\MovimientoDetalle;
use App\Services\InventarioService;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Exception;

class MovimientoDetalleController extends Controller
{
    protected $inventarioService;

    public function __construct(InventarioService $inventarioService)
    {
        $this->inventarioService = $inventarioService;
    }

    /**
     * Mostrar listado de movimientos
     */
    public function index($num_documento)
    {
        try {
            $movimientosDetalles = MovimientoDetalle::where('num_doc', $num_documento)->get();
            return response()->json($movimientosDetalles);
        } catch (Exception $e) {
            return response()->json(['success'=>false,'error'=>$e->getMessage()],404);
        }
    }

    /**
     * Guardar nuevo movimiento
     */
    public function store(MovimientoDetalleRequest $request)
    {
        $data = $request->validated();

        $movimiento = $this->inventarioService->registrarMovimientoDetalle($data);

        return response()->json([
            'message' => 'Movimiento registrado correctamente',
            'data' => $movimiento
        ], 201);
    }

    /**
     * Mostrar un movimiento (para edición)
     */
    public function edit($id)
    {
        try {
            $movimiento = MovimientoDetalle::where('producto_id', $id)->get();
            return response()->json(['success'=>true,'data'=>$movimiento]);
        } catch (Exception $e) {
            return response()->json(['success'=>false,'error'=>$e->getMessage()],404);
        }
    }

    /**
     * Actualizar un movimiento
     */
    public function update(MovimientoDetalleRequest $request, $id)
    {
        $data = $request->validated();
        $movimiento = $this->inventarioService->registrarMovimientoDetalle($data, $id);

        return response()->json([
            'message' => 'Movimiento actualizado correctamente',
            'data' => $movimiento
        ], 200);
    }

    /**
     * Eliminar un movimiento
     */
    public function destroy($id)
    {
        try {
            date_default_timezone_set('America/Bogota');
            $movimientosDetalles = MovimientoDetalle::findOrFail($id);
            $fechaMovimiento = date('Y-m-d', strtotime($movimientosDetalles->created_at));
            $fechaHoy = date('Y-m-d');

            if ($fechaMovimiento !== $fechaHoy) {
                return response()->json(['success'=>false,'error'=>'Solo se pueden eliminar movimientos del día actual.'], 403);
            }
            $movimientosDetalles->delete();
            return response()->json(['success'=>true,'message'=>'Movimiento eliminado correctamente']);
        } catch (Exception $e) {
            return response()->json(['success'=>false,'error'=>$e->getMessage()],422);
        }
    }
}
