<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Estado;
use Exception;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class EstadoController extends Controller
{
    use AuthorizesRequests;

    public function __construct()
    {
        $this->middleware('can:estados.index')->only('index');
        $this->middleware('can:estados.create')->only('create','store');
        $this->middleware('can:estados.edit')->only('edit','update');
    }


    public function index(Request $request)
    {
       // // Campos para tablas siempre y cuando sean iguales
       $campos = ['id','estado','color','descripcion'];

        try {

           $estados = Estado::select($campos)
                            ->get();

            if($request->ajax()) {
               return DataTables::of($estados)
                               ->addIndexColumn()
                               ->addColumn('estado', function ($td) {

                                   $href = $td->estado;
                                   return $href;

                               })
                               ->addColumn('descripcion', function ($td) {

                                $href = $td->descripcion;
                                return $href;

                                })
                               ->addColumn('color', function ($td) {
                                    $href = '<span class="badge badge-'. $td->color.'">'.$td->color.'</span>';
                                    return $href;

                                })
                               ->addColumn('acciones', function ($td) {
                                   if(Auth::user()->can('estados.edit')){
                                       $href = '<button type="button" onclick="upEstado('.$td->id.')" class="btn btn-warning btn-circle btn-sm" data-toggle="tooltip" data-placement="top" title="Editar estado"><i class="fas fa-pencil-alt"></i></button>&nbsp';
                                   }else{
                                       $href='';
                                   }
                                   // $href .= '<button type="button" class="btn btn-danger btn-circle btn-sm" data-toggle="tooltip" data-placement="top" title="Quitar Usuario"><i class="fas fa-trash"></i></button>';

                               return $href;

                               })
                               ->rawColumns(['estado','descripcion','color','acciones'])
                               ->make(true);

           }
           return view('admin.estados.index');

        }
        catch (Exception $e) {

            return response()->json(['error' => 'Error al obtener los estados ' . $e->getMessage()], 500);
        }

    }

    public function store(Request $request){

        try {
            //code...

            $validation =  Validator::make($request->all(),[
                'estado'=>'required|unique:App\Models\Estado,estado',
                'color'=>'required|in:primary,success,info,secondary,dark,light,warning,danger'
            ]);

            if($validation->fails()){
                return response()->json(['errors'=>$validation->errors()], 422);
            }

            $estado = Estado::create([
               'estado'=>$request->estado,
               'color'=>$request->color,
               'descripcion'=>$request->descripcion,
            ]);

            if($estado){
                return response()->json(['message'=>'El estado se creó con éxito'],200);
            }else{
                return response()->json(['message'=>'No es posible crear el estado'],502);
            }
        } catch (Exception $e) {
            return response()->json(['error' => 'No es posible guardar el estado ' . $e->getMessage()], 500);
        }
    }

    public function edit(Request $request, $id)
    {
        try{
            $campos = ['id','estado','color','descripcion'];
            $estado = estado::select($campos)->findOrFail($id);

            if($estado){

                return response()->json([
                    'estado' => $estado,
                    'message' => 'El estado se obtuvo con éxito.'
                ], 200);

            }

        }
        catch (Exception $e) {

            return response()->json(['error' => 'Error al obtener el estado ' . $e->getMessage()], 500);
        }


    }

    public function update(Request $request, $id)
    {
        try {
            $validation =  Validator::make($request->all(),[
                'estado'=>'required',
                'color'=>'required|in:primary,success,info,secondary,dark,light,warning,danger'
            ]);

            if($validation->fails()){
                return response()->json(['errors'=>$validation->errors()], 422);
            }

            $estado = Estado::where('id',$id)->first();

            $data = [
                'estado'=>$request->estado,
                'color'=>$request->color,
                'descripcion'=>$request->descripcion,

            ];

            $update = $estado->update($data);

            if($update){

                // Aactualizar
                return response()->json(['message' => 'El estado se modificó con éxito'], 200);

            }

        }catch (Exception $e) {

            // Manejar la excepción aquí
            return response()->json(['error' => 'Error al actualizar el estado: ' . $e->getMessage()], 500);
        }
    }
}
