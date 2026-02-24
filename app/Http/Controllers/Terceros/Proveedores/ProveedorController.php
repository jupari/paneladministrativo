<?php

namespace App\Http\Controllers\Terceros\Proveedores;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProveedorRequest;
use App\Models\Ciudad;
use App\Models\Pais;
use App\Models\Tercero;
use App\Models\TipoIdentificacion;
use App\Models\TipoPersona;
use App\Models\Vendedor;
use App\Services\ProveedorService;
use Exception;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Can;
use Spatie\Permission\Models\Permission;

class ProveedorController extends Controller
{
    protected $proveedorService;

    public function __construct(ProveedorService $proveedorService)
    {
        $this->proveedorService = $proveedorService;
    }

    public function index(Request $request)
    {
        try {
            $proveedores = $this->proveedorService->obtenerProveedores();

            if ($request->ajax()) {
                return DataTables::of($proveedores)
                    ->addIndexColumn()
                    ->addColumn('tipoid', fn ($td) => $td->tipoIdentificacion->nombre)
                    ->addColumn('identificacion', fn ($td) => $td->identificacion)
                    ->addColumn('tipopersona', fn ($td) => $td->tipoPersona->nombre)
                    ->addColumn('nombres', fn ($td) => $td->nombres)
                    ->addColumn('apellidos', fn ($td) => $td->apellidos)
                    ->addColumn('nombre_estableciemiento', fn ($td) => $td->nombre_establecimiento)
                    ->addColumn('correo', fn ($td) => $td->correo)
                    ->addColumn('telefono', fn ($td) => $td->telefono)
                    ->addColumn('celular', fn ($td) => $td->celular)
                    ->addColumn('created_at', fn ($td) => $td->created_at)
                    ->addColumn('acciones', function ($td) {
                        return auth()->user()->can('terceros.edit') ?
                            '<button type="button" onclick="upProv(' . $td->id . ')" class="btn btn-warning btn-circle btn-sm">
                                <i class="fas fa-pencil-alt"></i>
                            </button>' : '';
                    })
                    ->rawColumns(['tipoid','identificacion','tipopersona','nombres','apellidos','nombre_estableciemiento','correo','telefono','telefono','celular','created_at','acciones'])
                    ->make(true);
            }
            
            $permissions =  Permission::All();
            $tiposIdentificacion = TipoIdentificacion::where('active',1)->orderBy('nombre')->get();
            $tiposPersona=TipoPersona::where('active',1)->orderBy('nombre')->get();
            $ciudades=Ciudad::where('active',1)->orderBy('nombre')->get();
            $vendedores=Vendedor::where('active',1)->orderBy('nombre_completo')->get();
            $paises = Pais::with('departamentos.ciudades')->get();
            $authUser= auth()->user();
            
            return view('terceros.proveedores.index', [
                'permisos'=>$permissions?$permissions:[],
                'user'=>auth()->user()->roles->pluck('name'),
                'tiposIdentificacion'=>$tiposIdentificacion,
                'tiposPersona'=>$tiposPersona,
                'paises'=>$paises,
                'vendedores'=>$vendedores,
                'user_id'=>$authUser->id,
                'tercerotipo_id'=>2 // 2 = Proveedores
            ]);
        } catch (\Exception $e) {
            Log::error("Error en ProveedorController@index: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al obtener proveedores.'.$e->getMessage()], 500);
        }
    }

    public function store(ProveedorRequest $request)
    {
        try {
            $proveedor = $this->proveedorService->crearProveedor($request->validated());
            return response()->json(['success' => true, 'message' => 'Proveedor creado exitosamente.', 'data' => $proveedor]);
        } catch (\Exception $e) {
            Log::error("Error en ProveedorController@store: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al crear el proveedor.'], 500);
        }
    }

    public function edit($id)
    {
        try {
            $proveedor = $this->proveedorService->obtenerProveedorPorId($id);
            // En modo edición, usar el user_id del proveedor original, no del usuario logueado
            return response()->json([
                'success' => true,
                'data' => $proveedor,
                'user_id' => $proveedor->user_id ?? auth()->id(), // Usar el del proveedor o fallback al logueado
                'tercerotipo_id' => $proveedor->tercerotipo_id ?? 2
            ]);
        } catch (\Exception $e) {
            Log::error("Error en ProveedorController@edit: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Proveedor no encontrado.'], 404);
        }
    }

    public function update(ProveedorRequest $request, $id)
    {
        try {
            $proveedor = $this->proveedorService->actualizarProveedor($id, $request->validated());
            return response()->json(['success' => true, 'message' => 'Proveedor actualizado.', 'data' => $proveedor]);
        } catch (\Exception $e) {
            Log::error("Error en ProveedorController@update: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al actualizar el proveedor.'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $this->proveedorService->eliminarProveedor($id);
            return response()->json(['success' => true, 'message' => 'Proveedor eliminado correctamente.']);
        } catch (\Exception $e) {
            Log::error("Error en ProveedorController@destroy: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al eliminar el proveedor.'], 500);
        }
    }
}
