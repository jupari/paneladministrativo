<?php

namespace App\Services;

use App\Models\OrdObservacion;

class OrdObservacionService
{
    public function getAll()
    {
        return OrdObservacion::orderBy('id', 'desc')->get();
    }

    public function create(array $data)
    {
        if (!isset($data['active'])) {
            $data['active'] = true;
        }
        return OrdObservacion::create($data);
    }

    public function update($id, array $data)
    {
        $observacion = OrdObservacion::findOrFail($id);
        if (!isset($data['active'])) {
            $data['active'] = false;
        }
        $observacion->update($data);
        return $observacion;
    }

    public function toggleActive($id)
    {
        $observacion = OrdObservacion::findOrFail($id);
        $observacion->active = !$observacion->active;
        $observacion->save();
        return $observacion;
    }
}
