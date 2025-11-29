<?php

namespace App\Services;

use App\Models\ProcesoDet;
use DateTime;
use Exception;
use Yajra\DataTables\Facades\DataTables;

class ProcesoDetService
{
    public function getAll()
    {
        return ProcesoDet::all();
    }


    public function getById($proceso_id)
    {
        return ProcesoDet::with('proceso')->where('proceso_id', $proceso_id)->get();
    }

    public function create(array $data)
    {
        try {
            $data['proceso_id'] = $data['id'] ?? null;
            $minutos = $data['tiempo']; // 90
            $horas = floor($minutos / 60);
            $mins = $minutos % 60;
            $data['tiempo'] = sprintf('%02d:%02d:00', $horas, $mins); // "01:30:00"
            return ProcesoDet::create($data);
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function find($id)
    {
        return ProcesoDet::findOrFail($id);
    }

    public function update($id, array $data)
    {
        $elemento = ProcesoDet::findOrFail($id);
        $elemento->update($data);
        return $elemento;
    }

    public function delete($id)
    {
        try {
            $procesoDet = ProcesoDet::where('id', $id)->firstOrFail();
            $procesoDet->active = $procesoDet->active==1 ? 0 : 1;
            $procesoDet->save();
            return true;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function listar($id = null)
    {
        $query = ProcesoDet::query();
        if ($id!='' && $id!=null) {
            $query->where('proceso_id', $id);
        }
        return $query->get();
    }
}
