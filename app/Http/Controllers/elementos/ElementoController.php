<?php

namespace App\Http\Controllers\elementos;

use App\Http\Controllers\Controller;
use App\Services\ElementoService;
use Illuminate\Http\Request;

class ElementoController extends Controller
{
    protected ElementoService $elementoService;

    public function __construct(ElementoService $elementoService)
    {
        $this->elementoService = $elementoService;
    }

    public function index(Request $request)
    {
        $elementos = $this->elementoService->getDataTable($request);

          if ($elementos) {
                return $elementos; // respuesta JSON si es AJAX
            }
        return view('admin.configuracion.index');
    }

    public function show($id)
    {
        $elemento = $this->elementoService->getById($id);
        if (!$elemento) {
            return response()->json(['message' => 'Elemento no encontrado'], 404);
        }
        return response()->json($elemento);
    }

    public function store(Request $request)
    {
        $data = $request->all();
        $elemento = $this->elementoService->create($data);
        return response()->json(['message' => 'Elemento creado exitosamente', 'data' => $elemento], 201);
    }

    public function update(Request $request, $id)
    {
        $data = $request->all();
        $elemento = $this->elementoService->update($id, $data);
        if (!$elemento) {
            return response()->json(['message' => 'Elemento no encontrado'], 404);
        }
        return response()->json($elemento);
    }

    public function destroy($id)
    {
        $deleted = $this->elementoService->delete($id);
        if (!$deleted) {
            return response()->json(['message' => 'Elemento no encontrado'], 404);
        }
        return response()->json(['message' => 'Se cambio el estado del parámetro correctamente']);
    }

    public function listar($codigo = null)
    {
        $elementos = $this->elementoService->listar($codigo);
        return response()->json($elementos);
    }

    public function listarxCodigo($codigo)
    {
        try {
            $elementos = $this->elementoService->listarxCodigo($codigo);
            return response()->json($elementos);
        } catch (\Throwable $th) {
            return response()->json(['error' => 'Error al obtener los elementos: ' . $th->getMessage()], 500);
        }
    }
}
