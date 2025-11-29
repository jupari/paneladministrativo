<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use Spatie\Permission\Models\Permission;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class RolesPermisosController extends Controller
{
    //
    use AuthorizesRequests;
    public function __construct()
    {
        $this->middleware('can:permisos.index')->only('index');
        $this->middleware('can:permisos.create')->only('create','store');
        $this->middleware('can:permisos.edit')->only('edit','update');
    }

    public function index(Request $request)
    {
        $this->authorize('view', new Permission);

        try {

            $permissions = Permission::select('id', 'name','description', 'guard_name')->get();

            if($request->ajax()) {


                return Datatables::of($permissions)
                ->addIndexColumn()
                ->addColumn('nombre', function ($td) {

                    $href = $td->name;
                    return $href;

                })
                ->addColumn('descripcion', function ($td) {

                    $href = $td->description;
                    return $href;

                })
                ->addColumn('guard_name', function ($td) {

                    $href = $td->guard_name;
                    return $href;

                })
                ->addColumn('acciones', function ($td) {

                    if(Auth::user()->can('permisos.edit')){
                        $href = '<button type="button" onclick="upPerm('.$td->id.')" class="btn btn-warning btn-circle btn-sm" data-toggle="tooltip" data-placement="top" title="Editar permiso"><i class="fas fa-pencil-alt"></i></button>&nbsp';
                    }else{
                        $href='';
                    }
                    // $href .= '<button type="button" class="btn btn-danger btn-circle btn-sm" data-toggle="tooltip" data-placement="top" title="Quitar Usuario"><i class="fas fa-trash"></i></button>';

                return $href;

                })
                ->rawColumns(['nombre', 'descripcion','guard_name', 'acciones'])
                ->make(true);

            }

            return view('admin.permission.index');

        }
        catch (Exception $e) {

            return response()->json(['error' => 'Error al obtener lista de permisos ' . $e->getMessage()], 500);
        }


    }

    public function edit(Request $request, $id)
    {
        try{

            $permission = Permission::select('id', 'name','description', 'guard_name')->findOrFail($id);

            $this->authorize('update', $permission);

            if($permission){

                return response()->json([
                    'permission' => $permission,
                    'message' => 'Lista de permisos obtenida exitosamente'
                ], 200);

            }

        }
        catch (Exception $e) {

            return response()->json(['error' => 'Error al obtener permiso ' . $e->getMessage()], 500);
        }


    }

    public function store(Request $request)
    {
        $this->authorize('create', new Permission);


        $validation =  Validator::make($request->all(),[
            'name'=>'required|unique:Spatie\Permission\Models\Permission,name',
            'description'=>'required',
            'guard_name'=>'required'
        ]);

        if($validation->fails()){
            return response()->json(['errors'=>$validation->errors()],422);
        }

        try {

            $data = [

                'name' => $request->name,
                'description'=>$request->description,
                'guard_name' => $request->guard_name

            ];

            $permission = Permission::create($data);

            // Registrar urol
            return response()->json(['message' => 'Permiso creado exitosamente'], 200);



        }catch (Exception $e) {

            // Manejar la excepción aquí
            return response()->json(['error' => 'Error al registrar un nuevo permiso: ' . $e->getMessage()], 500);
        }


    }

    public function update(Request $request, $id)
    {
        try {

            $permission = Permission::findOrFail($id);

            $this->authorize('update', $permission);

            $validation =  Validator::make($request->all(),[
                'name'=>'required',
                'description'=>'required',
                'guard_name'=>'required'
            ]);

            if($validation->fails()){
                return response()->json(['errors'=>$validation->errors()],422);
            }


            $data = [

                'name' => $request->name,
                'description'=>$request->description,
                'guard_name' => $request->guard_name

            ];

            $update = $permission->update($data);

            if($update){

                // Aactualizar permiso
                return response()->json(['message' => 'Permiso actualizado exitosamente'], 200);

            }

        }catch (Exception $e) {

            // Manejar la excepción aquí
            return response()->json(['error' => 'Error al actualizar permiso: ' . $e->getMessage()], 500);
        }
    }
}
