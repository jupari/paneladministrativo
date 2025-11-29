<?php

namespace App\Services;

use App\Models\FichaTecnica;
use DateTime;
use Exception;
use Yajra\Datatables\Datatables;
use PhpOffice\PhpSpreadsheet\Calculation\MathTrig\Exp;

class FichaTecnicaService
{
    public function getAll()
    {
        return FichaTecnica::with('estado')->get();
    }

    public function getDataTable($request)
    {
        try {
            $fichasTecnicas = $this->getAll();

            if ($request->ajax()) {
                return DataTables::of($fichasTecnicas)
                    ->addIndexColumn()
                    ->addColumn('id', fn($td) => $td->id)
                    ->addColumn('codigo', fn($td) => $td->codigo)
                    ->addColumn('nombre', fn($td) => $td->nombre)
                    ->addColumn('coleccion', fn($td) => $td->coleccion)
                    ->addColumn('fecha', function ($td) {
                        $date = new DateTime($td->fecha);
                        return $date->format('d/m/Y');
                    })
                    ->addColumn('estado', function ($td) {
                        return '<div class="bg-' . $td->estado->color . ' text-center rounded-pill">
                                    <span class="badge">' . $td->estado->nombre . '</span>
                                </div>';
                    })
                    ->addColumn('acciones', function ($td) {
                        return '<a type="button" href="'.route('admin.fichas-tecnicas.show', $td->id).'"
                                    class="btn btn-primary btn-circle btn-sm"
                                    data-toggle="tooltip" data-placement="top"
                                    title="Ver Ficha Técnica">
                                    <i class="fas fa-eye"></i>
                                </a><button type="button" onclick="cambiarEstado(' . $td->id . ')"
                                    class="btn btn-secondary btn-circle btn-sm"
                                    data-toggle="tooltip" data-placement="top"
                                    title="Cambiar el estado Ficha Técnica">
                                    <i class="fas fa-arrows-alt"></i>
                                </button>';
                    })
                    ->rawColumns(['id','codigo','nombre','coleccion','fecha','estado','acciones'])
                    ->make(true);
            }

            return null; // Si no es AJAX
        } catch (Exception $e) {
            throw new Exception("Error al obtener las Fichas Técnicas: " . $e->getMessage());
        }
    }

    public function getById($id)
    {
        return FichaTecnica::with('bocetos')->where('id', $id)->get();
    }

    public function create(array $data)
    {
        try {
            $data['estado_ficha_tecnica_id']=1;
            return FichaTecnica::create($data);
        } catch (Exception $e) {
            throw $e->getMessage();
        }

    }

    public function update($id, array $data)
    {
        try {
            $model = FichaTecnica::findOrFail($id);
            $model->update($data);
            return $model;
        } catch (Exception $e) {
            throw $e->getMessage();
        }
    }

    public function delete($id)
    {
        try {
            $model = FichaTecnica::findOrFail($id);
            $model->estado_ficha_tecnica_id = $model->estado_ficha_tecnica_id==1?2:1;
            $model->save();
            return true;
        } catch (Exception $e) {
            throw $e->getMessage();
        }
    }
}
