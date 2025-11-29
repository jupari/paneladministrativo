<?php

namespace App\Services;

use App\Models\FichaTecnica;

use App\Models\FichaTecnicaMaterial;
use DateTime;
use Exception;


class FichaTecnicaMaterialService
{
    public function getAll()
    {
        return FichaTecnicaMaterial::getAll();
    }

    public function getById($id)
    {
       return FichaTecnicaMaterial::where('fichatecnica_id', $id)->get();
    }

    public function store(array $data)
    {
        try {
            return FichaTecnicaMaterial::create($data);
        } catch (Exception $e) {
            throw $e;
        }

    }


    public function destroy($id)
    {
        try {
            $ftmaterial = FichaTecnicaMaterial::findOrFail($id);
            $ftmaterial->delete();
            return true;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function listar()
    {
        $query = FichaTecnicaMaterial::where('active', 1)->orderBy('nombre', 'asc')->get();
        return response()->json($query);
    }
}
