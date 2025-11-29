<?php

namespace App\Http\Controllers\Produccion;

use App\Http\Controllers\Controller;
use App\Models\FichaTecnicaProceso;
use App\Models\Proceso;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class FichaTecnicaProcesoController extends Controller
{
    // public function index($id)
    // {
    //     return FichaTecnicaProceso::where('fichatecnica_id', $id)->get();
    // }

    // public function store(Request $request)
    // {
    //     $material = FichaTecnicaProceso::updateOrCreate(
    //         ['id' => $request->id ?? null],
    //         $request->all()
    //     );
    //     return response()->json($material);
    // }

    public function index($fichatecnica_id, Request $request)
    {
        if ($request->ajax()) {
            $data = FichaTecnicaProceso::where('fichatecnica_id', $fichatecnica_id)
                ->orderBy('id', 'asc')
                ->get();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('acciones', function ($row) {
                    return '
                        <button class="btn btn-success btn-sm guardarproceso" data-id="'.$row->id.'">
                            <i class="fas fa-save"></i>
                        </button>
                        <button class="btn btn-danger btn-sm eliminarproceso" data-id="'.$row->id.'">
                            <i class="fas fa-trash"></i>
                        </button>
                    ';
                })
                ->rawColumns(['acciones'])
                ->make(true);
        }

        return view('produccion.fichatecnica.materiales.index', compact('fichatecnica_id'));
    }

    public function store(Request $request)
    {


        $proceso = FichaTecnicaProceso::create([
            'fichatecnica_id' => $request->fichatecnica_id,
            'codigo_proceso' => $request->codigo_proceso ?? '',
            'proceso_id' => 0,
            'observacion' => $request->observacion ?? '',
            'costo' => $request->costo ?? 0,
            'codigo' => $request->codigo ?? '',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Material agregado correctamente',
            'data' => $proceso
        ]);
    }

    public function update(Request $request, $id)
    {
        $fproceso = FichaTecnicaProceso::findOrFail($id);

        $proceso=Proceso::where('codigo', $request->codigo_proceso)->first();

        $fproceso->update([
            'fichatecnica_id' => $request->fichatecnica_id,
            'codigo_proceso' => $request->codigo_proceso ?? '',
            'proceso_id' => $proceso?$proceso->id:0,
            'observacion' => $request->observacion ?? '',
            'costo' => $request->costo ?? 0,
            'codigo' => $request->codigo ?? '',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Proceso actualizado correctamente',
            'data' => $fproceso
        ]);
    }


    public function destroy($id)
    {
        FichaTecnicaProceso::findOrFail($id)->delete();
        return response()->json(['success' => true,'message'=>'Se eliminó el registro con éxito.']);
    }
}
