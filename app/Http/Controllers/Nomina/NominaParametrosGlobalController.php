<?php

namespace App\Http\Controllers\Nomina;

use App\Http\Controllers\Controller;
use App\Models\NominaParametrosGlobal;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class NominaParametrosGlobalController extends Controller
{
    public function index(Request $request)
    {
        try {
            if ($request->ajax()) {
                // Identificar el registro actualmente activo para el año en curso
                try {
                    $activo = NominaParametrosGlobal::paraAno((int)date('Y'));
                    $activoId = $activo->id;
                } catch (Exception) {
                    $activoId = null;
                }

                $parametros = NominaParametrosGlobal::orderBy('vigencia', 'desc')->get();

                return DataTables::of($parametros)
                    ->addIndexColumn()
                    ->addColumn('vigencia_badge', function ($p) use ($activoId) {
                        $badge = '';
                        if ($p->id === $activoId) {
                            $badge = ' <span class="badge badge-success ml-1">'
                                   . '<i class="fas fa-check-circle mr-1"></i>ACTIVO</span>';
                        }
                        return '<strong>' . $p->vigencia . '</strong>' . $badge;
                    })
                    ->addColumn('smlv_fmt', fn($p) =>
                        '<span class="font-weight-bold">$' . number_format($p->smlv, 0, ',', '.') . '</span>'
                    )
                    ->addColumn('aux_transporte_fmt', fn($p) =>
                        '$' . number_format($p->aux_transporte, 0, ',', '.')
                    )
                    ->addColumn('uvt_fmt', fn($p) =>
                        '$' . number_format($p->uvt, 0, ',', '.')
                    )
                    ->addColumn('tope_badge', fn($p) =>
                        '<span class="badge badge-secondary">'
                        . '<i class="fas fa-times mr-1"></i>×' . $p->tope_exoneracion_ley1607 . ' SMLV</span>'
                    )
                    ->addColumn('active', fn($p) => $p->active
                        ? '<span class="badge badge-success"><i class="fas fa-circle mr-1" style="font-size:.6rem;"></i>Activo</span>'
                        : '<span class="badge badge-secondary"><i class="fas fa-circle mr-1" style="font-size:.6rem;"></i>Inactivo</span>'
                    )
                    ->addColumn('acciones', function ($p) use ($activoId) {
                        $acciones = '';
                        if (auth()->user()->can('nomina.parametros.edit')) {
                            $acciones .= '<button type="button" onclick="upParametro(' . $p->id . ')" '
                                       . 'class="btn btn-warning btn-xs mr-1" data-toggle="tooltip" title="Editar">'
                                       . '<i class="fas fa-edit"></i></button>';
                        }
                        if (auth()->user()->can('nomina.parametros.destroy')) {
                            $esActivo = ($p->id === $activoId) ? 'true' : 'false';
                            $acciones .= '<button type="button" onclick="deleteParametro(' . $p->id . ',' . $esActivo . ')" '
                                       . 'class="btn btn-danger btn-xs" data-toggle="tooltip" title="Eliminar">'
                                       . '<i class="fas fa-trash"></i></button>';
                        }
                        return $acciones;
                    })
                    ->rawColumns(['vigencia_badge', 'smlv_fmt', 'aux_transporte_fmt', 'uvt_fmt', 'tope_badge', 'active', 'acciones'])
                    ->make(true);
            }

            // Parámetro activo para la alerta informativa
            try {
                $paramActivo = NominaParametrosGlobal::paraAno((int)date('Y'));
            } catch (Exception) {
                $paramActivo = null;
            }

            return view('nomina.parametros_globales.index', compact('paramActivo'));

        } catch (Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'vigencia'                 => 'required|integer|min:2020|max:2050|unique:nom_parametros_globales,vigencia',
            'smlv'                     => 'required|numeric|min:0',
            'aux_transporte'           => 'required|numeric|min:0',
            'uvt'                      => 'required|numeric|min:0',
            'tope_exoneracion_ley1607' => 'required|integer|min:1|max:25',
            'active'                   => 'required|boolean',
        ], [
            'vigencia.required' => 'El año fiscal es obligatorio.',
            'vigencia.unique'   => 'Ya existe un registro para ese año.',
            'vigencia.integer'  => 'El año debe ser un número entero.',
            'vigencia.min'      => 'El año mínimo permitido es 2020.',
            'vigencia.max'      => 'El año máximo permitido es 2050.',
            'smlv.required'     => 'El SMLV es obligatorio.',
            'smlv.numeric'      => 'El SMLV debe ser un número.',
            'aux_transporte.required' => 'El auxilio de transporte es obligatorio.',
            'uvt.required'      => 'La UVT es obligatoria.',
            'tope_exoneracion_ley1607.required' => 'El tope de exoneración es obligatorio.',
            'tope_exoneracion_ley1607.min'      => 'El tope mínimo es 1 SMLV.',
            'tope_exoneracion_ley1607.max'      => 'El tope máximo es 25 SMLV.',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            NominaParametrosGlobal::create($request->only([
                'vigencia', 'smlv', 'aux_transporte', 'uvt', 'tope_exoneracion_ley1607', 'active',
            ]));
            return response()->json(['success' => true, 'message' => 'Parámetros creados exitosamente.'], 201);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al crear: ' . $e->getMessage()], 500);
        }
    }

    public function edit($id)
    {
        try {
            $param = NominaParametrosGlobal::findOrFail($id);
            return response()->json(['success' => true, 'data' => $param]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al cargar: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'vigencia'                 => "required|integer|min:2020|max:2050|unique:nom_parametros_globales,vigencia,{$id}",
            'smlv'                     => 'required|numeric|min:0',
            'aux_transporte'           => 'required|numeric|min:0',
            'uvt'                      => 'required|numeric|min:0',
            'tope_exoneracion_ley1607' => 'required|integer|min:1|max:25',
            'active'                   => 'required|boolean',
        ], [
            'vigencia.required' => 'El año fiscal es obligatorio.',
            'vigencia.unique'   => 'Ya existe un registro para ese año.',
            'smlv.required'     => 'El SMLV es obligatorio.',
            'aux_transporte.required' => 'El auxilio de transporte es obligatorio.',
            'uvt.required'      => 'La UVT es obligatoria.',
            'tope_exoneracion_ley1607.required' => 'El tope de exoneración es obligatorio.',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $param = NominaParametrosGlobal::findOrFail($id);
            $param->update($request->only([
                'vigencia', 'smlv', 'aux_transporte', 'uvt', 'tope_exoneracion_ley1607', 'active',
            ]));
            return response()->json(['success' => true, 'message' => 'Parámetros actualizados exitosamente.']);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al actualizar: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $param = NominaParametrosGlobal::findOrFail($id);

            // Protección: no eliminar si es el único registro activo
            if ($param->active) {
                $countActivos = NominaParametrosGlobal::where('active', true)->count();
                if ($countActivos <= 1) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No se puede eliminar el único registro activo. Active otro registro primero.',
                    ], 422);
                }
            }

            $param->delete();
            return response()->json(['success' => true, 'message' => 'Registro eliminado exitosamente.']);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al eliminar: ' . $e->getMessage()], 500);
        }
    }
}
