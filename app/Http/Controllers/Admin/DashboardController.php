<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cuenta;
use App\Models\Estado;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use stdClass;

class DashboardController extends Controller
{
    //
    use AuthorizesRequests;

    public function __construct()
    {
        $this->middleware('can:admin.dashboard')->only('index');
    }

    public function index() {


        $usuarioLogeado= Auth::user()->id;

        $authUser = auth()->user();
        $userAdmin = $authUser->hasRole('Administrator')?true:false;

        //ejecutat sp
        //$results = DB::select('CALL actualizar_tiempos()');

        $result=[];

        if($userAdmin){
            //Indicadores
            $statuses = [
                'Disponibles',
                'Deshabilitadas',
                'Recuperadas',
                'Exoneradas',
                'Asignadas',
                'Vencidas'
            ];
            foreach ($statuses as $status) {
                // Contar usuarios por cada estado y obtener detalles adicionales
                $users = Cuenta::whereHas('estado', function($query) use($status){
                                             $query->where('estado',$status);
                                    })->select('estado_id', DB::raw('count(*) as total'))
                                    ->groupBy('estado_id')
                                    ->get();
                if($users->count()>0){
                    foreach ($users as $value) {
                        $statusDetail = new \stdClass();
                        $statusDetail->cantidad = $value->total??0;
                        $statusDetail->estado = $value->estado->estado;
                        $statusDetail->color = $value->estado->color??'light';
                        array_push($result, $statusDetail);
                    }
                }else{
                    $estado =  Estado::where('estado', $status)->first();

                    $statusDetail = new \stdClass();
                    $statusDetail->cantidad = $users->count()??0;
                    $statusDetail->estado =$status;
                    $statusDetail->color = $estado?$estado->color:'light';
                   array_push($result, $statusDetail);
                }
            }
        }else{
            //Indicadores
            $statusesDist = [
                'Asignadas',
                'Vencidas'
            ];
            foreach ($statusesDist as $status) {

                // Contar usuarios por cada estado y obtener detalles adicionales
                $users = Cuenta::with('estado')
                                ->whereHas('estado', function($query) use($status){
                                            $query->where('estado',$status);
                                })
                                ->where('usuario_dist',$usuarioLogeado)
                                ->select('estado_id', DB::raw('count(*) as total'))
                                ->groupBy('estado_id')
                                ->get();

                //dd($status);
                //dd($users);
                if($users->count()>0){
                    foreach ($users as $value) {
                        $statusDetail = new \stdClass();
                        $statusDetail->cantidad = $value->total??0;
                        $statusDetail->estado = $value->estado->estado;
                        $statusDetail->color = $value->estado->color??'light';
                        array_push($result, $statusDetail);
                    }
                }else{
                    $estado =  Estado::where('estado', $status)->first();

                    $statusDetail = new \stdClass();
                    $statusDetail->cantidad = $users->count()??0;
                    $statusDetail->estado =$status;
                    $statusDetail->color = $estado?$estado->color:'light';
                   array_push($result, $statusDetail);
                }
            }
        }

        //Resumen de Cuentas
        if($userAdmin){
            $cuentas = Cuenta::with('usuario','estado')
                                ->get();
        }else{
            $cuentas = Cuenta::with('usuario','estado')
                                ->where('usuario_dist',$usuarioLogeado)
                                ->whereHas('estado', function($query){
                                        $query->where('estado','Asignadas')->orWhere('estado','Vencidas');
                                    })
                                ->get();
        }

        return view('dashboard',[
            'resultados'=>collect($result)->sortByDesc('cantidad')->values()->all(),
            'resCuentas'=>$cuentas,
            'userAdmin'=>$userAdmin,
        ]);
    }
}
