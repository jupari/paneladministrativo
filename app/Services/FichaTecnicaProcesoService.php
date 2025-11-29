<?php

namespace App\Services;

use App\Models\FichaTecnicaProceso;
use DateTime;
use Exception;
use Yajra\Datatables\Datatables;
use PhpOffice\PhpSpreadsheet\Calculation\MathTrig\Exp;
use Illuminate\Support\Facades\Storage;

class FichaTecnicaProcesoService
{
    public function getAll()
    {
        return FichaTecnicaProceso::getAll();
    }

    public function getById($id)
    {
        return  FichaTecnicaProceso::where('fichatecnica_id', $id)->get();
    }

    public function store(array $data)
    {
        try {
            return FichaTecnicaProceso::create($data);
        } catch (Exception $e) {
            throw $e;
        }

    }


    public function destroy($id)
    {
        try {
            $ftmaterial = FichaTecnicaProceso::findOrFail($id);
            $ftmaterial->delete();
            return true;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function listar()
    {
        $query = FichaTecnicaProceso::where('active', 1)->orderBy('nombre', 'asc')->get();
        return response()->json($query);
    }
}
