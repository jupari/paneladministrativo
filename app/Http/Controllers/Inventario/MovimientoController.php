<?php

namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\Controller;
use App\Models\Movimiento;
use App\Services\InventarioService;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use DateTime;
use Exception;

class MovimientoController extends Controller
{
    protected $inventarioService;

    public function __construct(InventarioService $inventarioService)
    {
        $this->inventarioService = $inventarioService;
    }

    /**
     * Mostrar listado de movimientos
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $movimientos = Movimiento::with(['usuario'])
                ->orderBy('created_at','desc')
                ->get();

            return DataTables::of($movimientos)
                ->addIndexColumn()
                ->addColumn('id', fn($row) => $row->id)
                ->addColumn('numero_documento', fn($row) => $row->num_doc)
                ->addColumn('created_at', function ($td) {
                        $date = new DateTime($td->created_at);
                        return $date->format('d/m/Y');
                    })
                ->addColumn('usuario', fn($row) => $row->usuario?->name ?? '-')
                ->addColumn('observacion', fn($row) => $row->observacion ?? '-')
                ->addColumn('tipo', fn($row) => ucfirst($row->tipo))
                ->addColumn('acciones', function($row){
                    return '
                        <button class="btn btn-warning btn-sm editar-movimiento" data-id="'.$row->id.'">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-danger btn-sm eliminar-movimiento" data-id="'.$row->id.'">
                            <i class="fas fa-trash"></i>
                        </button>
                    ';
                })
                ->rawColumns(['id','numero_documento','created_at','usuario','observacion','tipo','acciones'])
                ->make(true);
        }

        return view('inventario.movimiento.index');
    }

    /**
     * Guardar nuevo movimiento
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'num_doc'        => 'required',
            'tipo'           => 'required|in:entrada,salida,ajuste',
            'observacion'    => 'nullable',
            'doc_ref'        => 'nullable',
            'usuario_id'     => 'nullable',
        ]);

        try {
            $movimiento =  Movimiento::create([
                'num_doc'     => $validated['num_doc'],
                'tipo'        => $validated['tipo'],
                'observacion' => $validated['observacion'] ?? null,
                'doc_ref'     => $validated['doc_ref'] ?? null,
                'usuario_id'  => auth()->id(),
            ]);
            return response()->json(['success'=>true,'message'=>'Movimiento registrado correctamente','data'=>$movimiento],200);
        } catch (Exception $e) {
            return response()->json(['success'=>false,'error'=>$e->getMessage()],422);
        }
    }

    /**
     * Mostrar un movimiento (para edición)
     */
    public function edit($id)
    {
        try {
            $movimiento = Movimiento::with('usuario')->findOrFail($id);
            return response()->json(['success'=>true,'data'=>$movimiento]);
        } catch (Exception $e) {
            return response()->json(['success'=>false,'error'=>$e->getMessage()],404);
        }
    }

    /**
     * Actualizar un movimiento
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'num_doc'        => 'required',
            'tipo'           => 'required|in:entrada,salida,ajuste',
            'observacion'    => 'nullable',
            'doc_ref'        => 'nullable',
            'usuario_id'     => 'nullable',
        ]);

        try {
            $movimiento =  Movimiento::findOrFail($id);
            $movimiento->update([
                'num_doc'     => $validated['num_doc'],
                'tipo'        => $validated['tipo'],
                'observacion' => $validated['observacion'] ?? null,
                'doc_ref'     => $validated['doc_ref'] ?? null,
                'usuario_id'  => auth()->id(),
            ]);
            return response()->json(['success'=>true,'message'=>'Movimiento actualizado correctamente', 'data'=>$movimiento],200);
        } catch (Exception $e) {
            return response()->json(['success'=>false,'error'=>$e->getMessage()],422);
        }
    }

    /**
     * Eliminar un movimiento
     */
    public function destroy($id)
    {
        try {
            $movimiento = Movimiento::findOrFail($id);
            $movimiento->delete();

            // ⚠️ Ojo: aquí no se ajusta saldo automáticamente, deberías manejarlo según política.
            return response()->json(['success'=>true,'message'=>'Movimiento eliminado correctamente']);
        } catch (Exception $e) {
            return response()->json(['success'=>false,'error'=>$e->getMessage()],422);
        }
    }
}
