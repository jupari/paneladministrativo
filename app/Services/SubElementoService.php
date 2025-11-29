<?php

namespace App\Services;

use App\Models\SubElemento;
use DateTime;
use Exception;
use Yajra\DataTables\Facades\DataTables;

class SubElementoService
{
    public function getAll()
    {
        return SubElemento::all();
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
                        if ($td->estado==1) {
                            return '<div class="bg-success text-center rounded-pill">
                                        <span class="badge">Activo</span>
                                    </div>';
                                }
                        if ($td->estado==2) {
                            return '<div class="bg-warning text-center rounded-pill">
                                    <span class="badge">Inactivo</span>
                                </div>';
                        }
                    })
                    ->addColumn('acciones', function ($td) {
                        return '<button type="button" onclick="upCuenta(' . $td->id . ')"
                                    class="btn btn-secondary btn-circle btn-sm"
                                    data-toggle="tooltip" data-placement="top"
                                    title="Editar Ficha Técnica">
                                    <i class="fas fa-pencil-alt"></i>
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
        return SubElemento::with('subElementos')->find($id);
    }

    public function createOrUpdate(array $data)
    {
        try {
            $data['elemento_id'] = $data['id'] ?? null;

            $querySubElemento = SubElemento::where('codigo', $data['codigo'])
                                    ->where('elemento_id', $data['elemento_id'])
                                    ->where('nombre', $data['nombre'])
                                    ->first();
            if ($querySubElemento) {
                // Actualizar
                $querySubElemento->update($data);
                return $querySubElemento;
            }
            // Crear
            return SubElemento::create( $data);
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function find($id)
    {
        return SubElemento::findOrFail($id);
    }

    public function update($id, array $data)
    {
        $elemento = SubElemento::findOrFail($id);
        $elemento->update($data);
        return $elemento;
    }

    public function delete($id)
    {
        try {
            $elemento = SubElemento::findOrFail($id);
            $elemento->delete();
            return true;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function listar($codigo = null)
    {
        $query = SubElemento::query();
        if ($codigo!='' && $codigo!=null) {
            $query->where('codigo', $codigo);
        }
        return $query->get();
    }
}
