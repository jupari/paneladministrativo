<?php

namespace App\Http\Controllers\Contratos\Categorias;

use App\Http\Controllers\Controller;
use App\Services\CategoriaService;
use Illuminate\Http\Request;

class CategoriaController extends Controller
{
    protected CategoriaService $categoriaService;

    public function __construct(CategoriaService $categoriaService)
    {
        $this->categoriaService = $categoriaService;
    }


    public function index(Request $request){

        return $this->categoriaService->listar($request);
    }

    public function store(Request $request)
    {
        $result = $this->categoriaService->guardar($request);
        return response()->json($result, $result['code'] ?? 200);
    }

    public function edit($id)
    {
        return response()->json($this->categoriaService->editar($id));
    }

    public function update(Request $request, $id)
    {
        $result = $this->categoriaService->actualizar($request, $id);
        return response()->json($result, $result['code'] ?? 200);
    }

    public function destroy($id)
    {
        return response()->json($this->categoriaService->eliminar($id));
    }


}
