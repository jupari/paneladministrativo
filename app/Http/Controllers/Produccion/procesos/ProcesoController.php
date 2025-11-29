<?php

namespace App\Http\Controllers\Produccion\procesos;

use App\Http\Controllers\Controller;
use App\Services\ProcesoService;
use Illuminate\Http\Request;

class ProcesoController extends Controller
{
    protected ProcesoService $procesoService;

    public function __construct(ProcesoService $procesoService)
    {
        $this->procesoService = $procesoService;
    }

    public function index(Request $request)
    {
        $elementos = $this->procesoService->getDataTable($request);

          if ($elementos) {
                return $elementos; // respuesta JSON si es AJAX
            }
        return view('produccion.procesos.index');
    }

    public function show($id)
    {
        $elemento = $this->procesoService->getById($id);
        if (!$elemento) {
            return response()->json(['message' => 'Elemento no encontrado'], 404);
        }
        return response()->json($elemento);
    }

    public function store(Request $request)
    {
        $data = $request->all();
        $elemento = $this->procesoService->create($data);
        return response()->json(['message' => 'Elemento creado exitosamente', 'data' => $elemento], 201);
    }

    public function update(Request $request, $id)
    {
        $data = $request->all();
        $elemento = $this->procesoService->update($id, $data);
        if (!$elemento) {
            return response()->json(['message' => 'Elemento no encontrado'], 404);
        }
        return response()->json($elemento);
    }

    public function destroy($id)
    {
        $deleted = $this->procesoService->delete($id);
        if (!$deleted) {
            return response()->json(['message' => 'Elemento no encontrado'], 404);
        }
        return response()->json(['message' => 'Se cambio el estado del parámetro correctamente']);
    }

    public function listar()
    {
        $elementos = $this->procesoService->listar();
        return response()->json($elementos);
    }

    public function listarxCodigo($codigo)
    {
        try {
            $elementos = $this->procesoService->listarxCodigo($codigo);
            return response()->json($elementos);
        } catch (\Throwable $th) {
            return response()->json(['error' => 'Error al obtener los elementos: ' . $th->getMessage()], 500);
        }
    }
}
