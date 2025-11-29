<?php

namespace App\Http\Controllers\Terceros\Clientes;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ciudad;
use App\Models\Tercero;
use App\Models\TerceroContacto;
use App\Models\TipoIdentificacion;
use App\Models\TipoPersona;
use Exception;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Yajra\Datatables\Datatables;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Can;
use Spatie\Permission\Models\Permission;

class ContactoClienteController extends Controller
{
    public function index(Request $request,$id){
        try {

            $query= TerceroContacto::where('tercero_id',$id)->orderBy('created_at')->get();
            $permissions =  Permission::All();
            if($request->ajax()) {
                return Datatables::of($query)
                                ->addIndexColumn()
                                ->addColumn('nombres', function ($td) {

                                    $href = $td->nombres??'';
                                    return $href;

                                })
                                ->addColumn('apellidos', function ($td) {

                                    $href = $td->apellidos;
                                    return $href;

                                })
                                ->addColumn('cargo', function ($td) {

                                    $href = $td->cargo;
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
                                ->addColumn('acciones', function ($td) {
                                    if(Auth::user()->can('roles.edit')){
                                        $href = '<button type="button" onclick="showContacto('.$td->id.')" class="btn btn-warning btn-circle btn-sm" data-toggle="tooltip" data-placement="top" title="Editar Contacto"><i class="fas fa-pencil-alt"></i></button>&nbsp';
                                    }else{
                                        $href='';
                                    }
                                   $href .= '<button type="button" onclick="deleteContacto('.$td->id.')" class="btn btn-danger btn-circle btn-sm" data-toggle="tooltip" data-placement="top" title="Quitar Contacto"><i class="fas fa-trash"></i></button>';

                                return $href;

                                })
                                ->rawColumns(['nombres','apellidos','cargo','correo','telefono','celular','acciones'])
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
            'nombres' => 'required|string|max:255',
            'apellidos' => 'nullable|string|max:255',
            'correo' => 'required|email|max:255|unique:terceros_sucursales,correo',
            'celular' => 'nullable|regex:/^[0-9]{10}$/',
            'telefono' => 'nullable|regex:/^[0-9]{10}$/',
            'ext' => 'nullable|string|max:10',
            'cargo' => 'required|string|max:255',
        ],[
            'celular.regex' => 'El número de celular debe tener exactamente 10 dígitos y solo contener números.',
            'telefono.regex' => 'El número de teléfono debe tener exactamente 10 dígitos y solo contener números.',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $sucursal = TerceroContacto::create($request->all());
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
            $sucursal = TerceroContacto::findOrFail($id);
            return response()->json(['success' => true, 'data' => $sucursal]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Sucursal no encontrada.'], 404);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'tercero_id' => 'required|exists:terceros,id',
            'nombres' => 'required|string|max:255',
            'apellidos' => 'nullable|string|max:255',
            'correo' => 'required|email|max:255',
            'celular' => 'nullable|regex:/^[0-9]{10}$/',
            'telefono' => 'nullable|regex:/^[0-9]{10}$/',
            'ext' => 'nullable|string|max:10',
            'cargo' => 'required|string|max:255',
        ],[
            'celular.regex' => 'El número de celular debe tener exactamente 10 dígitos y solo contener números.',
            'telefono.regex' => 'El número de teléfono debe tener exactamente 10 dígitos y solo contener números.',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $sucursal = TerceroContacto::findOrFail($id);
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
            $sucursal = TerceroContacto::findOrFail($id);
            $sucursal->delete();
            return response()->json(['Ok' => true, 'message' => 'Registro eliminada exitosamente.']);
        } catch (\Exception $e) {
            return response()->json(['Ok' => false, 'error' => $e->getMessage()], 500);
        }
    }
}
