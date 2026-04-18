<?php

namespace App\Http\Controllers\Nomina;

use App\Http\Controllers\Controller;
use App\Models\NominaTurno;
use App\Services\ParametroService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class NominaTurnoController extends Controller
{
    public function __construct(
        protected ParametroService $parametros,
    ) {}
    public function index(Request $request)
    {
        try {
            if ($request->ajax()) {
                $turnos = NominaTurno::orderBy('nombre')->get();

                return DataTables::of($turnos)
                    ->addIndexColumn()
                    ->addColumn('tipo_badge', fn($t) =>
                        $t->tipo_ordinaria === 'diurna'
                            ? '<span class="badge badge-warning text-dark"><i class="fas fa-sun mr-1"></i>Diurna</span>'
                            : '<span class="badge badge-dark"><i class="fas fa-moon mr-1"></i>Nocturna</span>'
                    )
                    ->addColumn('dominical_badge', fn($t) =>
                        $t->es_dominical_festivo
                            ? '<span class="badge badge-danger"><i class="fas fa-calendar-times mr-1"></i>Dom/Fest</span>'
                            : '<span class="badge badge-secondary">Semana</span>'
                    )
                    ->addColumn('horas_ord', fn($t) =>
                        '<span class="badge badge-info">Máx. ' . $t->max_horas_ordinarias . 'h/día</span>'
                    )
                    ->addColumn('extras_badge', function ($t) {
                        $items = [];
                        if ($t->max_horas_extras == 0) {
                            return '<span class="badge badge-secondary">Sin HE</span>';
                        }
                        if ($t->tiene_extras_diurnas) {
                            $items[] = '<span class="badge badge-warning text-dark mr-1"><i class="fas fa-sun mr-1"></i>HE Diurnas</span>';
                        }
                        if ($t->tiene_extras_nocturnas) {
                            $items[] = '<span class="badge badge-dark mr-1"><i class="fas fa-moon mr-1"></i>HE Nocturnas</span>';
                        }
                        $maxBadge = '<br><small class="text-muted">Máx. ' . $t->max_horas_extras . 'h extra/día</small>';
                        return implode('', $items) . $maxBadge;
                    })
                    ->addColumn('active', fn($t) => $t->active
                        ? '<span class="badge badge-success"><i class="fas fa-circle mr-1" style="font-size:.6rem;"></i>Activo</span>'
                        : '<span class="badge badge-secondary"><i class="fas fa-circle mr-1" style="font-size:.6rem;"></i>Inactivo</span>'
                    )
                    ->addColumn('acciones', function ($t) {
                        $acciones = '';
                        if (auth()->user()->can('nomina.turnos.edit')) {
                            $acciones .= '<button type="button" onclick="upTurno(' . $t->id . ')" '
                                       . 'class="btn btn-warning btn-xs mr-1" data-toggle="tooltip" title="Editar">'
                                       . '<i class="fas fa-edit"></i></button>';
                        }
                        if (auth()->user()->can('nomina.turnos.destroy')) {
                            $acciones .= '<button type="button" onclick="deleteTurno(' . $t->id . ')" '
                                       . 'class="btn btn-danger btn-xs" data-toggle="tooltip" title="Eliminar">'
                                       . '<i class="fas fa-trash"></i></button>';
                        }
                        return $acciones;
                    })
                    ->rawColumns(['tipo_badge', 'dominical_badge', 'horas_ord', 'extras_badge', 'active', 'acciones'])
                    ->make(true);
            }

            return view('nomina.turnos.index', [
                'maxOrd'   => $this->parametros->getInt('TURNO_MAX_ORD', 7),
                'maxExtra' => $this->parametros->getInt('TURNO_MAX_EXTRA', 2),
            ]);

        } catch (Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function activos()
    {
        return response()->json(
            NominaTurno::where('active', true)->orderBy('nombre')->get()
        );
    }

    public function store(Request $request)
    {
        $maxOrd   = $this->parametros->getInt('TURNO_MAX_ORD', 7);
        $maxExtra = $this->parametros->getInt('TURNO_MAX_EXTRA', 2);

        $validator = Validator::make($request->all(), [
            'nombre'                 => 'required|string|max:100|unique:nom_turnos,nombre',
            'descripcion'            => 'nullable|string|max:255',
            'tipo_ordinaria'         => 'required|in:diurna,nocturna',
            'es_dominical_festivo'   => 'required|boolean',
            'max_horas_ordinarias'   => "required|integer|min:1|max:{$maxOrd}",
            'tiene_extras_diurnas'   => 'required|boolean',
            'tiene_extras_nocturnas' => 'required|boolean',
            'max_horas_extras'       => "required|integer|min:0|max:{$maxExtra}",
            'active'                 => 'required|boolean',
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.unique'   => 'Ya existe un turno con ese nombre.',
            'tipo_ordinaria.required' => 'El tipo de jornada es obligatorio.',
            'max_horas_ordinarias.max' => "El máximo de horas ordinarias es {$maxOrd} por día.",
            'max_horas_extras.max'     => "El máximo de horas extra es {$maxExtra} por día.",
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            NominaTurno::create($request->only([
                'nombre', 'descripcion', 'tipo_ordinaria', 'es_dominical_festivo',
                'max_horas_ordinarias', 'tiene_extras_diurnas', 'tiene_extras_nocturnas',
                'max_horas_extras', 'active',
            ]));
            return response()->json(['success' => true, 'message' => 'Turno creado exitosamente.'], 201);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al crear: ' . $e->getMessage()], 500);
        }
    }

    public function edit($id)
    {
        try {
            $turno = NominaTurno::findOrFail($id);
            return response()->json(['success' => true, 'data' => $turno]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al cargar: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $maxOrd   = $this->parametros->getInt('TURNO_MAX_ORD', 7);
        $maxExtra = $this->parametros->getInt('TURNO_MAX_EXTRA', 2);

        $validator = Validator::make($request->all(), [
            'nombre'                 => "required|string|max:100|unique:nom_turnos,nombre,{$id}",
            'descripcion'            => 'nullable|string|max:255',
            'tipo_ordinaria'         => 'required|in:diurna,nocturna',
            'es_dominical_festivo'   => 'required|boolean',
            'max_horas_ordinarias'   => "required|integer|min:1|max:{$maxOrd}",
            'tiene_extras_diurnas'   => 'required|boolean',
            'tiene_extras_nocturnas' => 'required|boolean',
            'max_horas_extras'       => "required|integer|min:0|max:{$maxExtra}",
            'active'                 => 'required|boolean',
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.unique'   => 'Ya existe un turno con ese nombre.',
            'max_horas_ordinarias.max' => "El máximo de horas ordinarias es {$maxOrd} por día.",
            'max_horas_extras.max'     => "El máximo de horas extra es {$maxExtra} por día.",
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $turno = NominaTurno::findOrFail($id);
            $turno->update($request->only([
                'nombre', 'descripcion', 'tipo_ordinaria', 'es_dominical_festivo',
                'max_horas_ordinarias', 'tiene_extras_diurnas', 'tiene_extras_nocturnas',
                'max_horas_extras', 'active',
            ]));
            return response()->json(['success' => true, 'message' => 'Turno actualizado exitosamente.']);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al actualizar: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $turno = NominaTurno::findOrFail($id);
            $turno->delete();
            return response()->json(['success' => true, 'message' => 'Turno eliminado exitosamente.']);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al eliminar: ' . $e->getMessage()], 500);
        }
    }
}
