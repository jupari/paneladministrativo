<?php

namespace App\Services;

use App\Models\Categoria;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class CategoriaService
{

    public function listar(Request $request)
    {
        $categorias = Categoria::orderBy('nombre', 'asc')->get();
        if ($request->ajax()) {
            return DataTables::of($categorias)
                ->addIndexColumn()
                ->addColumn('id', fn($categoria) => $categoria->id)
                ->addColumn('nombre', fn($categoria) => $categoria->nombre)
                ->addColumn('active', fn($categoria) =>
                    $categoria->active == 1
                        ? '<span class="badge bg-success">Activo</span>'
                        : '<span class="badge bg-danger">Inactivo</span>'
                )
                ->addColumn('created_at', fn($categoria) =>
                    $categoria->created_at
                            ? Carbon::parse($categoria->created_at)->format('d-m-Y H:i:s')
                            : 'N/A'
                )
                ->addColumn('acciones', fn($categoria) =>
                    '<button type="button" onclick="upCargo(' . $categoria->id . ')" class="btn btn-warning btn-sm" title="Editar">
                        <i class="fas fa-edit"></i></button>'
                )
                ->rawColumns(['id','nombre','active', 'acciones'])
                ->make(true);
        }
        return view('contratos.categorias.index');
    }

    public function guardar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255|unique:cargos,nombre',
            'active' => 'required|in:1,0',
        ]);

        if ($validator->fails()) {
            return ['success' => false, 'errors' => $validator->errors(), 'code' => 422];
        }

        try {
            Categoria::create($request->all());
            return ['success' => true, 'message' => 'Cargo creado exitosamente.', 'code' => 201];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error al crear el cargo: ' . $e->getMessage(), 'code' => 500];
        }
    }

    public function editar($id)
    {
        try {
            $categoria = Categoria::findOrFail($id);
            return ['success' => true, 'data' => $categoria];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error al cargar el cargo: ' . $e->getMessage()];
        }
    }

    public function actualizar(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => "required|string|max:255|unique:cargos,nombre,{$id}",
            'active' => 'required|in:1,0',
        ]);

        if ($validator->fails()) {
            return ['success' => false, 'errors' => $validator->errors(), 'code' => 422];
        }

        try {
            $cargo = Categoria::findOrFail($id);
            $cargo->update($request->all());
            return ['success' => true, 'message' => 'Cargo actualizado exitosamente.'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error al actualizar el cargo: ' . $e->getMessage()];
        }
    }

    public function eliminar($id)
    {
        try {
            $categoria = Categoria::findOrFail($id);
            $categoria->delete();
            return ['success' => true, 'message' => 'Cargo eliminado exitosamente.'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error al eliminar el cargo: ' . $e->getMessage()];
        }
    }

}
