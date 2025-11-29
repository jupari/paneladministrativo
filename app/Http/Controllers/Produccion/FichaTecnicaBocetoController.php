<?php

namespace App\Http\Controllers\Produccion;

use App\Http\Controllers\Controller;
use App\Services\FichaTecnicaBocetoService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FichaTecnicaBocetoController extends Controller
{
    protected FichaTecnicaBocetoService $fichaTecnicaBocetoService;

    public function __construct(FichaTecnicaBocetoService $fichaTecnicaBocetoService) {
        $this->fichaTecnicaBocetoService = $fichaTecnicaBocetoService;
    }

    public function store(Request $request)
    {
        $request->validate([
            'fichatecnica_id' => 'required|exists:prd_fichas_tecnicas,id',
            'nombre.*' => 'nullable|string|max:255',
            'archivo.*' => 'nullable|file|mimes:jpg,jpeg,png,svg|max:2048',
            'observacion.*'=>'nullable|string|max:500'
        ]);

        try {
            $this->fichaTecnicaBocetoService->store($request->all());
            return response()->json(['status'=>'Ok','message'=>'Bocetos guardados correctamente.']);
        } catch (Exception $e) {
            return response()->json(['message'=>'Error al guardar el boceto.' . $e->getMessage()]);
        }

    }

    public function destroy($id)
    {
        try {
            $this->fichaTecnicaBocetoService->destroy($id);
            return response()->json(['message'=>'Boceto eliminado correctamente.']);
        } catch (Exception $e) {
            return response()->json(['message'=>'Error al eiminar el registro boceto. '.$e->getMessage()]);
        }
    }
}
