<?php

namespace App\Http\Controllers\Produccion;

use App\Http\Controllers\Controller;
use App\Models\FichaTecnicaMaterial;
use Exception;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class FichaTecnicaMaterialController extends Controller
{
    // public function index($id)
    // {
    //     return FichaTecnicaMaterial::where('fichatecnica_id', $id)->get();
    // }

    // public function store(Request $request)
    // {
    //     try {
    //         $material = FichaTecnicaMaterial::updateOrCreate(
    //             ['id' => $request->id ?? null],
    //             $request->all()
    //         );
    //         return response()->json(['message'=>'El registro se guardó con éxito. ', 'data'=>$material]);
    //     } catch (Exception $e) {
    //         throw response()->json(['message'=>'Error al guardar el registro '. $e->getMessage()]);
    //     }
    // }

    public function index($fichatecnica_id, Request $request)
    {
        try {
            //code...
            if ($request->ajax()) {
                $data = FichaTecnicaMaterial::where('fichatecnica_id', $fichatecnica_id)
                    ->orderBy('id', 'asc')
                    ->get();

                return DataTables::of($data)
                    ->addIndexColumn()
                    ->addColumn('acciones', function ($row) {
                        $id = $row->id ?? 0;
                        return '
                            <button class="btn btn-success btn-sm guardar" data-id="'.$id.'">
                                <i class="fas fa-save"></i>
                            </button>
                            <button class="btn btn-danger btn-sm eliminar" data-id="'.$id.'">
                                <i class="fas fa-trash"></i>
                            </button>
                        ';
                    })
                    ->rawColumns(['acciones'])
                    ->make(true);
            }

            return view('produccion.fichatecnica.materiales.index', compact('fichatecnica_id'));
        } catch (Exception $e) {
            return response()->json(['message'=>'Error al cargar tabla materiales. '.$e->getMessage()]);
        }
    }

    public function store(Request $request)
    {
        $material = FichaTecnicaMaterial::create([
            'fichatecnica_id' => $request->fichatecnica_id,
            'referencia_codigo' => $request->referencia_codigo ?? '',
            'unidad_medida' => $request->unidad_medida ?? '',
            'prop_1' => $request->prop_1 ?? '',
            'prop_2' => $request->prop_2 ?? '',
            'cantidad' => $request->cantidad ?? 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Material agregado correctamente',
            'data' => $material
        ]);
    }

    public function update(Request $request, $id)
    {
        $material = FichaTecnicaMaterial::findOrFail($id);

        $material->update([
            'referencia_codigo' => $request->referencia_codigo,
            'unidad_medida' => $request->unidad_medida,
            'prop_1' => $request->prop_1,
            'prop_2' => $request->prop_2,
            'cantidad' => $request->cantidad,
            'codigo' => $request->codigo
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Material actualizado correctamente',
            'data' => $material
        ]);
    }

    public function destroy($id)
    {
        FichaTecnicaMaterial::findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }
}
