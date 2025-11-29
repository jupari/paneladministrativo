<?php

namespace App\Services;

use App\Models\Proceso;
use DateTime;
use Exception;
use Yajra\DataTables\Facades\DataTables;

class ProcesoService
{
    public function getAll()
    {
        return Proceso::all();
    }

    public function getDataTable($request)
    {
        try {
            $procesos = $this->getAll();
            if ($request->ajax()) {
                return DataTables::of($procesos)
                    ->addIndexColumn()
                    ->addColumn('id', fn($td) => $td->id)
                    ->addColumn('codigo', fn($td) => $td->codigo)
                    ->addColumn('nombre', fn($td) => $td->nombre)
                    ->addColumn('descripcion', fn($td) => $td->descripcion)
                    ->addColumn('estado', function ($td) {
                        if ($td->active==1) {
                            return '<div  class="col-6 offset-3 bg-success text-center rounded-pill">
                                        <span class="badge">Activo</span>
                                    </div>';
                                }
                        if ($td->active==0) {
                            return '<div class="col-6 offset-3 bg-warning text-center rounded-pill">
                                    <span class="badge">Inactivo</span>
                                </div>';
                        }
                    })
                    ->addColumn('acciones', function ($td) {
                        return '<button type="button" onclick="upProceso(' . $td->id . ')"
                                    class="btn btn-primary btn-circle btn-sm"
                                    data-toggle="tooltip" data-placement="top"
                                    title="Editar Proceso">
                                    <i class="fas fa-pencil-alt"></i>
                                </button> <button type="button" onclick="desactivarProceso(' . $td->id . ')"
                                    class="btn btn-danger btn-circle btn-sm"
                                    data-toggle="tooltip" data-placement="top"
                                    title="Cambiar estado proceso">
                                    <i class="fas fa-trash-alt"></i>
                                </button>';
                    })
                    ->rawColumns(['id','codigo','nombre','descripcion','estado','acciones'])
                    ->make(true);
            }
            return null; // Si no es AJAX
        } catch (Exception $e) {
            throw new Exception("Error al obtener los procesos de producción: " . $e->getMessage());
        }
    }
    public function getById($id)
    {
        return Proceso::with('procesosDet')->find($id);
    }

    public function create(array $data)
    {
        return Proceso::create($data);
    }

    public function find($id)
    {
        return Proceso::findOrFail($id);
    }

    public function update($id, array $data)
    {
        $elemento = Proceso::findOrFail($id);
        $elemento->update($data);
        return $elemento;
    }

    public function delete($id)
    {
        try {
            $proceso = Proceso::where('id', $id)->firstOrFail();
            $proceso->active = $proceso->active==1 ? 0 : 1;
            $proceso->save();
            return true;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function listar()
    {
        $query = Proceso::with('procesosDet');

        return $query->get();
    }

    public function listarxCodigo($id)
    {
        try {
            return Proceso::where('id', $id)->with('procesosDet')->get();
        } catch (Exception $e) {
            throw $e;
        }
    }
}
