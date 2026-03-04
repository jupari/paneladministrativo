<?php

namespace App\Services;

use App\Models\Tercero;
use Exception;
use Illuminate\Support\Facades\Auth;

class ProveedorService
{

    public function __construct() {

    }

    public function obtenerProveedores()
    {
        $companyId = auth()->user()->company_id;

        $query = Tercero::with('tipoPersona', 'tipoIdentificacion', 'ciudad')
            ->where('tercerotipo_id', 1) // 1 = Proveedores
            ->where('company_id', $companyId)
            ->orderBy('created_at')
            ->get();

        return $query;
    }

    public function obtenerProveedorPorId($id)
    {
        $companyId = auth()->user()->company_id;

        return Tercero::with('tipoPersona', 'tipoIdentificacion', 'ciudad')
            ->where('company_id', $companyId)
            ->findOrFail($id);
    }

    public function crearProveedor(array $data)
    {
        // Agregar la company_id del usuario autenticado
        $data['company_id'] = auth()->user()->company_id;

        // También agregar el user_id del usuario que crea el proveedor
        $data['user_id'] = auth()->id();

        // Asegurar que el tipo sea 'proveedor'
        $data['tercerotipo_id'] = 1;

        return Tercero::create($data);
    }

    public function actualizarProveedor($id, array $data)
    {
        try {
            $companyId = auth()->user()->company_id;

            $tercero = Tercero::where('company_id', $companyId)
                ->findOrFail($id);

            $tercero->update($data);
            return $tercero;
        } catch (Exception $e) {
            throw new Exception("Error al actualizar el proveedor: " . $e->getMessage());
        }
    }

    public function eliminarProveedor($id)
    {
        $companyId = auth()->user()->company_id;

        $tercero = Tercero::where('company_id', $companyId)
            ->findOrFail($id);

        $tercero->delete();
        return true;
    }
}
