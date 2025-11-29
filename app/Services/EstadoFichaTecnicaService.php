<?php

namespace App\Services;

use App\Models\EstadoFichaTecnica;

class EstadoFichaTecnicaService
{
    public function getAll()
    {
        return EstadoFichaTecnica::all();
    }

    public function getById($id)
    {
        return EstadoFichaTecnica::findOrFail($id);
    }

    public function create(array $data)
    {
        return EstadoFichaTecnica::create($data);
    }

    public function update($id, array $data)
    {
        $model = EstadoFichaTecnica::findOrFail($id);
        $model->update($data);
        return $model;
    }

    public function delete($id)
    {
        $model = EstadoFichaTecnica::findOrFail($id);
        return $model->delete();
    }
}