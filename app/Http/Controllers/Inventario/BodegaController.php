<?php

namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\Controller;
use App\Models\Bodega;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class BodegaController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Bodega::query();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('codigo', fn($row) => $row->codigo)
                ->addColumn('nombre', fn($row) => $row->nombre)
                ->addColumn('ubicacion', fn($row) => $row->ubicacion)
                ->addColumn('estado', function ($td) {
                        if ($td->active==1) {
                            return '<div  class="col-6 text-center rounded-pill">
                                        <span class="badge bg-success">Activo</span>
                                    </div>';
                                }
                        if ($td->active==0) {
                            return '<div class="col-6 text-center rounded-pill">
                                    <span class="badge bg-warning">Inactivo</span>
                                </div>';
                        }
                    })
                ->addColumn('acciones', function ($row) {
                    return '
                        <button class="btn btn-warning btn-sm editar-bodega" data-id="'.$row->id.'"><i class="fas fa-edit"></i></button>
                        <button class="btn btn-danger btn-sm eliminar-bodega" data-id="'.$row->id.'"><i class="fas fa-trash"></i></button>
                    ';
                })
                ->rawColumns(['codigo','nombre','ubicacion','estado','acciones'])
                ->make(true);
        }

        return view('inventario.bodega.index');
    }

    public function store(Request $request)
    {
        $request->validate([
            'codigo' => 'required|unique:inv_bodegas,codigo',
            'nombre' => 'required',
        ]);

        Bodega::create($request->all());

        return response()->json(['message' => 'Bodega creada correctamente']);
    }

    public function edit($id)
    {
        $bodega = Bodega::findOrFail($id);
        return response()->json(['success' => 'Ok', 'data' => $bodega]);
    }

    public function update(Request $request, $id)
    {
        $bodega = Bodega::findOrFail($id);

        $request->validate([
            'codigo' => 'required|unique:inv_bodegas,codigo,'.$bodega->id,
            'nombre' => 'required',
        ]);

        $bodega->update($request->all());

        return response()->json(['message' => 'Bodega actualizada correctamente']);
    }

    public function destroy($id)
    {
        $bodega = Bodega::findOrFail($id);
        $bodega->delete();

        return response()->json(['message' => 'Bodega eliminada correctamente']);
    }

    public function listar()
    {
        $bodegas = Bodega::where('active', true)->get(['id', 'nombre']);
        return response()->json(['success' => true, 'data' => $bodegas], 200);
    }
}
