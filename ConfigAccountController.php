<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\CuentaMadre;
use Exception;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ConfigAccountController extends Controller
{
    //

    use AuthorizesRequests;

    public function __construct()
    {
        $this->middleware('can:configEmail.index')->only('index');
    }

    public function index(Request $request){
        try {

            $query =  CuentaMadre::with('account')->get();
            // dd($query[0]->account);
            if($request->ajax()) {
                return DataTables::of($query)
                                ->addIndexColumn()
                                ->addColumn('correo', function ($td) {

                                    $href = $td->email;

                                    return $href;

                                })
                                ->addColumn('token', function ($td) {

                                if($td->account){
                                    $href = $td->account->oauth_token;
                                }else{
                                    $href = '';
                                }

                                return $href;

                                })
                                ->addColumn('expiracion', function ($td) {

                                    $tokenExp = $td->account;
                                    if($tokenExp){
                                        $href =  date('Y-m-d h:i:s A', strtotime($td->account->token_expires_at));
                                    }else{
                                        $href='';
                                    }

                                    return $href;

                                })
                                ->addColumn('acciones', function ($td) {
                                    // if(Auth::user()->can('cuentappal.edit')){
                                        if($td->account){
                                            if($td->account->oauth_token){
                                                $href = '<button type="button" onclick="configToken('."'".$td->email."'".')" class="btn btn-info btn-circle btn-sm" data-toggle="tooltip" data-placement="top" title="Configurar el Token del correo"><i class="fas fa-cog"></i></button>&nbsp';
                                                $href .= '<button type="button" onclick="consultarCorreos(' . $td->id . ')" class="btn btn-success btn-circle btn-sm" data-toggle="tooltip" data-placement="top" title="Consultar Correos"><i class="fas fa-envelope"></i></button>';
                                            }else{
                                                $href = '<button type="button" onclick="configToken('."'".$td->email."'".')" class="btn btn-info btn-circle btn-sm" data-toggle="tooltip" data-placement="top" title="Configurar el Token del correo"><i class="fas fa-cog"></i></button>&nbsp';
                                            }
                                        }else{
                                            $href = '<button type="button" onclick="configToken('."'".$td->email."'".')" class="btn btn-info btn-circle btn-sm" data-toggle="tooltip" data-placement="top" title="Configurar el Token del correo"><i class="fas fa-cog"></i></button>&nbsp';
                                        }
                                    // }else{
                                        // $href='';
                                    // }
                                    // $href .= '<button type="button" class="btn btn-danger btn-circle btn-sm" data-toggle="tooltip" data-placement="top" title="Quitar Usuario"><i class="fas fa-trash"></i></button>';

                                return $href;

                                })
                                ->rawColumns(['correo','token','expiracion','acciones'])
                                ->make(true);

            }
            return view('admin.configEmails.index');

         }
         catch (Exception $e) {

             return response()->json(['error' => 'Error al obtener los estados ' . $e->getMessage()], 500);
         }
    }
}
