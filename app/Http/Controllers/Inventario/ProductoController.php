<?php

namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductoRequest;
use App\Models\Producto;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Exception;

class ProductoController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:productos.index')->only('index');
        $this->middleware('can:productos.create')->only('store');
        $this->middleware('can:productos.edit')->only('update');
        $this->middleware('can:productos.destroy')->only('destroy');
    }
    public function index(Request $request)
    {

        $data = Producto::with('productosPropiedades')->orderBy('id', 'desc')->get();
        if ($request->ajax()) {
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('id', fn($td) => $td->id)
                ->addColumn('tipo_prenda', fn($td) => $td->tipo_prenda)
                ->addColumn('codigo', fn($td) => $td->codigo)
                ->addColumn('nombre', fn($td) => $td->nombre)
                ->addColumn('unidad', fn($td) => $td->unidad_medida)
                ->addColumn('marca', fn($td) => $td->marca)
                ->addColumn('categoria', fn($td) => $td->categoria)
                ->addColumn('subcategoria', fn($td) => $td->subcategoria)
                ->addColumn('estado', function ($td) {
                        if ($td->active==1) {
                            return '<div  class="col-6 text-center rounded-pill">
                                        <span class="badge bg-success">Activo</span>
                                    </div>';
                                }
                        if ($td->active==0) {
                            return '<div class="col-6 text-center rounded-pill">
                                    <span class="badge bg-warning">Inactivo</span>
                                </div>';
                        }
                    })
                ->addColumn('acciones', function ($td) {
                        $href = '';
                        if (auth()->user()->can('productos.edit')) {
                            $href .= '<button type="button" class="btn btn-primary btn-circle btn-sm editar" data-toggle="tooltip" data-placement="top" title="Editar Producto" data-id="' . $td->id . '"><i class="fas fa-pencil-alt"></i></button>&nbsp;';
                        }
                        if (auth()->user()->can('productos.destroy')) {
                            $href .= '<button type="button" class="btn btn-danger btn-circle btn-sm eliminar" data-toggle="tooltip" data-placement="top" title="Eliminar Producto" data-id="' . $td->id . '"><i class="fas fa-trash-alt"></i></button>';
                        }
                        return $href;
                    })
                ->rawColumns(['id','tipo_prenda','codigo','nombre','unidad','marca','categoria','subcategoria','estado','acciones'])
                ->make(true);
        }

        return view('inventario.producto.index');
    }


    public function store(StoreProductoRequest $request)
    {
        try {
            $producto = Producto::updateOrCreate(
                ['id' => $request->id],
                $request->all()
            );
            return response()->json(['status' => 'ok', 'data' => $producto, 'message' => 'Producto guardado correctamente']);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
    public function edit($id)
    {
        $producto = Producto::findOrFail($id);
        return response()->json(['status' => 'ok', 'data' => $producto]);
    }

    public function update(Request $request, $id)
    {
        $producto = Producto::findOrFail($id);
        $producto->update($request->all());
        return response()->json(['status' => 'ok', 'data' => $producto, 'message' => 'Producto actualizado correctamente']);
    }

    public function destroy($id)
    {
        Producto::findOrFail($id)->delete();
        return response()->json(['status' => 'ok', 'message' => 'Producto eliminado correctamente'  ]);
    }

    public function listar()
    {
        $productos = Producto::where('active', true)->get(['id', 'codigo', 'nombre']);
        return response()->json(['success' => true, 'data' => $productos], 200);
    }

}
