<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Company;
use App\Repositories\UserRepository;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Str;
use Mail;
use Exception;
use GuzzleHttp\Psr7\Message;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;
use stdClass;
use Yajra\DataTables\DataTables;

class UserController extends Controller
{
    //
    use AuthorizesRequests;
    protected $userRepository;

    // Llamar Funciones del Repositorio UsuarioRepository
    public function __construct(UserRepository $userRepository)
    {

        $this->userRepository = $userRepository;

        $this->middleware('can:users.index')->only('indexDataTable');
        // $this->middleware('can:permisos.create')->only('create','store');
        // $this->middleware('can:permisos.edit')->only('edit','update');
    }

    private function setAuthUser()
    {
        // Verifica si el usuario está autenticado a través del guard 'api' o 'web'
        // $authUser = auth('api')->check()
        //     ? auth('api')->user()
        //     : auth()->user();

        // return $authUser;
    }

    public function lista(User $user, Request $request)
    {
        try {

            // Politica y permiso incluido: ver usuario
            $usuarios = User::all();

            return response()->json(['usuarios' => $usuarios]);
        } catch (Exception $e) {

            return response()->json(['error' => 'Error al obtener usuarios ' . $e->getMessage()], 500);
        }
    }

    // 1. lista usuarios
    public function indexDataTable(User $user, Request $request)
    {
        try {

            // Politica y permiso incluido: ver usuario

            $usuarios = User::with(['roles', 'company'])->get();
            $companies = Company::where('is_active', 1)->get();

            $roles =  Role::all();
            $authUser = auth()->user();
            if($authUser->hasRole('Administrator')){
                //$usuarios = User::all();
                // $usuarios = $usuarios->reject(function ($user) {
                //     return $user->hasRole('Administrator');
                // });
            }
            if ($request->ajax()) {
                return DataTables::of($usuarios)
                    ->addIndexColumn()
                    ->addColumn('nombres', function ($td) {

                        $href = $td->name;
                        return $href;
                    })
                    ->addColumn('email', function ($td) {

                        $href = $td->email;
                        return $href;
                    })
                    ->addColumn('identificacion', function ($td) {

                        $href = $td->identificacion;
                        return $href;
                    })
                    ->addColumn('empresa', function ($td) {

                        $href = $td->company ? $td->company->name : '<span class="text-muted">Sin empresa</span>';
                        return $href;
                    })
                    ->addColumn('rol', function ($td) {

                        $href = $td->getRoleNames()->implode(', ');
                        return $href;
                    })
                    ->addColumn('fecha_cr', function ($td) {

                        $href = date('Y-m-d h:i:s A', strtotime($td->created_at));

                        return $href;
                    })
                    ->addColumn('active', function ($td) {
                        if($td->active==1){
                            $href = '<input class="form-check-input" type="checkbox" id="activecta" checked="true">';
                        }else{
                            $href = '<input class="form-check-input" type="checkbox" id="activecta" checked="false">';
                        }

                        return $href;
                    })
                    ->addColumn('acciones', function ($td) {
                        if(Auth::user()->hasRole(['Administrator', 'Administrador', 'sysadmin'])){
                            $href = '<button type="button" onclick="upUsr(' . $td->id . ')" class="btn btn-warning btn-circle btn-sm" data-toggle="tooltip" data-placement="top" title="Editar usuario"><i class="fas fa-pencil-alt"></i></button>&nbsp';
                            $href .= '<button type="button" onclick="changep(' . $td->id . ')" class="btn btn-info btn-circle btn-sm" data-toggle="tooltip" data-placement="top" title="Cambiar contraseña"><i class="fas fa-key"></i></button>';
                        }else{
                            $href = '<button type="button" onclick="changep(' . $td->id . ')" class="btn btn-info btn-circle btn-sm" data-toggle="tooltip" data-placement="top" title="Cambiar contraseña"><i class="fas fa-key"></i></button>';
                        }

                        return $href;
                    })
                    ->rawColumns(['nombres','email','identificacion','empresa','rol','fecha_cr','active','acciones'])
                    ->make(true);
            }
            return view('admin.users.index', compact('roles', 'companies'));
        } catch (Exception $e) {

            return response()->json(['error' => 'Error al obtener los usuarios. '. $e->getMessage()], 500);
        }
    }



    public function index(User $user, Request $request)
    {
        try {

            // Politica y permiso incluido: ver usuario

            $usuarios = $this->userRepository->userData()->allowed()->get();
            $authUser = auth()->user();

            $usuariosFinal = array();

            foreach ($usuarios as $key => $user) {

                $usuarioFinal = new stdClass();
                $href = '<button type="button" onclick="upUsr(' . $user->id . ')" class="btn btn-warning btn-circle btn-sm" data-toggle="tooltip" data-placement="top" title="Modificar usuario"><i class="fas fa-pencil-alt"></i></button>&nbsp';
                if ($this->setAuthUser()->documento === $user->documento) {
                    $href .= '<button type="button" onclick="changep(' . $user->id . ')" class="btn btn-info btn-circle btn-sm" data-toggle="tooltip" data-placement="top" title="Cambiar contraseña"><i class="fas fa-key"></i></button>';
                }

                $rol = collect($user->getRoleNames());



                $usuarioFinal->documento = $user->documento;
                $usuarioFinal->nombres = $user->nombres;
                $usuarioFinal->correo = $user->correo;
                $usuarioFinal->apellidos = $user->apellido_1 . " " .$user->apellido_2;
                $usuarioFinal->rol = ($rol->isEmpty())? 'Usuario App': $rol[0] ;
                $usuarioFinal->fecha_cr = date('Y-m-d h:i:s A', strtotime($user->created_at));
                $usuarioFinal->acciones = $href;


                $usuariosFinal[] = $usuarioFinal;

            }


            return view('admin.usuario.index',['usuarios'=> $usuariosFinal]);
        } catch (Exception $e) {

            return response()->json(['error' => 'Error al obtener usuarios '], 500);
        }
    }



    // 2. Mostrar usuario
    public function edit(Request $request, $id)
    {
        $this->authorize('update', $this->userRepository->userData()->findOrFail($id));

        $campos = [
            'id', 'name', 'email','created_at','active','identificacion','company_id'
        ];

        try {

            $usuario =User::select($campos)
                ->with([
                    'roles' => function ($td) {
                        $td->select('id', 'name');
                    },
                    'company' => function ($td) {
                        $td->select('id', 'name');
                    }
                ])
                ->findOrFail($id);

            if ($usuario) {

                return response()->json([
                    'usuario' => $usuario,
                    'message' => 'Usuario obtenido exitosamente'
                ], 200);
            }
        } catch (Exception $e) {

            return response()->json(['error' => 'Error al obtener usuario ' . $e->getMessage()], 500);
        }
    }

    // 3. Registrar usuario
    public function store(Request $request)
    {
        try {
            $authUser = auth()->user();

            // Validar que usuarios no-sysadmin solo asignen su empresa
            if (!$authUser->hasRole('sysadmin') && $request->company_id) {
                if (!$authUser->company_id || $request->company_id != $authUser->company_id) {
                    return response()->json([
                        'errors' => ['company_id' => ['Solo puede asignar usuarios a su propia empresa.']]
                    ], 422);
                }
            }

            $validation =  Validator::make($request->all(),[
                'name'=>'required|min:3',
                'email'=>'required|unique:App\Models\User,email|regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
                // 'password'=>'required|regex:/^(?=.*[A-Z])(?=.*\d)[A-Za-z\d]{8,}$/',
            ]);

            if($validation->fails()){
                return response()->json(['errors'=>$validation->errors()], 422);
            }

            // Si no es sysadmin y no especifica empresa, usar la empresa del usuario actual
            $companyId = $request->company_id;
            if (!$authUser->hasRole('sysadmin') && !$companyId && $authUser->company_id) {
                $companyId = $authUser->company_id;
            }

            $act =  $request->active=='1'?1:0;
            $ok = User::create([
                'name'=>$request->name,
                'email'=>$request->email,
                'active'=>$act,
                'company_id'=>$companyId ?: null,
                'password'=>$request->password?bcrypt($request->password):bcrypt('Password0'),
                'identificacion'=>$request->identificacion
            ]);

            if($ok){
                $asignRol= User::orderBy('id', 'desc')->first();
                $asignRol->syncRoles([$request->role]);
                return response()->json([
                    'message'=>'El usuario se creó con éxito.'
                ],200);

            }else{
                return response()->json([
                    'message'=>'El usuario no pudo ser creado.'
                ],502);
            }

        } catch (Exception $e) {
            // Manejar la excepción aquí
            return response()->json(['error' => 'Error al registrar usuario: ' . $e->getMessage()], 500);
        }
    }

    // 4. Actualizar usuario
    public function update($id,Request $request)
    {
        try {
            $authUser = auth()->user();

            // Validar que usuarios no-sysadmin solo asignen su empresa
            if (!$authUser->hasRole('sysadmin') && $request->company_id) {
                if (!$authUser->company_id || $request->company_id != $authUser->company_id) {
                    return response()->json([
                        'errors' => ['company_id' => ['Solo puede asignar usuarios a su propia empresa.']]
                    ], 422);
                }
            }

            $validation =  Validator::make($request->all(),[
                'name'=>'required|min:3',
                'email'=>'required|regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
                // 'password'=>'required|regex:/^(?=.*[A-Z])(?=.*\d)[A-Za-z\d]{8,}$/',
            ]);

            if($validation->fails()){
                return response()->json(['errors'=>$validation->errors()], 422);
            }

            // Si no es sysadmin y no especifica empresa, usar la empresa del usuario actual
            $companyId = $request->company_id;
            if (!$authUser->hasRole('sysadmin') && !$companyId && $authUser->company_id) {
                $companyId = $authUser->company_id;
            }


            $upd =  User::where('id',$id)->first();

            $act =  $request->active=='1'?1:0;

            if(isset($upd)){
                $upd->name=$request->name;
                $upd->email=$request->email;
                $upd->identificacion=$request->identificacion;
                $upd->company_id=$companyId ?: null;
                $upd->active=$act;
                $upd->update();
                $upd->syncRoles([$request->role]);

                return response()->json([
                    'message'=>'El usuario de actualizó con éxito.'
                ],200);
            }
            else{
                return response()->json([
                    'message'=>'No es posible actualizar el usuario.'
                ],400);
            }


        } catch (Exception $e) {
            return response()->json([
                'error'=>'No es posible actualizar el usuario.' . $e->getMessage()
            ],500);
        }

    }

    public function changePass(Request $request, $id)
    {
        $campos = ['id', 'password'];

        $validation =  Validator::make($request->all(),[
            'password'=>'required|regex:/^(?=.*[A-Z])(?=.*\d)[A-Za-z\d]{8,}$/',
        ]);

        if($validation->fails()){
            return response()->json(['errors'=>$validation->errors()], 422);
        }

        $usuario = User::select($campos)->findOrFail($id);

        try {

            $data = [

                'password' => bcrypt($request->password),

            ];


            $update = $usuario->update($data);

            if ($update) {

                // Valida si el usuario y el perfil han sido actualizados
                return response()->json([
                    'message' => 'Contraseña actualizada exitosamente!',
                ], 201);
            }
        } catch (Exception $e) {

            // Manejar la excepción aquí
            return response()->json(['error' => 'Error al actualizar la contraseña: ' . $e->getMessage()], 500);
        }
    }

    public function removeArchivo(Request $request, $id)
    {
        try {

        } catch (Exception $e) {
            // Manejar la excepción aquí
            return response()->json(['error' => 'Error al quitar archivo'], 500);
        }
    }

}
