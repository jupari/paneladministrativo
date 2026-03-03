<?php

namespace App\Http\Controllers\Contratos\Parametrizacion;

use App\Http\Controllers\Controller;
use App\Http\Requests\parametrizacionCostosRequest;
use App\Http\Requests\parametrizacionRequest;
use App\Models\Cargo;
use App\Models\Categoria;
use App\Models\ItemPropio;
use App\Models\Novedad;
use App\Models\Parametrizacion;
use App\Models\ParametrizacionCosto;
use App\Models\UnidadMedida;
use App\Services\TablaPreciosCargoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ParametrizacionController extends Controller
{

    public function index(Request $request)
    {
        try {
            $authUser= auth()->user();
            $parametrizacion = Parametrizacion::where('active',1)->orderBy('id')->get();
            $parametrizacioncostos = ParametrizacionCosto::where('active',1)->orderBy('id')->get();
            $categorias = Categoria::where('active', 1)->orderBy('nombre')->get(['id', 'nombre', 'costos'])->toArray();
            $cargos = Cargo::where('active', 1)->orderBy('nombre')->pluck('nombre', 'id')->toArray();
            $cantHorasDiarias = config('app.horasDiarias', 8);
            $novedadesDetalle = Novedad::with('detalles')
                ->where('active', 1)
                ->get()
                ->flatMap(function ($novedad) {
                    return $novedad->detalles->map(function ($detalle) use ($novedad) {
                        return [
                            'id' => $detalle->id,
                            'nombre' => $novedad->nombre . ' - ' . $detalle->nombre,
                        ];
                    });
                })
                ->toArray();
            $unidades = UnidadMedida::where('active', 1)
                                    ->pluck('nombre', 'sigla')->toArray();

            $itemsPropios = ItemPropio::where('active', 1)->orderBy('orden')->orderBy('nombre')->get();

            // $itemsPropios = ItemPropio::with('categoria', 'unidadMedida')
            //                     ->where('active', 1)
            //                     ->orderBy('nombre')
            //                     ->get(['id', 'codigo', 'nombre', 'categoria_id', 'unidad_medida']);

            return view('contratos.parametrizacion.index', [
                    'parametrizacioncostos'=>$parametrizacioncostos,
                    'parametrizacion'=>$parametrizacion,
                    'categorias'=>$categorias,
                    'cargos'=>$cargos,
                    'novedadesDetalle'=>$novedadesDetalle,
                    'user_id'=>$authUser->id,
                    'unidades'=>$unidades,
                    'itemsPropios'=>$itemsPropios,
                    'cantHorasDiarias'=>$cantHorasDiarias,
                ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al actualizar el empleado: ' . $e->getMessage()], 500);
        }
    }

    public function storeNovedades(parametrizacionRequest $request)
    {
        //Parametrizacion::truncate();

        foreach ($request->parametrizacion as $item) {
            Parametrizacion::updateOrCreate(
                [
                    'categoria_id' => $item['categoria_id'],
                    'cargo_id' => $item['cargo_id'],
                    'novedad_detalle_id' => $item['novedad_detalle_id'],
                ],
                [
                    'valor_porcentaje' => $item['valor_porcentaje'],
                    'valor_admon' => $item['valor_admon'],
                    'valor_obra' => $item['valor_obra'],
                ]
            );
        }

        return response()->json(['message' => 'Novedades guardadas correctamente']);
    }

    public function deleteNovedad($id)
    {
        $row = Parametrizacion::find($id);

        if (!$row) {
            return response()->json(['message' => 'Registro no encontrado'], 404);
        }

        $row->delete();

        return response()->json(['message' => 'Registro eliminado correctamente']);
    }

    public function storeCostos(parametrizacionCostosRequest $request)
    {

        //ParametrizacionCosto::truncate();
        $rows = $request->input('tablaCostos', []);
        if (empty($rows)) {
            return response()->json(['message' => 'No hay filas para guardar'], 422);
        }

        DB::transaction(function () use ($rows) {
            foreach ($rows as $r) {
                ParametrizacionCosto::updateOrCreate(
                    // columnas clave para buscar el registro existente:
                    ['item' => Str::of($r['item'])->trim()->upper(),
                    'unidad_medida' => (string) $r['unidad_medida']],
                    // columnas a actualizar/crear:
                    [
                        'categoria_id'  => (int) $r['categoria_id'],
                        'item_nombre'   => Str::of($r['item_nombre'])->trim()->upper(),
                        'costo_dia'     => (float) str_replace(',', '.', $r['costo_dia'] ?? 0),
                        'costo_hora'    => (float) str_replace(',', '.', $r['costo_hora'] ?? 0),
                        'active'        => (int) (!!($r['active'] ?? 1)),
                        'updated_at'    => now(),
                        'created_at'    => now(),
                    ]
                );
            }
        });

        return response()->json(['message' => 'Parametrización de costos guardada correctamente']);
    }

    public function deleteCosto($id)
    {
        $row = ParametrizacionCosto::find($id);

        if (!$row) {
            return response()->json(['message' => 'Registro no encontrado'], 404);
        }

        $row->delete();

        return response()->json(['message' => 'Registro eliminado correctamente']);
    }

    public function generarTablaPrecios(TablaPreciosCargoService $service)
    {
        $data = $service->generar(true);
        return response()->json([
            'success' => true,
            'message' => 'Tabla de precios por cargo generada correctamente.',
            'count' => count($data),
        ]);
    }

    public function tablaPreciosData(Request $request, TablaPreciosCargoService $service)
    {
        // Si ya existe la tabla persistida, puedes leer de BD (más rápido)
        // Si no existe, devolvemos calculado en caliente
        if (Schema::hasTable('cargos_tabla_precios')) {
            $rows = DB::table('cargos_tabla_precios as tp')
                ->join('cargos as c', 'c.id', '=', 'tp.cargo_id')
                ->select([
                    'tp.cargo_id',
                    'c.nombre as cargo',
                    'tp.base_costo_dia',
                    'tp.base_costo_hora',
                    'tp.hora_ordinaria',
                    'tp.recargo_nocturno',
                    'tp.hora_extra_diurna',
                    'tp.hora_extra_nocturna',
                    'tp.hora_dominical',
                    'tp.hora_extra_dominical_diurna',
                    'tp.hora_extra_dominical_nocturna',
                    'tp.valor_dia_ordinario',
                    'tp.utilidad_pct',
                    'tp.horas_diarias',
                    'tp.updated_at',
                ])
                ->orderBy('c.nombre')
                ->get();

            return response()->json(['success' => true, 'data' => $rows]);
        }

        // fallback: calcular sin persistir
        $data = $service->generar(false);
        return response()->json(['success' => true, 'data' => $data]);
    }

}
