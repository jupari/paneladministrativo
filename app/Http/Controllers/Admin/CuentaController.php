<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cuenta;
use App\Models\CuentaMadre;
use App\Models\Estado;
use App\Models\Logs_files;
use App\Models\User;
use Carbon\Carbon;
use DateTime;
use Exception;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Yajra\Datatables\Datatables;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\DB;
use stdClass;
use Illuminate\Support\Str;

class CuentaController extends Controller
{
    //
    use AuthorizesRequests;

    public function __construct()
    {
        $this->middleware('can:cuentaabonado.index')->only('index');
        $this->middleware('can:cuentaabonado.create')->only('create', 'store');
        $this->middleware('can:cuentaabonado.edit')->only('edit', 'update');
    }


    public function index(Request $request)
    {
        // // Campos para tablas siempre y cuando sean iguales
        $campos = ['id', 'user_id', 'estado_id', 'nombre_cuenta', 'usuario_dist', 'password_cuenta', 'fecha_asig', 'tiempo'];

        try {

            $usuarioLogeado = Auth::user()->id;

            $authUser = auth()->user();

            if ($authUser->hasRole('Administrator')) {
                $cuentaAbonado = Cuenta::with('usuario', 'estado')
                    ->select($campos)
                    ->get();
            } else {
                $cuentaAbonado = Cuenta::with('usuario', 'estado')
                    ->whereHas('estado', function ($query) {
                        $query->where('estado', 'Asignadas')->orWhere('estado', 'Vencidas');
                    })
                    ->where('usuario_dist', $usuarioLogeado)
                    ->select($campos)
                    ->get();
            }

            $usuarioAbonados = User::role('Distribuidor')->orderBy('name')->get();
            $estados = Estado::orderBy('estado')->get();
            if ($request->ajax()) {
                return Datatables::of($cuentaAbonado)
                    ->addIndexColumn()
                    ->addColumn('id', function ($td) {
                        $href = $td->id;
                        return $href;
                    })
                    ->addColumn('nombre_usuario', function ($td) {

                        if ($td->usuario) {
                            $href = $td->usuario->name;
                        } else {
                            $usuario =  User::where('id', $td->usuario_dist)->first();
                            if ($usuario) {
                                $href = $usuario->name;
                            } else {
                                $href = '';
                            }
                        }
                        return $href;
                    })
                    ->addColumn('nombre_cuenta', function ($td) {

                        $href = $td->nombre_cuenta;

                        return $href;
                    })
                    ->addColumn('password_cuenta', function ($td) {
                        $href = $td->password_cuenta;
                        return $href;
                    })
                    ->addColumn('fecha_asig', function ($td) {

                        $date = new DateTime($td->fecha_asig);
                        $href = $date->format('d/m/Y');

                        return $href;
                    })
                    ->addColumn('tiempo', function ($td) {

                        $href = 30 - ($td->tiempo == 0 ? 0 : $td->tiempo) . ' de 30 día(s)';
                        return $href;
                    })
                    ->addColumn('estado', function ($td) {
                        $href = '<div class="bg-' . $td->estado->color . ' text-center rounded-pill"><span class="badge">' . $td->estado->estado . '</span></div>';
                        return $href;
                    })
                    ->addColumn('acciones', function ($td) {
                        if (Auth::user()->can('cuentaabonado.edit')) {
                            $href = '<button type="button" onclick="upCuenta(' . $td->id . ')" class="btn btn-secondary btn-circle btn-sm" data-toggle="tooltip" data-placement="top" title="Editar Cuenta"><i class="fas fa-pencil-alt"></i></button>&nbsp';
                        } else {
                            $href = '';
                        }
                        // $href .= '<button type="button" class="btn btn-danger btn-circle btn-sm" data-toggle="tooltip" data-placement="top" title="Quitar Usuario"><i class="fas fa-trash"></i></button>';

                        return $href;
                    })
                    ->rawColumns(['id', 'nombre_usuario', 'nombre_cuenta', 'password_cuenta', 'estado', 'fecha_asig', 'acciones'])
                    ->make(true);
            }
            return view('admin.cuentaabonado.index', ['usuarios' => $usuarioAbonados, 'estados' => $estados]);
        } catch (Exception $e) {

            return response()->json(['error' => 'Error al obtener las Cuentas Principales ' . $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {

        try {
            //code...

            $validation =  Validator::make($request->all(), [
                'nombre_cuenta' => 'required',
                'password_cuenta' => 'required',
            ]);

            if ($validation->fails()) {
                return response()->json(['errors' => $validation->errors()], 422);
            }

            $cuentaPpal = Cuenta::create([
                'user_id' => $request->user_id,
                'usuario_dist' => $request->usuario_dist,
                'estado_id' => $request->estado_id,
                'nombre_cuenta' => $request->nombre_cuenta,
                'fecha_asig' => $request->fecha_asig,
                'password_cuenta' => $request->password_cuenta
            ]);

            if ($cuentaPpal) {
                return response()->json(['message' => 'La Cuenta se creó con éxito'], 200);
            } else {
                return response()->json(['message' => 'No es posible crear la Cuenta'], 502);
            }
        } catch (Exception $e) {
            return response()->json(['error' => 'No es posible guardar la Cuenta ' . $e->getMessage()], 500);
        }
    }

    public function edit(Request $request, $id)
    {
        try {
            $campos = ['id', 'usuario_dist', 'estado_id', 'nombre_cuenta', 'password_cuenta', 'fecha_asig'];
            $cuenta = Cuenta::select($campos)->findOrFail($id);

            if ($cuenta) {

                return response()->json([
                    'cuentas' => $cuenta,
                    'message' => 'Lista de las cuentas se obtuvo con éxito.'
                ], 200);
            }
        } catch (Exception $e) {

            return response()->json(['error' => 'Error al obtener las cuentas ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {

            $validation =  Validator::make($request->all(), [
                'nombre_cuenta' => 'required',
                'password_cuenta' => 'required',
            ]);

            if ($validation->fails()) {
                return response()->json(['errors' => $validation->errors()], 422);
            }

            $estadoId = 2;

            if ($request->usuario_dist > 0 && !$request->estado_id) {
                $estadoId = 1;
            } elseif ($request->estado_id) {
                # code...
                $estadoId = $request->estado_id;
            }

            $cuenta = Cuenta::findOrFail($id);

            $data = [
                'user_id' => $request->user_id ? $request->user_id : $cuenta->user_id,
                'usuario_dist' => $request->usuario_dist ? $request->usuario_dist : $cuenta->usuario_dist,
                'estado_id' => $estadoId,
                'nombre_cuenta' => $request->nombre_cuenta ? $request->nombre_cuenta : $cuenta->nombre_cuenta,
                'fecha_asig' => $request->fecha_asig ? $request->fecha_asig : $cuenta->fecha_asig,
                'password_cuenta' => $request->password_cuenta ? $request->password_cuenta : $cuenta->password_cuenta

            ];

            $update = $cuenta->update($data);

            //Se actualiza la tabla de cuentas madres para estar las dos con la misma data
            $email = $request->nombre_cuenta ? $request->nombre_cuenta : $cuenta->nombre_cuenta;

            $cuentaMadre = CuentaMadre::where('email', $email)->first();

            $datamadre = [
                'usuario_dist' => $request->usuario_dist ? $request->usuario_dist : $cuenta->usuario_dist,
            ];

            $updateMadre = $cuentaMadre->update($datamadre);

            if ($update) {
                // Aactualizar
                return response()->json(['message' => 'La Cuenta se modificó con éxito'], 200);
            }
        } catch (Exception $e) {

            // Manejar la excepción aquí
            return response()->json(['error' => 'Error al actualizar la Cuenta Principal: ' . $e->getMessage()], 500);
        }
    }

    //type: post
    //route: excel/import post : excel
    public function verifyExcelData(Request $request)
    {
        try {
            $file = $request->file('excel_file');
            if (!$file) {
                return response()->json(['error' => 'No file provided'], 400);
            }

            $spreadsheet = IOFactory::load($file->getPathname());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();
            $lote = Str::uuid();
            $dataQuantity = count($rows) - 1;
            $rowsToInsert = [];

            $totalRows = count($rows);

            $emailPattern = '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/';
            $dateTimePattern = '/^\d{2}\/\d{2}\/\d{4}$/';
            $regex = '/^(?:(?:(?:0?[1-9]|[12][0-9]|3[01])(?:\.(?:0?[1-9]|1[0-2])(?:\.(?:[0-9]{2}))?|(?:\/)(?:0?[1-9]|1[0-2])(?:\/)(?:[0-9]{2})))|(?:[0-9]{4})-(?:(?:0?[1-9]|1[0-2])(?:\.(?:0?[1-9]|1[0-2])(?:\.(?:[0-9]{2}))?|(?:\/)(?:0?[1-9]|1[0-2])(?:\/)(?:[0-9]{2}))))}$/';

            for ($i = 1; $i < $totalRows; $i++) {
                $data = $rows[$i];

                if (empty($data[1])) {
                    continue;
                }

                // if(preg_match($dateTimePattern, $data[3])){
                //     dd(new Carbon(str_replace('/', '-', $data[3])));
                //     dd(Carbon::hasFormatWithModifiers($data[3], 'd#m#Y!'));
                // }

                $rowsToInsert[] = [
                    'dato1' => $this->getIdUser($data[0]) ? $this->getIdUser($data[0]) : 'Usuario distribuidor no existe',  //abonado
                    'dato2' => !preg_match($emailPattern, $data[1]) ? 'Correo no valido. (' . $data[1] . ')' : (!$this->verifyDuplicatedEmail($data[1]) ? $data[1] : 'Correo no existe ' . '( ' . $data[1] . ' )'),   //$data[1],//email
                    'dato3' => $data[2] ?? 'N/A', //clave
                    'fecha' => preg_match($dateTimePattern, $data[3]) ? Carbon::parse(str_replace('/', '-', $data[3])) : 'Fecha no valida. (' . $data[3] . ')', //fecha asignacion
                    'dato4' => preg_replace('([^A-Za-z0-9])', '', $data[4]), //estado ()
                    'lote' => $lote //lote
                ];
            }

            if (!empty($rowsToInsert)) {
                Logs_files::insert($rowsToInsert);
            }

            $logsRows = Logs_files::where('lote', $lote)->get();
            $logsFilesQuantity = $logsRows->count();

            return response()->json(['cantExcelFiles' => $dataQuantity, 'cantFilesFound' => $logsFilesQuantity, 'rows' => $logsRows, 'lote' => $lote]);
        } catch (Exception $e) {
            return response()->json(['error' => 'El archivo no es valido.' . $e->getMessage()], 500);
        }
    }

    //type: post
    //route: excel/insertarCuentasMadre post : excel
    public function insertarCuentas(Request $request)
    {

        if (!isset($request->lote)) {
            return response()->json(['msg' => 'Debe enviar un número de lote valido.']);
        }

        try {
            //code...
            $logsFiles = Logs_files::where('lote', $request->lote)->get();
            $cantRegSaved = 0;
            $cantRegLote = $logsFiles->count();

            $emailPattern = '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/';
            $dateTimePattern = '/^\d{2}\/\d{2}\/\d{4}$/';

            $regex = '/Fecha no valida\. \((.*?)\)/';
            foreach ($logsFiles as  $logFile) {
                $cant =  Cuenta::where('nombre_cuenta', $logFile->dato2)->count();
                if ($cant == 0) {
                    //SE ANULA POR QUE NO VAN A INSERTAR FILAS
                    // if(!preg_match($emailPattern, $logFile->dato2)){
                    //     continue;
                    // }

                    // preg_match($regex, $logFile->fecha, $matches);
                    // if(isset($matches[1])){
                    //     continue;
                    // }

                    // $save =  Cuenta::create([
                    //     'usuario_dist'=> User::role('Distribuidor')->where('name',$logFile->dato1)->first()
                    //                     ? User::role('Distribuidor')->where('name',$logFile->dato1)->first()->id
                    //                     :(User::role('Distribuidor')->where('identificacion',$logFile->dato1)->first()
                    //                         ?User::role('Distribuidor')->where('identificacion',$logFile->dato1)->first()->id
                    //                         :User::role('Distribuidor')->where('name','No aplica')->first()->id),
                    //     'nombre_cuenta'=>$logFile->dato2,
                    //     'password_cuenta'=>$logFile->dato3??'',
                    //     'fecha_asig'=>Carbon::parse($logFile->fecha)??null,
                    //     'estado_id'=>$logFile->dato4?
                    //                     Estado::where('estado', $logFile->dato4)->first()->id
                    //                     :Estado::where('estado', 'Sin Estado')->first()->id,
                    // ]);
                    // $cantRegSaved++;
                } else {
                    $dataUpdated = [
                        // 'usuario_dist'=> User::role('Distribuidor')->where('name',$logFile->dato1)->first()
                        //                 ? User::role('Distribuidor')->where('name',$logFile->dato1)->first()->id
                        //                 :(User::role('Distribuidor')->where('identificacion',$logFile->dato1)->first()
                        //                     ?User::role('Distribuidor')->where('identificacion',$logFile->dato1)->first()->id
                        //                     :User::role('Distribuidor')->where('name','No aplica')->first()->id),
                        'usuario_dist'      => $logFile->dato1,
                        'nombre_cuenta'     => $logFile->dato2,
                        'password_cuenta'   => $logFile->dato3 ?? '',
                        'fecha_asig'        => Carbon::parse($logFile->fecha) ?? null,
                        'estado_id'         => $logFile->dato4 ?
                            Estado::where('estado', $logFile->dato4)->first()->id
                            : Estado::where('estado', 'Sin Estado')->first()->id,
                    ];

                    $updateCuenta = Cuenta::where('nombre_cuenta', $logFile->dato2)->first();
                    if ($updateCuenta) {
                        $update = $updateCuenta->update($dataUpdated);
                        if ($update) {
                            //Se actualiza la cuenta madre
                            $dataUpdatedCM = [
                                // 'usuario_dist'=> User::role('Distribuidor')->where('name',$logFile->dato1)->first()
                                //                 ? User::role('Distribuidor')->where('name',$logFile->dato1)->first()->id
                                //                 :(User::role('Distribuidor')->where('identificacion',$logFile->dato1)->first()
                                //                     ?User::role('Distribuidor')->where('identificacion',$logFile->dato1)->first()->id
                                //                     :User::role('Distribuidor')->where('name','No aplica')->first()->id)
                                'usuario_dist' => $logFile->dato1,

                            ];

                            $upCuentaMadre =  CuentaMadre::where('email', $logFile->dato2)->first();
                            if ($upCuentaMadre) {
                                $saveM =  $upCuentaMadre->update($dataUpdatedCM);
                            }
                        }
                    }
                    $cantRegSaved++;
                }
            }

            if ($cantRegSaved > 0) {
                return response()->json(['msg' => 'La operación se realizó con éxito', 'cantidadReg' => $cantRegSaved]);
            } else {
                return response()->json(['msg' => 'No fue posible insertar los registros'], 501);
            }
        } catch (Exception $e) {
            //throw $th;
            return response()->json(['msg' => 'Error al insertar los registros. ' . $e->getMessage()], 500);
        }
    }
    //funciones utilitarias
    public function verifyCuentaMadre($dato)
    {
        try {
            //code...
            if ($dato == '') {
                return '';
            } else {
                /*
                  Verificamos si la cuenta asociada
                  es una cuenta madre
                */
                $verify =  CuentaMadre::where('email', $dato)->where('cta_ppal', 1)->count();
                if ($verify > 0) {
                    return true;
                } else {
                    return false;
                }
            }
        } catch (Exception $e) {
            throw  new Exception($e->getMessage());
        }
    }

    public function verifyDuplicatedEmail($dato)
    {
        try {
            /*
                Verificamos si la cuenta asociada
                es una cuenta madre
            */
            $verify =  CuentaMadre::where('email', $dato)->count();
            if ($verify > 0) {
                return false;
            } else {
                return true;
            }
        } catch (Exception $e) {
            throw  new Exception($e->getMessage());
        }
    }

    public function getIdUser($dato)
    {
        try {
            //code...

            if ($dato == '' || $dato == null) {
                return null;
            }
            $user = User::where('identificacion', $dato)->first();
            if ($user) {
                return $user->id;
            } else {
                return null;
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}
