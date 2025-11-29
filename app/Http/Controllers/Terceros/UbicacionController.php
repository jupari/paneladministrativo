<?php

namespace App\Http\Controllers\Terceros;

use App\Http\Controllers\Controller;
use App\Models\Ciudad;
use App\Models\Departamento;
use App\Models\Pais;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\Datatables\Datatables;

class UbicacionController extends Controller
{
    //Ubicacion ciudad
    public function index(Request $request)
    {
        try {
            $ciudades = Ciudad::with('departamento.pais')->get();
            //return response()->json($paises);
            if($request->ajax()) {
                return DataTables::of($ciudades)
                                ->addIndexColumn()
                                ->addColumn('id', function ($td) {

                                    $href = $td->id;
                                    return $href;

                                })
                                ->addColumn('pais', function ($td) {

                                    $href = $td->departamento->pais->nombre;
                                    return $href;

                                })
                                ->addColumn('departamento', function ($td) {

                                    $href = $td->departamento->nombre;
                                    return $href;

                                })
                                ->addColumn('ciudad', function ($td) {

                                    $href = $td->nombre;
                                    return $href;
                                })
                                ->addColumn('acciones', function ($td) {
                                    if(Auth::user()->can('roles.edit')){
                                         $href = '<button type="button" onclick="upCiudad('.$td->id.')" class="btn btn-warning btn-circle btn-sm" data-toggle="tooltip" data-placement="top" title="Editar Cliente"><i class="fas fa-pencil-alt"></i></button>&nbsp';
                                    }else{
                                        $href='';
                                    }
                                    return $href;
                                })
                                ->rawColumns(['id', 'pais', 'departamento','ciudad','acciones'])
                                ->make(true);

            }

            $paises = Pais::with('departamentos.ciudades')->orderBy('nombre')->get();
            //dd($paises);
            return view('terceros.ciudades.index', [
                'paises'=>$paises
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }



    }

     public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'pais_id' => 'nullable|exists:paises,id',
            'departamento_id' => 'nullable|exists:departamentos,id',
        ]);

        if ($request->tipo == 'pais') {
            Pais::create($request->only('nombre'));
        } elseif ($request->tipo == 'departamento') {
            Departamento::create($request->only('nombre', 'pais_id'));
        } else {
            Ciudad::create($request->only('nombre', 'departamento_id', 'pais_id'));
        }

        return response()->json(['success' => true, 'message' => 'Ciudad creada éxitosamente.'], 200);
    }

    public function edit($id)
    {
        try {
            $ciudades = Ciudad::findOrFail($id);
            return response()->json(['success' => true, 'data' => $ciudades]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
        ]);

        $ciudad =  Ciudad::where('id', $id)->first();
        $ciudad->update($request->all());
        return response()->json(['success' => true, 'message' => 'Ciudad actualizada éxitosamente.', 'data' =>[]]);
    }

    public function destroy($id, $tipo)
    {
        if ($tipo == 'pais') {
            Pais::destroy($id);
        } elseif ($tipo == 'departamento') {
            Departamento::destroy($id);
        } else {
            Ciudad::destroy($id);
        }

        return redirect()->route('ubicaciones.index')->with('success', 'Registro eliminado con éxito.');
    }

    //Ubicacion Departamento

    //Ubicacion Pais

}
