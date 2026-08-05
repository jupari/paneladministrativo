<?php

namespace App\Http\Controllers\Terceros;

use App\Http\Controllers\Controller;
use App\Services\UbicacionService;
use Illuminate\Http\Request;

class UbicacionController extends Controller
{
    protected UbicacionService $ubicacionService;

    public function __construct(UbicacionService $ubicacionService)
    {
        $this->middleware('can:ciudades.index')->only(['index', 'indexDepartamentos', 'indexPaises', 'paisesSelect']);
        $this->middleware('can:ciudades.create')->only(['store', 'storeDepartamento', 'storePais']);
        $this->middleware('can:ciudades.edit')->only(['edit', 'update', 'editDepartamento', 'updateDepartamento', 'editPais', 'updatePais']);
        $this->middleware('can:ciudades.destroy')->only(['destroyCiudad', 'destroyDepartamento', 'destroyPais']);

        $this->ubicacionService = $ubicacionService;
    }

    // Ciudades

    public function index(Request $request)
    {
        if ($request->ajax()) {
            return $this->ubicacionService->listarCiudades($request);
        }

        return view('terceros.ciudades.index', [
            'paises' => $this->ubicacionService->paisesParaSelect(),
        ]);
    }

    public function store(Request $request)
    {
        $result = $this->ubicacionService->guardarCiudad($request);

        return response()->json($result, $result['code'] ?? 200);
    }

    public function edit($id)
    {
        $result = $this->ubicacionService->editarCiudad($id);

        return response()->json($result, $result['code'] ?? 200);
    }

    public function update(Request $request, $id)
    {
        $result = $this->ubicacionService->actualizarCiudad($request, $id);

        return response()->json($result, $result['code'] ?? 200);
    }

    public function destroyCiudad($id)
    {
        $result = $this->ubicacionService->eliminarCiudad($id);

        return response()->json($result, $result['code'] ?? 200);
    }

    public function paisesSelect()
    {
        return response()->json(['success' => true, 'data' => $this->ubicacionService->paisesParaSelect()]);
    }

    // Departamentos

    public function indexDepartamentos(Request $request)
    {
        return $this->ubicacionService->listarDepartamentos($request);
    }

    public function storeDepartamento(Request $request)
    {
        $result = $this->ubicacionService->guardarDepartamento($request);

        return response()->json($result, $result['code'] ?? 200);
    }

    public function editDepartamento($id)
    {
        $result = $this->ubicacionService->editarDepartamento($id);

        return response()->json($result, $result['code'] ?? 200);
    }

    public function updateDepartamento(Request $request, $id)
    {
        $result = $this->ubicacionService->actualizarDepartamento($request, $id);

        return response()->json($result, $result['code'] ?? 200);
    }

    public function destroyDepartamento($id)
    {
        $result = $this->ubicacionService->eliminarDepartamento($id);

        return response()->json($result, $result['code'] ?? 200);
    }

    // Países

    public function indexPaises(Request $request)
    {
        return $this->ubicacionService->listarPaises($request);
    }

    public function storePais(Request $request)
    {
        $result = $this->ubicacionService->guardarPais($request);

        return response()->json($result, $result['code'] ?? 200);
    }

    public function editPais($id)
    {
        $result = $this->ubicacionService->editarPais($id);

        return response()->json($result, $result['code'] ?? 200);
    }

    public function updatePais(Request $request, $id)
    {
        $result = $this->ubicacionService->actualizarPais($request, $id);

        return response()->json($result, $result['code'] ?? 200);
    }

    public function destroyPais($id)
    {
        $result = $this->ubicacionService->eliminarPais($id);

        return response()->json($result, $result['code'] ?? 200);
    }
}
