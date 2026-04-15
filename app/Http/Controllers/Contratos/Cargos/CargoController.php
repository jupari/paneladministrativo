<?php

namespace App\Http\Controllers\Contratos\Cargos;

use App\Http\Controllers\Controller;
use App\Models\Cargo;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Permission;
use Yajra\DataTables\Facades\DataTables;

class CargoController extends Controller
{
    public function index(Request $request)
    {
        try {
            $cargos = Cargo::orderBy('nombre', 'asc')->get();

            if ($request->ajax()) {
                $arlLabels = [
                    1 => ['label' => 'Nivel I',   'color' => 'success', 'pct' => '0.522%'],
                    2 => ['label' => 'Nivel II',  'color' => 'info',    'pct' => '1.044%'],
                    3 => ['label' => 'Nivel III', 'color' => 'warning', 'pct' => '2.436%'],
                    4 => ['label' => 'Nivel IV',  'color' => 'orange',  'pct' => '4.350%'],
                    5 => ['label' => 'Nivel V',   'color' => 'danger',  'pct' => '6.960%'],
                ];

                return Datatables::of($cargos)
                    ->addIndexColumn()
                    ->addColumn('nombre', fn($c) => $c->nombre)
                    ->addColumn('salario_base_fmt', function ($cargo) {
                        if ($cargo->salario_base) {
                            return '<span class="font-weight-bold">$' . number_format($cargo->salario_base, 0, ',', '.') . '</span>';
                        }
                        return '<span class="text-muted small"><i class="fas fa-equals mr-1"></i>SMLV vigente</span>';
                    })
                    ->addColumn('arl_badge', function ($cargo) use ($arlLabels) {
                        $nivel = $cargo->arl_nivel ?? 1;
                        $info  = $arlLabels[$nivel] ?? $arlLabels[1];
                        $color = $info['color'] === 'orange' ? 'warning' : $info['color'];
                        return '<span class="badge badge-' . $color . '">'
                             . $info['label'] . '</span>'
                             . '<br><small class="text-muted">' . $info['pct'] . '</small>';
                    })
                    ->addColumn('exoneracion_badge', function ($cargo) {
                        if ($cargo->aplica_exoneracion_ley1607 ?? true) {
                            return '<span class="badge badge-success"><i class="fas fa-check mr-1"></i>Exonerado</span>'
                                 . '<br><small class="text-muted">−13.5%</small>';
                        }
                        return '<span class="badge badge-secondary"><i class="fas fa-times mr-1"></i>No aplica</span>'
                             . '<br><small class="text-muted">+13.5%</small>';
                    })
                    ->addColumn('active', fn($c) => $c->active == 1
                        ? '<span class="badge badge-success"><i class="fas fa-circle mr-1" style="font-size:.6rem;"></i>Activo</span>'
                        : '<span class="badge badge-secondary"><i class="fas fa-circle mr-1" style="font-size:.6rem;"></i>Inactivo</span>'
                    )
                    ->addColumn('acciones', function ($cargo) {
                        $acciones = '';
                        if (auth()->user()->can('cargos.edit')) {
                            $acciones .= '<button type="button" onclick="upCargo(' . $cargo->id . ')" '
                                       . 'class="btn btn-warning btn-xs mr-1" data-toggle="tooltip" title="Editar">'
                                       . '<i class="fas fa-edit"></i></button>';
                        }
                        if (auth()->user()->can('cargos.destroy')) {
                            $acciones .= '<button type="button" onclick="deleteCargo(' . $cargo->id . ')" '
                                       . 'class="btn btn-danger btn-xs" data-toggle="tooltip" title="Eliminar">'
                                       . '<i class="fas fa-trash"></i></button>';
                        }
                        return $acciones;
                    })
                    ->rawColumns(['salario_base_fmt', 'arl_badge', 'exoneracion_badge', 'active', 'acciones'])
                    ->make(true);
            }

            return view('contratos.cargos.index');
        } catch (Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Almacena un nuevo cargo.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre'                       => 'required|string|max:255|unique:cargos,nombre',
            'active'                       => 'required|in:1,0',
            'salario_base'                 => 'nullable|numeric|min:0',
            'arl_nivel'                    => 'nullable|integer|in:1,2,3,4,5',
            'aplica_exoneracion_ley1607'   => 'nullable|boolean',
        ], [
            'nombre.required' => 'El campo nombre es obligatorio.',
            'nombre.unique'   => 'El nombre ingresado ya existe.',
            'active.required' => 'El campo estado activo es obligatorio.',
            'active.in'       => 'El estado activo debe ser 1 (activo) o 0 (inactivo).',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            Cargo::create($request->only(['nombre', 'active', 'salario_base', 'arl_nivel', 'aplica_exoneracion_ley1607']));
            return response()->json(['success' => true, 'message' => 'Cargo creado exitosamente.'], 201);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al crear el cargo: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Muestra los detalles de un cargo específico.
     */
    public function edit($id)
    {
        try {
            $cargo = Cargo::findOrFail($id);
            return response()->json(['success' => true, 'data' => $cargo]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al cargar el cargo: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Actualiza un cargo existente.
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'nombre'                       => "required|string|max:255|unique:cargos,nombre,{$id}",
            'active'                       => 'required|in:1,0',
            'salario_base'                 => 'nullable|numeric|min:0',
            'arl_nivel'                    => 'nullable|integer|in:1,2,3,4,5',
            'aplica_exoneracion_ley1607'   => 'nullable|boolean',
        ], [
            'nombre.required' => 'El campo nombre es obligatorio.',
            'nombre.unique'   => 'El nombre ingresado ya existe.',
            'active.required' => 'El campo estado activo es obligatorio.',
            'active.in'       => 'El estado activo debe ser 1 (activo) o 0 (inactivo).',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $cargo = Cargo::findOrFail($id);
            $cargo->update($request->only(['nombre', 'active', 'salario_base', 'arl_nivel', 'aplica_exoneracion_ley1607']));
            return response()->json(['success' => true, 'message' => 'Cargo actualizado exitosamente.'], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al actualizar el cargo: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Elimina un cargo.
     */
    public function destroy($id)
    {
        try {
            $cargo = Cargo::findOrFail($id);
            $cargo->delete();
            return response()->json(['success' => true, 'message' => 'Cargo eliminado exitosamente.'], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al eliminar el cargo: ' . $e->getMessage()], 500);
        }
    }
}
