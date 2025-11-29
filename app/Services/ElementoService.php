<?php

namespace App\Services;

use App\Models\Elemento;
use DateTime;
use Exception;
use Yajra\DataTables\Facades\DataTables;

class ElementoService
{
    public function getAll()
    {
        return Elemento::all();
    }

    public function getDataTable($request)
    {
        try {
            $elementos = $this->getAll();
            if ($request->ajax()) {
                return DataTables::of($elementos)
                    ->addIndexColumn()
                    ->addColumn('id', fn($td) => $td->id)
                    ->addColumn('codigo', fn($td) => $td->codigo)
                    ->addColumn('nombre', fn($td) => $td->nombre)
                    ->addColumn('valor', fn($td) => $td->valor)
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
                        return '<button type="button" onclick="upParametro(' . $td->id . ')"
                                    class="btn btn-primary btn-circle btn-sm"
                                    data-toggle="tooltip" data-placement="top"
                                    title="Editar Ficha Técnica">
                                    <i class="fas fa-pencil-alt"></i>
                                </button> <button type="button" onclick="desactivarParametro(' . $td->id . ')"
                                    class="btn btn-danger btn-circle btn-sm"
                                    data-toggle="tooltip" data-placement="top"
                                    title="Desactivar Ficha Técnica">
                                    <i class="fas fa-trash-alt"></i>
                                </button>';
                    })
                    ->rawColumns(['id','codigo','nombre','valor','estado','acciones'])
                    ->make(true);
            }
            return null; // Si no es AJAX
        } catch (Exception $e) {
            throw new Exception("Error al obtener las Fichas Técnicas: " . $e->getMessage());
        }
    }
    public function getById($id)
    {
        return Elemento::with('subElementos')->find($id);
    }

    public function create(array $data)
    {
        return Elemento::create($data);
    }

    public function find($id)
    {
        return Elemento::findOrFail($id);
    }

    public function update($id, array $data)
    {
        $elemento = Elemento::findOrFail($id);
        $elemento->update($data);
        return $elemento;
    }

    public function delete($id)
    {
        try {
            $elemento = Elemento::where('id', $id)->firstOrFail();
            $elemento->active = $elemento->active==1 ? 0 : 1;
            $elemento->save();
            return true;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function listar($codigo = null)
    {
        $query = Elemento::with('subElementos');
        if ($codigo) {
            $query->where('codigo', $codigo);
        }
        return $query->get();
    }

    public function listarxCodigo($codigo)
    {
        try {
            return Elemento::where('codigo', $codigo)->with('subElementos')->get();
        } catch (Exception $e) {
            throw $e;
        }
    }
}
