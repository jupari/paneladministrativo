<?php

namespace App\Services;

use App\Models\Material;
use Yajra\DataTables\Facades\DataTables;
use Exception;

class MaterialService
{
     /**
     * Retorna todos los materiales
     */
    public function getAll()
    {
        return Material::orderBy('nombre', 'asc')->get();
    }

    /**
     * Retorna un material por ID
     */
    public function getById($id)
    {
        return Material::findOrFail($id);
    }

    /**
     * Retorna materiales en formato DataTable
     */
    public function getDataTable($request)
    {
        $materiales = $this->getAll();

        if ($request->ajax()) {
            return DataTables::of($materiales)
                ->addIndexColumn()
                ->addColumn('id', fn($td) => $td->id)
                ->addColumn('codigo', fn($td) => $td->codigo)
                ->addColumn('nombre', fn($td) => $td->nombre)
                ->addColumn('descripcion', fn($td) => $td->descripcion)
                ->addColumn('unidad_medida', fn($td) => $td->unidad_medida)
                ->addColumn('active', function ($td) {
                    return $td->active == 1
                        ? '<span class="badge bg-success">Activo</span>'
                        : '<span class="badge bg-danger">Inactivo</span>';
                })
                ->addColumn('acciones', function ($td) {
                    return '
                        <button type="button" onclick="upMaterial(' . $td->id . ')"
                            class="btn btn-warning btn-circle btn-sm" title="Editar Material">
                            <i class="fas fa-pencil-alt"></i>
                        </button>
                        <button type="button" onclick="deleteMaterial(' . $td->id . ')"
                            class="btn btn-danger btn-circle btn-sm" title="Eliminar Material">
                            <i class="fas fa-trash"></i>
                        </button>';
                })
                ->rawColumns(['id', 'codigo', 'nombre', 'descripcion', 'unidad_medida', 'active', 'acciones'])
                ->make(true);
        }

        return null;
    }

    /**
     * Crear material
     */
    public function store(array $data)
    {
        return Material::create($data);
    }

    /**
     * Actualizar material
     */
    public function update($id, array $data)
    {
        $material = $this->getById($id);
        $material->update($data);
        return $material;
    }

    /**
     * Eliminar material
     */
    public function destroy($id)
    {
        $material = $this->getById($id);
        return $material->delete();
    }

    public function listar()
    {
        $query = Material::where('active', 1)->orderBy('nombre', 'asc')->get();
        return response()->json($query);
    }


}
