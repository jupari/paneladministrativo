<?php

namespace App\Http\Controllers\Terceros\Clientes;

use App\Http\Controllers\Controller;
use App\Models\TerceroSucursal;
use Spatie\Permission\Models\Permission;
use Exception;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Yajra\Datatables\Datatables;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Can;

use Illuminate\Http\Request;

class SucursalClienteController extends Controller
{
    //
    public function index(Request $request,$id){
        try {

            $query= TerceroSucursal::with('vendedores', 'ciudades')->where('tercero_id',$id)->orderBy('created_at')->get();
            $permissions =  Permission::All();
            if($request->ajax()) {
                return Datatables::of($query)
                                ->addIndexColumn()
                                ->addColumn('comercial', function ($td) {

                                    $href = $td->vendedores->nombre_completo??'';
                                    return $href;

                                })
                                 ->addColumn('nombre_sucursal', function ($td) {

                                    $href = $td->nombre_sucursal;
                                    return $href;

                                })
                                ->addColumn('correo', function ($td) {

                                    $href = $td->correo;
                                    return $href;

                                })
                                ->addColumn('telefono', function ($td) {

                                    $href = $td->telefono;
                                    return $href;

                                })
                                ->addColumn('celular', function ($td) {

                                    $href = $td->celular;
                                    return $href;

                                })
                                ->addColumn('ciudad', function ($td) {

                                    $href = $td->ciudades->nombre;
                                    return $href;

                                })
                                ->addColumn('direccion', function ($td) {

                                    $href = $td->direccion;
                                    return $href;

                                })
                                ->addColumn('persona_contacto', function ($td) {

                                    $href = $td->persona_contacto;
                                    return $href;

                                })
                                ->addColumn('acciones', function ($td) {
                                    if(Auth::user()->can('roles.edit')){
                                        $href = '<button type="button" onclick="showSucursal('.$td->id.')" class="btn btn-warning btn-circle btn-sm" data-toggle="tooltip" data-placement="top" title="Editar Contacto"><i class="fas fa-pencil-alt"></i></button>&nbsp';
                                    }else{
                                        $href='';
                                    }
                                   $href .= '<button type="button" onclick="deleteSucursal('.$td->id.')" class="btn btn-danger btn-circle btn-sm" data-toggle="tooltip" data-placement="top" title="Quitar Contacto"><i class="fas fa-trash"></i></button>';

                                return $href;

                                })
                                ->rawColumns(['comercial','nombre_sucursal','correo','telefono','celular','ciudad','direccion','persona_contacto', 'acciones'])
                                ->make(true);

            }
        } catch (Exception $e) {
            return response()->json(['OK'=>false, 'msg'=>$e->getMessage()]);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tercero_id' => 'required|exists:terceros,id',
            'nombre_sucursal' => 'required|string|max:50',
            'ciudad_id' => 'nullable|exists:ciudades,id',
            'vendedor_id'=> 'nullable|exists:vendedores,id',
            'correo' => 'required|email|max:50|unique:terceros_sucursales,correo',
            'celular' => 'nullable|regex:/^[0-9]{10}$/',
            'telefono' => 'nullable|regex:/^[0-9]{10}$/',
            'direccion' => 'nullable|string|max:50',
            'persona_contacto' => 'required|string|max:100',
        ],[
            'celular.regex' => 'El número de celular debe tener exactamente 10 dígitos y solo contener números.',
            'telefono.regex' => 'El número de teléfono debe tener exactamente 10 dígitos y solo contener números.',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $sucursal = TerceroSucursal::create($request->all());
            return response()->json(['success' => true, 'message' => 'Sucursal creada exitosamente.', 'data' => $sucursal]);
        // } catch (\Illuminate\Validation\ValidationException $e) {
        //     // Devolver errores de validación como JSON
        //     return response()->json(['success' => false, 'errorssuc' => $e->errors()], 422);
        }catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function edit($id)
    {
        try {
            $sucursal = TerceroSucursal::findOrFail($id);
            return response()->json(['success' => true, 'data' => $sucursal]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Sucursal no encontrada.'], 404);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'tercero_id' => 'required|exists:terceros,id',
            'nombre_sucursal' => 'required|string|max:50',
            'ciudad_id' => 'nullable|exists:ciudades,id',
            'vendedor_id'=> 'nullable|exists:vendedores,id',
            'correo' => 'required|email|max:50',
            'celular' => 'nullable|regex:/^[0-9]{10}$/',
            'telefono' => 'nullable|regex:/^[0-9]{10}$/',
            'direccion' => 'nullable|string|max:50',
            'persona_contacto' => 'required|string|max:100',
        ],[
            'celular.regex' => 'El número de celular debe tener exactamente 10 dígitos y solo contener números.',
            'telefono.regex' => 'El número de teléfono debe tener exactamente 10 dígitos y solo contener números.',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $sucursal = TerceroSucursal::findOrFail($id);
            $sucursal->update($request->all());
            return response()->json(['success' => true, 'message' => 'Sucursal actualizada exitosamente.', 'data' => $sucursal]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Devolver errores de validación como JSON
            return response()->json(['success' => false, 'errorssuc' => $e->errors()], 422);
        }catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $sucursal = TerceroSucursal::findOrFail($id);
            $sucursal->delete();
            return response()->json(['Ok' => true, 'message' => 'Registro eliminada exitosamente.']);
        } catch (\Exception $e) {
            return response()->json(['Ok' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function getSucursales($clienteId){
        $query= TerceroSucursal::where('tercero_id',$clienteId)->orderBy('created_at')->get();
        return response()->json(['status'=>true, 'data'=>$query??[]]);

    }
}
