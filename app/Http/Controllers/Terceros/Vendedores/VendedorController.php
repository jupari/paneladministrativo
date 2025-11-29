<?php

namespace App\Http\Controllers\Terceros\Vendedores;

use App\Http\Controllers\Controller;
use App\Models\Vendedor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\Datatables\Datatables;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Can;
use Spatie\Permission\Models\Permission;

class VendedorController extends Controller
{
    public function index(Request $request)
    {
        try {
            $vendedores = Vendedor::orderBy('nombre_completo', 'asc')->get();
            $permissions =  Permission::All();
            if($request->ajax()) {
                return Datatables::of($vendedores)
                                ->addIndexColumn()
                                ->addColumn('id', function ($td) {

                                    $href = $td->id;
                                    return $href;

                                })
                                ->addColumn('identificacion', function ($td) {

                                    $href = $td->identificacion;
                                    return $href;

                                })
                                ->addColumn('nombre_completo', function ($td) {

                                    $href = $td->nombre_completo;
                                    return $href;

                                })
                                ->addColumn('active', function ($td) {

                                    if($td->active==1){
                                        $href = '<span class="badge bg-success">Activo</span>';
                                    }else{
                                        $href = '<span class="badge bg-danger">Inactivo</span>';
                                    }
                                    return $href;
                                })
                                ->addColumn('created_at', function ($td) {

                                    $href = $td->created_at;
                                    return $href;

                                })
                                ->addColumn('acciones', function ($td) {
                                    if(Auth::user()->can('roles.edit')){
                                        $href = '<button type="button" onclick="upVend('.$td->id.')" class="btn btn-warning btn-circle btn-sm" data-toggle="tooltip" data-placement="top" title="Editar Cliente"><i class="fas fa-pencil-alt"></i></button>&nbsp';
                                        $href .= '<button type="button" onclick="deleteVend('.$td->id.')" class="btn btn-danger btn-circle btn-sm" data-toggle="tooltip" data-placement="top" title="Quitar Usuario"><i class="fas fa-trash"></i></button>';
                                    }else{
                                        $href='';
                                    }
                                    return $href;
                                })
                                ->rawColumns(['id', 'identificacion', 'nombre_completo','active','created_at','acciones'])
                                ->make(true);

            }

            return view('terceros.vendedores.index');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'identificacion' => "required|string|max:20||unique:vendedores,identificacion|regex:/^\d{1,20}$/",
            'nombre_completo' => 'required|string|max:255',
            'active' => 'required|in:1,0', // Solo acepta 1 o 0
        ], [
            'identificacion.required' => 'El campo identificación es obligatorio.',
            'identificacion.unique' => 'La identificación ingresada ya existe.',
            'identificacion.regex' => 'La identificación debe ser númerico sin signos y maximo de 20 caracteres',
            'nombre_completo.required' => 'El campo nombre completo es obligatorio.',
            'active.required' => 'El campo estado activo es obligatorio.',
            'active.in' => 'El estado activo debe ser 1 (activo) o 0 (inactivo).',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            Vendedor::create($request->all());
            return response()->json(['success' => true, 'message' => 'Vendedor creado exitosamente.'], 200);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function edit($id)
    {
        try {
            $vendedores = Vendedor::where('id', $id)->first();
            return response()->json(['success' => true, 'data' =>$vendedores]);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'identificacion' => "required|string|max:20|regex:/^\d{1,20}$/",
            'nombre_completo' => 'required|string|max:255',
            'active' => 'required|in:1,0', // Solo acepta 1 o 0
        ], [
            'identificacion.required' => 'El campo identificación es obligatorio.',
            'identificacion.unique' => 'La identificación ingresada ya existe.',
            'identificacion.regex' => 'La identificación debe ser númerico sin signos y maximo de 20 caracteres',
            'nombre_completo.required' => 'El campo nombre completo es obligatorio.',
            'active.required' => 'El campo estado activo es obligatorio.',
            'active.in' => 'El estado activo debe ser 1 (activo) o 0 (inactivo).',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

         try {
            $vendedor = Vendedor::findOrFail($id);
            $vendedor->update($request->all());
            return response()->json(['success' => true, 'message' => 'Vendedor actualizado exitosamente.'], 200);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Elimina un vendedor.
     */
    public function destroy($id)
    {
        try {
            $vendedor = Vendedor::findOrFail($id);
            $vendedor->delete();
            return response()->json(['success' => false, 'message' => 'Registro eliminado con éxito'], 422);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
