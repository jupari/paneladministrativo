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
                return Datatables::of($cargos)
                    ->addIndexColumn()
                    ->addColumn('id', function ($cargo) {
                        return $cargo->id;
                    })
                    ->addColumn('nombre', function ($cargo) {
                        return $cargo->nombre;
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
                    ->addColumn('acciones', function ($cargo) {
                        $acciones = '<button type="button" onclick="upCargo(' . $cargo->id . ')" class="btn btn-warning btn-sm" data-toggle="tooltip" title="Editar"><i class="fas fa-edit"></i></button>';
                        //$acciones .= ' <button type="button" onclick="deleteCargo(' . $cargo->id . ')" class="btn btn-danger btn-sm" data-toggle="tooltip" title="Eliminar"><i class="fas fa-trash"></i></button>';
                        return $acciones;
                    })
                    ->rawColumns(['id','nombre','active', 'acciones'])
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
            'nombre' => 'required|string|max:255|unique:cargos,nombre',
            'active' => 'required|in:1,0',
        ], [
            'nombre.required' => 'El campo nombre es obligatorio.',
            'nombre.unique' => 'El nombre ingresado ya existe.',
            'active.required' => 'El campo estado activo es obligatorio.',
            'active.in' => 'El estado activo debe ser 1 (activo) o 0 (inactivo).',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            Cargo::create($request->all());
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
            'nombre' => "required|string|max:255|unique:cargos,nombre,{$id}",
            'active' => 'required|in:1,0',
        ], [
            'nombre.required' => 'El campo nombre es obligatorio.',
            'nombre.unique' => 'El nombre ingresado ya existe.',
            'active.required' => 'El campo estado activo es obligatorio.',
            'active.in' => 'El estado activo debe ser 1 (activo) o 0 (inactivo).',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $cargo = Cargo::findOrFail($id);
            $cargo->update($request->all());
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
