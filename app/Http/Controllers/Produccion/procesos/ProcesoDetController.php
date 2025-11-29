<?php

namespace App\Http\Controllers\Produccion\procesos;

use App\Http\Controllers\Controller;
use App\Services\ProcesoDetService;
use App\Services\SubElementoService;
use Exception;
use Illuminate\Http\Request;

class ProcesoDetController extends Controller
{
    protected ProcesoDetService $procesoDetService;

    public function __construct(ProcesoDetService $procesoDetService)
    {
        $this->procesoDetService = $procesoDetService;

    }

    public function index($proceso_id)
    {
        $elementos = $this->procesoDetService->listar($proceso_id);
        return response()->json($elementos);
    }

    public function show($proceso_id)
    {
        $elemento = $this->procesoDetService->getById($proceso_id);
        if (!$elemento) {
            return response()->json(['message' => 'Elemento no encontrado'], 404);
        }
        return response()->json($elemento);
    }

    public function store(Request $request)
    {
        try {
            $data = $request->all();
            $elemento = $this->procesoDetService->create($data);
            return response()->json($elemento, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al crear el elemento', 'error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $data = $request->all();
        $elemento = $this->procesoDetService->update($id, $data);
        if (!$elemento) {
            return response()->json(['message' => 'Elemento no encontrado'], 404);
        }
        return response()->json($elemento);
    }

    public function destroy($id)
    {
        try {
            $deleted = $this->procesoDetService->delete($id);
            if (!$deleted) {
                return response()->json(['message' => 'Elemento no encontrado'], 404);
            }
            return response()->json(['message' => 'Elemento eliminado correctamente']);
        } catch (Exception $e) {
            return response()->json(['message' => 'Error al eliminar el elemento', 'error' => $e->getMessage()], 500);
        }


    }

    public function listar($codigo = null)
    {
        $elementos = $this->procesoDetService->listar($codigo);
        return response()->json($elementos);
    }
}
