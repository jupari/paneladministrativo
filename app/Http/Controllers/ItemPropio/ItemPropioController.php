<?php

namespace App\Http\Controllers\ItemPropio;

use App\Exceptions\AppValidationException;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreItemPropioRequest;
use App\Http\Requests\UpdateItemPropioRequest;
use App\Models\Categoria;
use App\Models\ItemPropio;
use App\Models\UnidadMedida;
use App\Services\ItemPropioService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ItemPropioController extends Controller
{
    // public function store(Request $request)
    // {
    //     $request->validate([
    //         'nombre' => 'required|string|max:255',
    //         'codigo' => 'required|string|max:100|unique:items_propios,codigo',
    //         'unidadmedida_id' => 'required|exists:unidades_medida,sigla',
    //     ]);

    //     $item = ItemPropio::create([
    //         'nombre' => $request->nombre,
    //         'codigo' => $request->codigo,
    //         'unidad_medida_id' => $request->unidadmedida_id,
    //         'active' => 1,
    //     ]);

    //     return response()->json([
    //         'id' => $item->id,
    //         'nombre' => $item->nombre,
    //     ]);
    // }

    public function __construct(private readonly ItemPropioService $service) {}

    // Vista principal con grilla
    public function index()
    {
        $categorias = Categoria::where('active',1)->orderBy('nombre')->get(['id','nombre']);
        $unidades = UnidadMedida::orderBy('nombre')->get(['sigla','nombre']);
        return view('contratos.items.itempropio', compact('categorias','unidades'));
    }

    // JSON para Tabulator
    public function listar(): JsonResponse
    {
        return response()->json($this->service->listar());
    }

    public function store(StoreItemPropioRequest $request): JsonResponse
    {
        try {
            $item = $this->service->crear($request->validated());
            return response()->json($item, 201);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function update(UpdateItemPropioRequest $request, ItemPropio $item_propio): JsonResponse
    {
        try {
            $item = $this->service->actualizar($item_propio, $request->validated());
            return response()->json($item);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    public function destroy(ItemPropio $item_propio)
    {
        try {
            $this->service->eliminar($item_propio);
            return response()->json(['message' => 'Ítem eliminado']);
        } catch (AppValidationException $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        } catch (\Throwable $e) {
            report($e);
            return response()->json(['message' => 'Error al eliminar'], 500);
        }
    }

}
