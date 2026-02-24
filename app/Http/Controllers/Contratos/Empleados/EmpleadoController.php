<?php

namespace App\Http\Controllers\Contratos\Empleados;

use App\Http\Controllers\Controller;
use App\Http\Requests\EmpleadoRequest;
use App\Models\Cargo;
use App\Models\Ciudad;
use App\Models\Empleado;
use App\Models\Pais;
use App\Models\Tercero;
use App\Models\TipoContrato;
use App\Models\TipoIdentificacion;
use App\Services\EmpleadoService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class EmpleadoController extends Controller
{

    protected $empleadoService;

    public function __construct(EmpleadoService $empleadoService)
    {
        $this->empleadoService = $empleadoService;
    }

    /**
     * Listar empleados.
     */
    public function index(Request $request)
    {
        try {
            $empleados = $this->empleadoService->obtenerEmpleados();

            if ($request->ajax()) {
                return DataTables::of($empleados)
                    ->addIndexColumn()
                    ->addColumn('nombres_completos', fn($empleado) => $empleado->nombres . ' ' . $empleado->apellidos)
                    ->addColumn('fecha_nacimiento', fn($empleado) => optional($empleado->fecha_nacimiento)->format('d-m-Y') ?? 'N/A')
                    ->addColumn('fecha_inicio_labor', fn($empleado) => optional($empleado->fecha_inicio_labor)->format('d-m-Y') ?? 'N/A')
                    ->addColumn('cargo', fn($empleado) => optional($empleado->cargo)->nombre ?? 'Sin cargo')
                    ->addColumn('active', fn($empleado) => $empleado->active ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-danger">Inactivo</span>')
                    ->addColumn('created_at', fn($empleado) => optional($empleado->created_at)->format('d-m-Y H:i:s') ?? 'N/A')
                    ->addColumn('acciones', fn($empleado) => '<button type="button" onclick="upEmpleado(' . $empleado->id . ')" class="btn btn-warning btn-sm" title="Editar"><i class="fas fa-edit"></i></button>')
                    ->rawColumns(['active', 'acciones'])
                    ->make(true);
            }

            $tiposContratos = TipoContrato::where('active',1)->get();
            $clientes =  Tercero::where('active', 1)->orderBy('nombres')->get();
            $tiposIdentificacion= TipoIdentificacion::where('active',1)->orderBy('nombre')->get();
            $paises = Pais::with('departamentos.ciudades')->get();
            $ciudades=Ciudad::where('active',1)->orderBy('nombre')->get();
            return view('contratos.empleados.index', [
                'cargos' => Cargo::where('active', 1)->orderBy('nombre')->get(),
                'tiposContratos'=>$tiposContratos,
                'clientes'=>$clientes,
                'user_id' => auth()->id(),
                'tiposIdentificaciones'=> $tiposIdentificacion,
                'paises'=> $paises,
                'ciudades'=>$ciudades,
            ]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Almacena un nuevo empleado.
     */
    public function store(EmpleadoRequest $request)
    {
        try {
            $empleado = $this->empleadoService->crearEmpleado($request->validated());
            return response()->json(['success' => true, 'message' => 'Empleado creado exitosamente.', 'data' => $empleado], 201);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Muestra los detalles de un empleado específico.
     */
    public function edit($id)
    {
        try {
            $empleado = $this->empleadoService->obtenerEmpleadoPorId($id);
            return response()->json(['success' => true, 'data' => $empleado]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Actualiza un empleado existente.
     */
    public function update(EmpleadoRequest $request, $id)
    {
        try {
            $empleado = $this->empleadoService->actualizarEmpleado($id, $request->validated());
            return response()->json(['success' => true, 'message' => 'Empleado actualizado exitosamente.', 'data' => $empleado]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Elimina un empleado.
     */
    public function destroy($id)
    {
        try {
            $this->empleadoService->eliminarEmpleado($id);
            return response()->json(['success' => true, 'message' => 'Empleado eliminado exitosamente.']);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function list()
    {
        $companyId = (int) session('company_id');

        $items = Empleado::query()
            ->where('company_id', $companyId)
            ->where('active', 1)
            ->orderBy('nombres')
            ->get(['id','nombres','apellidos','identificacion'])
            ->map(fn($e)=>[
                'id'=>$e->id,
                'text'=> trim($e->nombres.' '.$e->apellidos).' - '.$e->identificacion
            ])->values();

        return response()->json(['data'=>$items]);
    }


    //
    // public function index(Request $request)
    // {
    //     try {
    //         $empleados = Empleado::with('cargo')->orderBy('nombres', 'asc')->get();

    //         if ($request->ajax()) {
    //             return DataTables::of($empleados)
    //                 ->addIndexColumn()
    //                 ->addColumn('id', function ($empleado) {
    //                     return $empleado->id;
    //                 })
    //                 ->addColumn('nombres_completos', function ($empleado) {
    //                     return $empleado->nombres . ' ' . $empleado->apellidos;
    //                 })
    //                 ->addColumn('identificacion', function ($empleado) {
    //                     return $empleado->identificacion;
    //                 })
    //                 ->addColumn('expedida_en', function ($empleado) {
    //                     return $empleado->expedida_en;
    //                 })
    //                 ->addColumn('fecha_nacimiento', function ($empleado) {
    //                     return $empleado->fecha_nacimiento?$empleado->fecha_nacimiento->format('d-m-Y') : 'N/A';
    //                 })
    //                 ->addColumn('fecha_inicio_labor', function ($empleado) {
    //                     return $empleado->fecha_inicio_labor ? $empleado->fecha_inicio_labor->format('d-m-Y') : 'N/A';
    //                 })
    //                 ->addColumn('direccion', function ($empleado) {
    //                     return $empleado->direccion;
    //                 })
    //                 ->addColumn('cargo', function ($empleado) {
    //                     return $empleado->cargo ? $empleado->cargo->nombre : 'Sin cargo';
    //                 })
    //                 ->addColumn('active', function ($cargo) {
    //                     return $cargo->active == 1
    //                         ? '<span class="badge bg-success">Activo</span>'
    //                         : '<span class="badge bg-danger">Inactivo</span>';
    //                 })
    //                 ->addColumn('created_at', function ($cargo) {

    //                     $fecha=$cargo->created_at ? $cargo->created_at->format('d-m-Y H:i:s') : 'N/A';
    //                     return $fecha;
    //                 })
    //                 ->addColumn('acciones', function ($empleado) {
    //                     $acciones = '<button type="button" onclick="upEmpleado(' . $empleado->id . ')" class="btn btn-warning btn-sm" data-toggle="tooltip" title="Editar"><i class="fas fa-edit"></i></button>';
    //                     //$acciones .= ' <button type="button" onclick="deleteEmpleado(' . $empleado->id . ')" class="btn btn-danger btn-sm" data-toggle="tooltip" title="Eliminar"><i class="fas fa-trash"></i></button>';
    //                     return $acciones;
    //                 })
    //                 ->rawColumns(['id','identificacion','nombres_completos','expedida_en','fecha_nacimiento','fecha_inicio_labor','direccion','cargo','active','created_at','acciones'])
    //                 ->make(true);
    //         }
    //         $cargos = Cargo::where('active',1)->orderBy('nombre')->get();
    //         $authUser= auth()->user();
    //         return view('contratos.empleados.index',[
    //             'cargos'=>$cargos,
    //             'user_id'=>$authUser->id,
    //         ]);
    //     } catch (Exception $e) {
    //         return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
    //     }
    // }

    // /**
    //  * Almacena un nuevo empleado.
    //  */
    // public function store(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'nombres' => 'required|string|max:255',
    //         'apellidos' => 'required|string|max:255',
    //         'identificacion' => 'required|string|max:20|unique:empleados,identificacion|regex:/^\d{1,20}$/',
    //         'expedida_en' => 'required|string|max:255',
    //         'fecha_nacimiento' => 'required|date|before:today',
    //         'fecha_inicio_labor' => 'required|date|after_or_equal:fecha_nacimiento',
    //         'direccion' => 'required|string|max:255',
    //         'telefono' => 'nullable|string|max:255',
    //         'celular' => 'required|string|max:255',
    //         'correo' => 'required|string|max:255',
    //         'cargo_id' => 'required|exists:cargos,id',
    //         'salario' => 'required|numeric|min:0',
    //     ], [
    //         'nombres.required' => 'El campo nombres es obligatorio.',
    //         'apellidos.required' => 'El campo apellidos es obligatorio.',
    //         'identificacion.required' => 'El campo identificación es obligatorio.',
    //         'identificacion.unique' => 'La identificación ingresada ya existe.',
    //         'identificacion.regex' => 'La identificación debe ser numérica y no exceder 20 caracteres.',
    //         'expedida_en.required' => 'El campo expedida en es obligatorio.',
    //         'fecha_nacimiento.required' => 'El campo fecha de nacimiento es obligatorio.',
    //         'fecha_nacimiento.before' => 'La fecha de nacimiento debe ser anterior a hoy.',
    //         'fecha_inicio_labor.required' => 'El campo fecha de inicio laboral es obligatorio.',
    //         'fecha_inicio_labor.after_or_equal' => 'La fecha de inicio laboral debe ser posterior a la fecha de nacimiento.',
    //         'direccion.required' => 'El campo dirección es obligatorio.',
    //         'direccion.celular' => 'El campo celular es obligatorio.',
    //         'direccion.correo' => 'El campo correo es obligatorio.',
    //         'cargo_id.required' => 'El campo cargo es obligatorio.',
    //         'cargo_id.exists' => 'El cargo seleccionado no existe.',
    //         'salario.required' => 'El campo salario es obligatorio.',
    //         'salario.numeric' => 'El salario debe ser un valor numérico.',
    //         'salario.min' => 'El salario no puede ser negativo.',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
    //     }

    //     try {
    //         Empleado::create($request->all());
    //         return response()->json(['success' => true, 'message' => 'Empleado creado exitosamente.'], 201);
    //     } catch (Exception $e) {
    //         return response()->json(['success' => false, 'message' => 'Error al crear el empleado: ' . $e->getMessage()], 500);
    //     }
    // }

    // /**
    //  * Muestra los detalles de un empleado específico.
    //  */
    // public function edit($id)
    // {
    //     try {
    //         $empleado = Empleado::findOrFail($id);
    //         $empleado->fecha_nacimiento = $empleado->fecha_nacimiento
    //             ? Carbon::parse($empleado->fecha_nacimiento)->format('Y-m-d')
    //             : null;

    //         $empleado->fecha_inicio_labor = $empleado->fecha_inicio_labor
    //             ? Carbon::parse($empleado->fecha_inicio_labor)->format('Y-m-d')
    //             : null;
    //         return response()->json(['success' => true, 'data' => $empleado]);
    //     } catch (Exception $e) {
    //         return response()->json(['success' => false, 'message' => 'Error al cargar el empleado: ' . $e->getMessage()], 500);
    //     }
    // }

    // /**
    //  * Actualiza un empleado existente.
    //  */
    // public function update(Request $request, $id)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'nombres' => 'required|string|max:255',
    //         'apellidos' => 'required|string|max:255',
    //         'identificacion' => "required|string|max:20|regex:/^\d{1,20}$/|unique:empleados,identificacion,{$id}",
    //         'expedida_en' => 'required|string|max:255',
    //         'fecha_nacimiento' => 'required|date|before:today',
    //         'fecha_inicio_labor' => 'required|date|after_or_equal:fecha_nacimiento',
    //         'direccion' => 'required|string|max:255','telefono' => 'nullable|string|max:255',
    //         'celular' => 'required|string|max:255',
    //         'correo' => 'required|string|max:255',
    //         'cargo_id' => 'required|exists:cargos,id',
    //         'salario' => 'required|numeric|min:0',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
    //     }

    //     try {
    //         $empleado = Empleado::findOrFail($id);
    //         $empleado->update($request->all());
    //         return response()->json(['success' => true, 'message' => 'Empleado actualizado exitosamente.'], 200);
    //     } catch (Exception $e) {
    //         return response()->json(['success' => false, 'message' => 'Error al actualizar el empleado: ' . $e->getMessage()], 500);
    //     }
    // }

    // /**
    //  * Elimina un empleado.
    //  */
    // public function destroy($id)
    // {
    //     try {
    //         $empleado = Empleado::findOrFail($id);
    //         $empleado->delete();
    //         return response()->json(['success' => true, 'message' => 'Empleado eliminado exitosamente.'], 200);
    //     } catch (Exception $e) {
    //         return response()->json(['success' => false, 'message' => 'Error al eliminar el empleado: ' . $e->getMessage()], 500);
    //     }
    // }
}
