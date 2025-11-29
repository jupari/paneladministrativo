<?php

namespace App\Services;

use App\Models\Tercero;

class ClienteService
{

    public function __construct() {

    }

    public function obtenerClientes()
    {
        $query =Tercero::with('tipoPersona', 'tipoIdentificacion', 'ciudad')->where('tercerotipo_id', 2)->orderBy('created_at')->get();
        return $query;
    }

    public function obtenerClientePorId($id)
    {
        return Tercero::with('tipoPersona', 'tipoIdentificacion', 'ciudad')->findOrFail($id);
    }

    public function crearCliente(array $data)
    {
        return Tercero::create($data);
    }

    public function actualizarCliente($id, array $data)
    {
        $tercero = Tercero::findOrFail($id);
        $tercero->update($data);
        return $tercero;
    }

    public function eliminarCliente($id)
    {
        $tercero = Tercero::findOrFail($id);
        $tercero->delete();
        return true;
    }
}
