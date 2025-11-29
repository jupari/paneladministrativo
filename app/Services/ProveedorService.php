<?php

namespace App\Services;

use App\Models\Tercero;

class ProveedorService
{

    public function __construct() {

    }

    public function obtenerProveedores()
    {
        $query =Tercero::with('tipoPersona', 'tipoIdentificacion', 'ciudad')->where('tercerotipo_id', 2)->orderBy('created_at')->get();
        return $query;
    }

    public function obtenerProveedorPorId($id)
    {
        return Tercero::with('tipoPersona', 'tipoIdentificacion', 'ciudad')->findOrFail($id);
    }

    public function crearProveedor(array $data)
    {
        $data['tercerotipo_id'] = 2; // Asegurar que el tipo sea 'proveedor'
        return Tercero::create($data);
    }

    public function actualizarProveedor($id, array $data)
    {
        $tercero = Tercero::findOrFail($id);
        $tercero->update($data);
        return $tercero;
    }

    public function eliminarProveedor($id)
    {
        $tercero = Tercero::findOrFail($id);
        $tercero->delete();
        return true;
    }
}
