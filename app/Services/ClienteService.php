<?php

namespace App\Services;

use App\Models\Tercero;
use Exception;
use Illuminate\Support\Facades\Auth;

class ClienteService
{

    public function __construct() {

    }

    public function obtenerClientes()
    {
        $companyId = auth()->user()->company_id;

        $query = Tercero::with('tipoPersona', 'tipoIdentificacion', 'ciudad')
            ->where('tercerotipo_id', 2)
            ->where('company_id', $companyId)
            ->orderBy('created_at')
            ->get();

        return $query;
    }

    public function obtenerClientePorId($id)
    {
        $companyId = auth()->user()->company_id;

        return Tercero::with('tipoPersona', 'tipoIdentificacion', 'ciudad')
            ->where('company_id', $companyId)
            ->findOrFail($id);
    }

    public function crearCliente(array $data)
    {
        // Agregar la company_id del usuario autenticado
        $data['company_id'] = auth()->user()->company_id;

        // También agregar el user_id del usuario que crea el cliente
        $data['user_id'] = auth()->id();

        return Tercero::create($data);
    }

    public function actualizarCliente($id, array $data)
    {
        try {
            $companyId = auth()->user()->company_id;

            $tercero = Tercero::where('company_id', $companyId)
                ->findOrFail($id);

            $tercero->update($data);
            return $tercero;
        } catch (Exception $e) {
            throw new Exception("Error al actualizar el cliente: " . $e->getMessage());
        }
    }

    public function eliminarCliente($id)
    {
        $companyId = auth()->user()->company_id;

        $tercero = Tercero::where('company_id', $companyId)
            ->findOrFail($id);

        $tercero->delete();
        return true;
    }
}
