<?php

namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Models\ProductoPropiedad;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Exception;

class ProductoPropiedadController extends Controller
{
    public function index($productoId)
    {
        try {
            $productoPropiedades = ProductoPropiedad::where('producto_id', $productoId)->get();
            return response()->json($productoPropiedades);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }


    public function store(Request $request)
    {
        try {
            $producto = ProductoPropiedad::updateOrCreate(
                ['id' => $request->id],
                $request->all()
            );
            return response()->json(['status' => 'ok', 'data' => $producto, 'message' => 'Propiedad guardada correctamente']);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function destroy($id)
    {
        $propiedad=ProductoPropiedad::findOrFail($id);
        $propiedad->active = false;
        $propiedad->save();
        return response()->json(['status' => 'ok', 'message' => 'Propiedad Desactivada correctamente']);
    }
}
