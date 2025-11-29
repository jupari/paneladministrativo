<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Cuenta;
use App\Models\CuentaMadre;
use App\Models\Estado;
use App\Models\Logs_files;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\Datatables\Datatables;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Str;

class CuentaMadreController extends Controller
{
    //
    use AuthorizesRequests;

    public function __construct()
    {
        $this->middleware('can:cuentappal.index')->only('index');
        $this->middleware('can:cuentappal.create')->only('create','store');
        $this->middleware('can:cuentappal.edit')->only('edit','update');
    }


    public function index(Request $request)
    {
       // // Campos para tablas siempre y cuando sean iguales
       $campos = ['id', 'nombre','email', 'password', 'cm_asociada','usuario_dist','cta_ppal'];

        try {

           $cuentaPpal = CuentaMadre::with('user')->select($campos)
                            ->orderByDesc('id')->get();
           $usuarioAbonados=User::role('Distribuidor')->orderBy('name')->get();
           //dd($usuarioAbonados);
           if($request->ajax()) {
               return Datatables::of($cuentaPpal)
                               ->addIndexColumn()
                               ->addColumn('id', function ($td) {

                                $href = $td->id;
                                return $href;

                                })
                               ->addColumn('nombre', function ($td) {

                                   $href = $td->nombre;
                                   return $href;

                               })
                               ->addColumn('correo', function ($td) {

                                   $href = $td->email;

                                   return $href;

                               })
                               ->addColumn('password', function ($td) {
                                if(Auth::user()->can('see.password')){
                                    try {
                                        $href = Crypt::decryptString($td->password);
                                    } catch (DecryptException $e) {
                                        $href = $e->getMessage();
                                    }
                                }else{
                                    $href='No permitido';
                                }
                                return $href;

                                })
                                ->addColumn('cm_asociada', function ($td) {
                                    $href = $td->cm_asociada?$td->cm_asociada:'N/A';

                                   return $href;

                                })
                                ->addColumn('usuario_dist', function ($td) {

                                    $href = $td->user?$td->user->name:'Sin Usuario';

                                    return $href;

                                })
                                ->addColumn('cta_ppal', function ($td) {

                                    $href = $td->cta_ppal==0?'No':'Si';

                                    return $href;

                                })
                                ->addColumn('acciones', function ($td) {
                                   if(Auth::user()->can('cuentappal.edit')){
                                       $href = '<button type="button" onclick="upCuentaPpal('.$td->id.')" class="btn btn-secondary btn-circle btn-sm" data-toggle="tooltip" data-placement="top" title="Editar Cuenta Principal"><i class="fas fa-pencil-alt"></i></button>&nbsp';
                                   }else{
                                       $href='';
                                   }
                                   // $href .= '<button type="button" class="btn btn-danger btn-circle btn-sm" data-toggle="tooltip" data-placement="top" title="Quitar Usuario"><i class="fas fa-trash"></i></button>';

                               return $href;

                               })
                               ->rawColumns(['id','nombre', 'correo','password','cm_asociada','usuario_dist','cta_ppal','acciones'])
                               ->make(true);

           }
           return view('admin.cuentappal.index',['abonados'=>$usuarioAbonados,'cuentasMadres'=>$cuentaPpal]);

        }
        catch (Exception $e) {

            return response()->json(['error' => 'Error al obtener las Cuentas Principales ' . $e->getMessage()], 500);
        }

    }

    public function store(Request $request){

        DB::beginTransaction(); // Inicia la transacción

        try {

            $validationData= Validator::make($request->all(), [
                'nombre'=>'required|unique:App\Models\CuentaMadre,email',
                'email'=>'required|email:rfc',
                'password'=>'required'
            ]);

            if($validationData->fails()){
                return response()->json(['errors' => $validationData->errors()],422);
            }

            $cuentaPpal = CuentaMadre::create([
                'nombre'=>$request->nombre,
                'email'=>$request->email,
                'password'=>Crypt::encryptString($request->password),
                'cm_asociada'=>$request->cm_asociada,
                'usuario_dist'=>$request->usuario_reg,
                'cta_ppal'=>$request->cta_ppal
            ]);

            if($cuentaPpal){
                $exitoAccount='';
                $account =  Account::where('email',$request->email)->count();
                if($account>0){
                    return response()->json(['message'=>'La Cuenta Principal se creó con éxito, pero ya existe la cuenta en Accounts'],200);
                }else{
                    $accountStore= Account::create([
                        'email'=>$request->email,
                        'clientId'=>$request->clientId,
                        'tenant_id'=>$request->tenant_id,
                        'clientSecret'=>$request->clientSecret,
                        'redirectUri' =>config('app.redirectUri'),
                        'urlAuthorize'=>config('app.urlAuthorize'),
                        'urlAccessToken'=>config('app.urlAccessToken'),
                    ]);

                    if($accountStore){
                        //-->se creá la cuenta para ser asignada al distribuidor--
                        $respuesta =  $this->storeCuenta($request->email);
                        DB::commit();
                        return response()->json(['message'=>'La Cuenta Principal se creó con éxito'],200);
                    }else{
                        return response()->json(['message'=>'La Cuenta Principal se creó con éxito, pero el registro quedo incompleto revisa Accounts'],200);
                    }
                }

            }else{
                return response()->json(['message'=>'No es posible crear la Cuenta principal'],502);
            }
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['errors' => 'No es posible guardar la Cuenta Principal ' . $e->getMessage()], 500);
        }
    }

    public function edit(Request $request, $id)
    {
        try{

            $cuentaPpal = CuentaMadre::where('id',$id)
                                        ->first();
            // if(!$cuentaPpal){
            //     return response()->json(['error' => 'Esta cuenta no se puede vincular a otra por que es una cuenta principal.'], 500);
            // }

            $cuenta = Account::where('email',$cuentaPpal->email)->first();

            if($cuentaPpal){

                return response()->json([
                    'cuentappal' => [
                         'id'=>$cuentaPpal->id
                        ,'nombre'=>$cuentaPpal->nombre
                        ,'email'=>$cuentaPpal->email
                        ,'password'=>Crypt::decryptString($cuentaPpal->password)
                        ,'usuario_dist'=>$cuentaPpal->usuario_dist?$cuentaPpal->usuario_dist:'Sin Usuario'
                        ,'clientId'=>$cuenta->clientId
                        ,'tenant_id'=>$cuenta->tenant_id
                        ,'clientSecret'=>$cuenta->clientSecret
                        ,'cm_asociada'=>$cuentaPpal->cm_asociada?$cuentaPpal->cm_asociada:'N/A'
                        ,'cta_ppal'=>$cuentaPpal->cta_ppal
                    ],
                    'message' => 'Lista de las cuentas se obtuvo con éxito.'
                ], 200);

            }

        }
        catch (Exception $e) {

            return response()->json(['error' => 'Error al obtener las cuentas principales ' . $e->getMessage()], 500);
        }


    }

    public function update(Request $request, $id)
    {
        try {

            $validationData= Validator::make($request->all(), [
                'nombre'=>'required',
                'email'=>'required|email:rfc',
                'password'=>'required'
            ]);

            if($validationData->fails()){
                return response()->json(['errors' => $validationData->errors()],422);
            }
            $cuentaPpal = CuentaMadre::findOrFail($id);

            $data = [
                'nombre'=>$request->nombre,
                'email'=>$request->email,
                'password'=>Crypt::encryptString($request->password),
                'cm_asociada'=>$request->cm_asociada,
                'usuario_dist'=>$request->usuario_reg?$request->usuario_reg:$cuentaPpal->usuario_dist,
                'cta_ppal'=>$request->cta_ppal?$request->cta_ppal:$cuentaPpal->cta_ppal,
            ];

            $update = $cuentaPpal->update($data);

            if($update){
                //Se asigna el distribuidor y se cambia al estado de asignada
                $cuenta = Cuenta::where('nombre_cuenta',$request->email)->first();
                if($cuenta){
                    $cuenta->usuario_dist = $request->usuario_reg?$request->usuario_reg:$cuentaPpal->usuario_dist;
                    $cuenta->fecha_asig = Carbon::now();
                    $cuenta->estado_id = Estado::where('estado', 'Asignadas')->first()->id;
                    $cuenta->save();
                }



                $upAccount= Account::where('email',$request->email)->first();
                if($upAccount){
                    $upAccount->clientId=$request->clientId;
                    $upAccount->tenant_id=$request->tenant_id;
                    $upAccount->clientSecret=$request->clientSecret;
                    $upAccount->save();
                }

                // Aactualizar
                return response()->json(['message' => 'La Cuenta Principal se modificó con éxito'], 200);

            }

        }catch (Exception $e) {

            // Manejar la excepción aquí
            return response()->json(['error' => 'Error al actualizar la Cuenta Principal: ' . $e->getMessage()], 500);
        }
    }

    //type: post
    //route: excel/import post : excel
    public function verifyExcelData(Request $request){
        try {
            $file = $request->file('excel_file');
            if(!$file) {
                return response()->json(['error' => 'No file provided'], 400);
            }

            $spreadsheet = IOFactory::load($file->getPathname());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();
            $lote = Str::uuid();
            $dataQuantity = count($rows) - 1;
            $rowsToInsert = [];

            $emailPattern = '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/';

            $totalRows = count($rows);

            $rules = [
                'dato1'=>'required|email',
                'dato2'=>'required',
                'dato3'=>'required',
            ];

            for ($i = 1; $i < $totalRows; $i++) {
                $data = $rows[$i];



                if(empty($data[1])) {
                    continue;
                }

                $rowsToInsert[]=[
                    'dato1'=>!preg_match($emailPattern, $data[0])?'Correo no valido '.'( '.$data[0].' )': (!$this->verifyDuplicatedEmail($data[0])?$data[0]:'Correo ya existe '.'( '.$data[0].' )'),
                    'dato2'=>Crypt::encryptString($data[1]), //password
                    'dato3'=>$data[2]??'',//codigo: puede ir en blanco
                    'dato4'=>$data[3]==''?'':($this->verifyCuentaMadre($data[3])?$data[3]:'No es una cuenta principal'), //cuenta asociada
                    'dato5'=>$data[4]?$this->getIdUser($data[4]):'Usuario distribuidor no existe', //cuenta asociada
                    'dato6'=>$data[5]?:'',//usuario dist
                    'dato7'=>$data[6]??'',
                    'dato8'=>$data[7]??'',
                    'dato9'=>$data[8]??0,
                    'lote' =>$lote
                ];

            }

            if (!empty($rowsToInsert)) {
                Logs_files::insert($rowsToInsert);
            }

            $logsRows = Logs_files::where('lote', $lote)->get();
            $logsFilesQuantity = $logsRows->count();

            return response()->json(['cantExcelFiles' => $dataQuantity, 'cantFilesFound' => $logsFilesQuantity, 'rows'=>$logsRows, 'lote'=>$lote]);

        } catch (Exception $e) {
            return response()->json(['error' => 'El archivo no es valido.'. $e->getMessage()], 500);
        }
    }

    //type: post
    //route: excel/insertarCuentasMadre post : excel
    public function insertarCuentasMadre(Request $request){

        if(!isset($request->lote)){
             return response()->json(['msg'=>'Debe enviar un número de lote valido.']);
         }
         try {
            DB::beginTransaction(); // Inicia la transacción

            $logsFiles = Logs_files::where('lote', $request->lote)->get();
            $cantRegSaved=0;
            $cantRegLote=$logsFiles->count();

            $emailPattern = '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/';

            foreach ($logsFiles as  $logFile) {
                $cant =  CuentaMadre::where('email', $logFile->dato1)->count();
                if($cant==0){
                    if(preg_match($emailPattern, $logFile->dato1)){
                        $save =  CuentaMadre::create([
                            'email'       =>$logFile->dato1,//columna 1 email
                            'password'    =>$logFile->dato2??'',//columna 2 password
                            'nombre'      =>$logFile->dato3??'',//columna 3  nombre
                            'cm_asociada' =>$logFile->dato4?$logFile->dato4:'',//columna 4 cuenta madre
                            'usuario_dist'=>$logFile->dato5??null, //columna 5 usuario distribuidor
                            'cta_ppal'    =>$logFile->dato9?$logFile->dato9:null,//columna 9 marcacion como cta madre
                        ]);

                        ///creacion de la tabla Account
                        $accountStore= Account::create([
                            'email'         =>$logFile->dato1,//columna 1 email
                            'clientId'      =>$logFile->dato6?$logFile->dato6:'', //columna 6 clienteId
                            'tenant_id'     =>$logFile->dato7?$logFile->dato7:'',//columna 7 tenant_Id
                            'clientSecret'  =>$logFile->dato8?$logFile->dato8:'',//columna 8 clientSecret
                            'redirectUri'   =>config('app.redirectUri'),
                            'urlAuthorize'  =>config('app.urlAuthorize'),
                            'urlAccessToken'=>config('app.urlAccessToken'),
                        ]);

                        if($accountStore){
                            //creacion de la tabla cuenta
                            //-->se creá la cuenta para ser asignada al distribuidor--
                            $respuesta =  $this->storeCuenta($logFile->dato1, $logFile->dato5);
                            DB::commit();
                        }

                        $cantRegSaved++;
                    }


                }
            }

            if($cantRegSaved>0){
                return response()->json(['msg'=>'Los datos se insertarón con éxito','cantidadReg'=>$cantRegSaved]);
            }else{
                return response()->json(['msg'=>'No fue posible insertar los registros'], 501);
            }

         } catch (Exception $e) {
             //throw $th;
             return response()->json(['msg'=>'Error al insertar los registros. '. $e->getMessage()],500);

         }
    }

    public function storeCuenta($cuentaMadre, $usuarioDist){

        try {

            $cuentaM= CuentaMadre::where('email', $cuentaMadre)->first();

            if(!isset($cuentaM)){
                throw new Exception('La cuenta ppal no existe.');
            }

            $name =  config('app.usuario_default');
            $estadoDefault = config('app.estado_default');
            $usuario=User::where('name',$name)->first();

            if(!isset($usuario)){
                throw new Exception('El usuario no existe. '. $name);
            }

            $cuentaPpal = Cuenta::create([
                'user_id'=>$usuario?$usuario->id:0,
                'usuario_dist'=>$cuentaM->usuario_dist,
                'estado_id'=>$usuarioDist?$estadoDefault:2,
                'nombre_cuenta'=>$cuentaM->email,
                // 'fecha_asig'=>Carbon::now(),
                'password_cuenta'=>''
            ]);

            if($cuentaPpal){
                return ['Ok'=>true];
            }else{
                throw new Exception('No fue posible guardar la cuenta. ');
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }


    public function updateCuenta($request, $id)
    {
        try {

            $validation =  Validator::make($request->all(),[
                'nombre_cuenta'=>'required',
                'password_cuenta'=>'required',
            ]);

            if($validation->fails()){
                return['errors'=>$validation->errors()];
            }

            $cuenta = Cuenta::findOrFail($id);

            $data = [
                'user_id'=>$request->user_id,
                'estado_id'=>$request->estado_id,
                'nombre_cuenta'=>$request->nombre_cuenta,
                'fecha_asig'=>$request->fecha_asig,
                'password_cuenta'=>$request->password_cuenta

            ];

            $update = $cuenta->update($data);

            if($update){

                // Aactualizar
                return ['message' => 'La Cuenta se modificó con éxito'];

            }

        }catch (Exception $e) {

            // Manejar la excepción aquí
            return ['error' => 'Error al actualizar la Cuenta Principal: ' . $e->getMessage()];
        }
    }

    //Busca cuenta madre por correo electronico
    //type: get
    //route
    public function getDataEmail($email){
        try {
            //code...
            $data = CuentaMadre::with('Account')->where('email',$email)->first();
            if($data){
                return response()->json([
                    'Ok'=>true,
                    'data'=>$data
                ]);
            }else{
                return response()->json([
                    'Ok'=>false,
                    'data'=>[]
                ]);
            }
        } catch (Exception $e) {
            //throw $th;
            return response()->json([
                'errors'=>$e->getMessage(),
            ]);
        }
    }

    //funciones utilitarias
    public function getIdUser($dato){
        try{
            //code...
            $user = User::where('identificacion', $dato)->first();
            if($user){
                return $user->id;
            }else{
                return null;
            }

        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function verifyCuentaMadre($dato){
        try {
            //code...
            if($dato==''){
                return '';
            }else{
                /*
                  Verificamos si la cuenta asociada
                  es una cuenta madre
                */
                $verify =  CuentaMadre::where('email', $dato)->where('cta_ppal',1)->count();
                if($verify>0){
                    return true;
                }else{
                    return false;
                }
            }
        } catch (Exception $e) {
            throw  new Exception($e->getMessage());
        }
    }

    public function verifyDuplicatedEmail($dato){
        try {
            /*
                Verificamos si la cuenta asociada
                es una cuenta madre
            */
            $verify =  CuentaMadre::where('email', $dato)->count();
            if($verify>0){
                return true;
            }else{
                return false;
            }

        } catch (Exception $e) {
            throw  new Exception($e->getMessage());
        }
    }
}
