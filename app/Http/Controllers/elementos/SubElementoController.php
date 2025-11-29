<?php

namespace App\Http\Controllers\elementos;

use App\Http\Controllers\Controller;
use App\Services\SubElementoService;
use Exception;
use Illuminate\Http\Request;

class SubElementoController extends Controller
{
    protected SubElementoService $subelementoService;

    public function __construct(SubElementoService $subelementoService)
    {
        $this->subelementoService = $subelementoService;
    }

    public function index($codigo)
    {
        $elementos = $this->subelementoService->listar($codigo);
        return response()->json($elementos);
    }

    public function show($id)
    {
        $elemento = $this->subelementoService->getById($id);
        if (!$elemento) {
            return response()->json(['message' => 'Elemento no encontrado'], 404);
        }
        return response()->json($elemento);
    }

    public function store(Request $request)
    {
        try {
            $data = $request->all();
            $elemento = $this->subelementoService->createOrUpdate($data);
            return response()->json($elemento, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al crear el elemento', 'error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $data = $request->all();
        $elemento = $this->subelementoService->update($id, $data);
        if (!$elemento) {
            return response()->json(['message' => 'Elemento no encontrado'], 404);
        }
        return response()->json($elemento);
    }

    public function destroy($id)
    {
        try {
            $deleted = $this->subelementoService->delete($id);
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
        $elementos = $this->subelementoService->listar($codigo);
        return response()->json($elementos);
    }
}
