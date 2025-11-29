<?php

namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\Controller;
use App\Models\MovimientoDetalle;
use App\Models\Saldo;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Exception;

class SaldoController extends Controller
{
    public function index(Request $request)
    {
        try {
            if ($request->ajax()) {
                $query = Saldo::with(['producto', 'bodega']);

                // Filtros dinámicos
                if ($request->filled('producto')) {
                    $query->where('producto_id', $request->producto_id);
                }
                if ($request->filled('talla')) {
                    $query->where('talla', $request->talla);
                }
                if ($request->filled('color')) {
                    $query->where('color', $request->color);
                }
                if ($request->filled('bodega_id')) {
                    $query->where('bodega_id', $request->bodega_id);
                }

                $data = $query->get();

                return DataTables::of($data)
                    ->addIndexColumn()
                    ->addColumn('id', fn($row) => $row->id)
                    ->addColumn('producto', fn($row) => $row->producto?->nombre ?? '-')
                    ->addColumn('talla', fn($row) => $row->talla ?? '-')
                    ->addColumn('color', fn($row) => $row->color ?? '-')
                    ->addColumn('bodega', fn($row) => $row->bodega?->nombre ?? '-')
                    ->editColumn('saldo_cantidad', fn($row) => number_format($row->saldo, 2))
                    ->editColumn('ultimo_costo', fn($row) => '$'.number_format($row->ultimo_costo, 2))
                    ->addColumn('acciones', function($row){
                                        return '
                                            <button class="btn btn-primary btn-sm ver-saldos"
                                                    data-producto-id="'.$row->producto_id.'"
                                                    data-bodega-id="'.$row->bodega_id.'"
                                                    data-codigo="'.$row->producto?->codigo.'"
                                                    data-bodega="'.$row->bodega?->nombre.'"
                                                >
                                                <i class="fas fa-eye"></i>
                                            </button>';
                                    })
                    ->rawColumns(['producto','talla','color','bodega','saldo_cantidad','ultimo_costo','acciones'])
                    ->make(true);
            }

            return view('inventario.saldo.index');
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al cargar saldos',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function listar($product_id, $bodega_id)
    {
        try {
            $movimientos = MovimientoDetalle::with('producto', 'bodega')
                ->where('producto_id', $product_id)
                ->where('bodega_id', $bodega_id)
                ->get();

            return response()->json($movimientos);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al listar saldos',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}
