<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use Spatie\Permission\Models\Role;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Can;
use Spatie\Permission\Models\Permission;

class UsuarioRolesController extends Controller
{
    //
     // Asegúrate de incluir el trait si es necesario
     use AuthorizesRequests;


     public function __construct()
    {
        $this->middleware('can:roles.index')->only('index');
        // $this->middleware('can:roles.create')->only('store');
        // $this->middleware('can:roles.edit')->only('edit','update');
    }

     public function index(Request $request)
     {
        $this->authorize('view', new Role);

         // // Campos para tablas siempre y cuando sean iguales
        $campos = ['id', 'name'];

         try {

             $roles = Role::select($campos)
                             ->with([
                                 'permissions' => function($td) use($campos){
                                     $td->select($campos);
                                 }
                             ])
                             ->get();
            $permissions =  Permission::All();
            if($request->ajax()) {
                return Datatables::of($roles)
                                ->addIndexColumn()
                                ->addColumn('nombre', function ($td) {

                                    $href = $td->name;
                                    return $href;

                                })
                                ->addColumn('permisos', function ($td) {

                                    $href = $td->permissions->pluck('name')->implode(', ');

                                    if(count($td->permissions) == 0){

                                        $href = "Este rol aún no tiene asignado permisos";

                                    }

                                    return $href;

                                })
                                ->addColumn('acciones', function ($td) {
                                    if(Auth::user()->can('roles.edit')){
                                        $href = '<button type="button" onclick="upRol('.$td->id.')" class="btn btn-warning btn-circle btn-sm" data-toggle="tooltip" data-placement="top" title="Editar rol"><i class="fas fa-pencil-alt"></i></button>&nbsp';
                                    }else{
                                        $href='';
                                    }
                                    // $href .= '<button type="button" class="btn btn-danger btn-circle btn-sm" data-toggle="tooltip" data-placement="top" title="Quitar Usuario"><i class="fas fa-trash"></i></button>';

                                return $href;

                                })
                                ->rawColumns(['nombre', 'permisos', 'acciones'])
                                ->make(true);

            }
            return view('admin.roles.index', [
                'permisos'=>$permissions?$permissions:[]
            ]);

         }
         catch (Exception $e) {

             return response()->json(['error' => 'Error al obtener usuarios ' . $e->getMessage()], 500);
         }


     }

     public function edit(Request $request, $id)
    {
        // Campos para tablas siempre y cuando sean iguales
        $campos = ['id', 'name', 'guard_name'];

        $roles = Role::select($campos)
                        ->with([
                            'permissions' => function($td) use($campos){
                                $td->select($campos);
                            }
                        ])
                        ->findOrFail($id);

        $this->authorize('update', $roles);

        if($roles){

        return response()->json([
            'roles' => $roles,
            'message' => 'Roles obtenido exitosamente'
        ], 200);

        }
    }

    public function store(Request $request)
    {
        $this->authorize('create', new Role);

        $validation =  Validator::make($request->all(),[
            'name'=>'required|unique:Spatie\Permission\Models\Role,name',
            'guard_name'=>'required'
        ]);

        if($validation->fails()){
            return response()->json(['errors'=>$validation->errors()],422);
        }

        try {
            $data = [

                'name' => $request->name,
                'guard_name' => $request->guard_name

            ];

            $rol = Role::create($data);

            if($request->has('permissions')){

                $rol->givePermissionTo($request->permissions);

            }

            // Registrar urol
            return response()->json(['message' => 'Rol creado exitosamente'], 200);



        }catch (Exception $e) {

            // Manejar la excepción aquí
            return response()->json(['error' => 'Error al registrar el rol: ' . $e->getMessage()], 500);
        }


    }

    public function update(Request $request, $id)
    {
        $rol = Role::select('id', 'name', 'guard_name')->findOrFail($id);
        $this->authorize('update', $rol);

        $validation =  Validator::make($request->all(),[
            'name'=>'required',
            'guard_name'=>'required'
        ]);

        if($validation->fails()){
            return response()->json(['errors'=>$validation->errors()],422);
        }

        try {

            $data = [

                'name' => $request->name,
                'guard_name' => $request->guard_name

            ];

            $update = $rol->update($data);

            if($request->has('permissions')){

                $rol->syncPermissions($request->permissions);

            }

            if($update){

                // Registrar urol
                return response()->json(['message' => 'Rol actualizado exitosamente'], 200);

            }
        }catch (Exception $e) {

            // Manejar la excepción aquí
            return response()->json(['error' => 'Error al actualizar el rol: ' . $e->getMessage()], 500);
        }
    }
}
