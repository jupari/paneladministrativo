<?php

namespace App\Http\Controllers\Contratos;

use App\Http\Controllers\Controller;
use App\Models\Cargo;
use App\Models\Empleado;
use App\Models\Plantilla;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class ContratoController extends Controller
{
    //
    public function index(Request $request)
    {
        try {
            $empleados = Empleado::with('cargo')->orderBy('nombres', 'asc')->get();

            if ($request->ajax()) {
                return DataTables::of($empleados)
                    ->addIndexColumn()
                    ->addColumn('id', function ($empleado) {
                        return $empleado->id;
                    })
                    ->addColumn('nombres_completos', function ($empleado) {
                        return $empleado->nombres . ' ' . $empleado->apellidos;
                    })
                    ->addColumn('identificacion', function ($empleado) {
                        return $empleado->identificacion;
                    })
                    ->addColumn('expedida_en', function ($empleado) {
                        return $empleado->expedida_en;
                    })
                    ->addColumn('fecha_nacimiento', function ($empleado) {
                        return $empleado->fecha_nacimiento?$empleado->fecha_nacimiento->format('d-m-Y') : 'N/A';
                    })
                    ->addColumn('fecha_inicio_labor', function ($empleado) {
                        return $empleado->fecha_inicio_labor ? $empleado->fecha_inicio_labor->format('d-m-Y') : 'N/A';
                    })
                    ->addColumn('direccion', function ($empleado) {
                        return $empleado->direccion;
                    })
                    ->addColumn('cargo', function ($empleado) {
                        return $empleado->cargo ? $empleado->cargo->nombre : 'Sin cargo';
                    })
                    ->addColumn('active', function ($cargo) {
                        return $cargo->active == 1
                            ? '<span class="badge bg-success">Activo</span>'
                            : '<span class="badge bg-danger">Inactivo</span>';
                    })
                    ->addColumn('created_at', function ($cargo) {

                        $fecha=$cargo->created_at ? $cargo->created_at->format('d-m-Y H:i:s') : 'N/A';
                        return $fecha;
                    })
                    ->addColumn('acciones', function ($empleado) {
                        $acciones = ' <button type="button" onclick="upGenerarContrato(' . $empleado->id . ')" class="btn btn-success btn-sm" data-toggle="tooltip" title="Generar contrato"><i class="fas fa-file"></i></button>';
                        return $acciones;
                    })
                    ->rawColumns(['id','identificacion','nombres_completos','expedida_en','fecha_nacimiento','fecha_inicio_labor','direccion','cargo','active','created_at','acciones'])
                    ->make(true);
            }
            $cargos = Cargo::where('active',1)->orderBy('nombre')->get();
            $authUser= auth()->user();
            $plantillas =  Plantilla::where('active',1)->orderBy('plantilla')->get();
            return view('contratos.index',[
                'cargos'=>$cargos,
                'user_id'=>$authUser->id,
                'plantillas'=>$plantillas,
            ]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
}
