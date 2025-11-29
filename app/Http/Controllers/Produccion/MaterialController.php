<?php

namespace App\Http\Controllers\Produccion;

use App\Http\Controllers\Controller;
use App\Services\MaterialService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MaterialController extends Controller
{

    protected MaterialService $materialService;

    public function __construct(MaterialService $materialService) {
        $this->materialService = $materialService;
    }

   public function index(Request $request)
    {
        try {
            $datatable = $this->materialService->getDataTable($request);
            if ($datatable) {
                return $datatable;
            }

            return view('produccion.materiales.index');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255|unique:prd_materiales,nombre',
            'descripcion' => 'nullable|string|max:500',
            'unidad_medida' => 'required|string|max:50',
            'active' => 'required|in:1,0',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $this->materialService->store($request->all());
            return response()->json(['success' => true, 'message' => 'Material creado exitosamente.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function edit($id)
    {
        try {
            $material = $this->materialService->getById($id);
            return response()->json(['success' => true, 'data' => $material]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => "required|string|max:255|unique:prd_materiales,nombre,$id",
            'descripcion' => 'nullable|string|max:500',
            'unidad_medida' => 'required|string|max:50',
            'active' => 'required|in:1,0',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $this->materialService->update($id, $request->all());
            return response()->json(['success' => true, 'message' => 'Material actualizado exitosamente.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $this->materialService->destroy($id);
            return response()->json(['success' => true, 'message' => 'Material eliminado con éxito']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }


    public function listar()
    {
        return $this->materialService->listar();
    }
}
