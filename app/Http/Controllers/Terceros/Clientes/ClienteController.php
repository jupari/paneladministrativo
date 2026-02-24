<?php

namespace App\Http\Controllers\Terceros\Clientes;

use App\Http\Controllers\Controller;
use App\Http\Requests\ClienteRequest;
use App\Models\Ciudad;
use App\Models\Pais;
use App\Models\Tercero;
use App\Models\TipoIdentificacion;
use App\Models\TipoPersona;
use App\Models\Vendedor;
use App\Repositories\ClienteRepository;
use App\Services\ClienteService;
use Beta\Microsoft\Graph\Model\Vendor;
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


class ClienteController extends Controller
{
    //
    protected $clienteRepo;

    public function __construct(ClienteService $clienteRepo)
    {
        $this->clienteRepo = $clienteRepo;
    }

    public function index(Request $request)
    {
        try {
            $clientes = $this->clienteRepo->obtenerClientes();

            if ($request->ajax()) {
                return DataTables::of($clientes)
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
                        return auth()->user()->can('roles.edit') ?
                            '<button type="button" onclick="upCli(' . $td->id . ')" class="btn btn-warning btn-circle btn-sm">
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
            if(auth()->user()->roles->pluck('name')[0]=='Vendedor'){
                $vendedorxrol =  Vendedor::where('identificacion', auth()->user()->identificacion)->first();
            }else
            {
                $vendedorxrol = null;
            }
            return view('terceros.clientes.index', [
                'permisos'=>$permissions?$permissions:[],
                'user'=>auth()->user()->roles->pluck('name'),
                'tiposIdentificacion'=>$tiposIdentificacion,
                'tiposPersona'=>$tiposPersona,
                'paises'=>$paises,
                'vendedores'=>$vendedores,
                'vendedorxrol'=>$vendedorxrol,
                'user_id'=>$authUser->id,
                'tercerotipo_id'=>1
            ]);
        } catch (\Exception $e) {
            Log::error("Error en ClienteController@index: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al obtener clientes.'.$e->getMessage()], 500);
        }
    }

    public function store(ClienteRequest $request)
    {
        try {
            $cliente = $this->clienteRepo->crearCliente($request->validated());
            return response()->json(['success' => true, 'message' => 'Cliente creado exitosamente.', 'data' => $cliente]);
        } catch (\Exception $e) {
            Log::error("Error en ClienteController@store: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al crear el cliente.'], 500);
        }
    }

    public function edit($id)
    {
        try {
            $cliente = $this->clienteRepo->obtenerClientePorId($id);
            // En modo edición, usar el user_id del cliente original, no del usuario logueado
            return response()->json([
                'success' => true,
                'data' => $cliente,
                'user_id' => $cliente->user_id ?? auth()->id(), // Usar el del cliente o fallback al logueado
                'tercerotipo_id' => $cliente->tercerotipo_id ?? 1
            ]);
        } catch (\Exception $e) {
            Log::error("Error en ClienteController@edit: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Cliente no encontrado.'], 404);
        }
    }

    public function update(ClienteRequest $request, $id)
    {
        try {
            $cliente = $this->clienteRepo->actualizarCliente($id, $request->validated());
            return response()->json(['success' => true, 'message' => 'Cliente actualizado.', 'data' => $cliente]);
        } catch (\Exception $e) {
            Log::error("Error en ClienteController@update: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al actualizar el cliente.'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $this->clienteRepo->eliminarCliente($id);
            return response()->json(['success' => true, 'message' => 'Cliente eliminado correctamente.']);
        } catch (\Exception $e) {
            Log::error("Error en ClienteController@destroy: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al eliminar el cliente.'], 500);
        }
    }
}
