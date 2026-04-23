<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Concepto;
use Exception;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class ConceptoController extends Controller
{
    use AuthorizesRequests;

    public function __construct()
    {
        $this->middleware('can:conceptos.index')->only('index');
        $this->middleware('can:conceptos.create')->only('store');
        $this->middleware('can:conceptos.edit')->only('edit', 'update', 'toggleActive');
        $this->middleware('can:conceptos.delete')->only('destroy');
    }

    public function index(Request $request)
    {
        try {
            if ($request->ajax()) {
                $conceptos = Concepto::select(['id', 'nombre', 'tipo', 'porcentaje_defecto', 'active'])->get();

                return DataTables::of($conceptos)
                    ->addIndexColumn()
                    ->editColumn('tipo', fn($row) => $row->tipo ?? '-')
                    ->editColumn('porcentaje_defecto', fn($row) => number_format($row->porcentaje_defecto, 2) . '%')
                    ->editColumn('active', function ($row) {
                        $badge = $row->active
                            ? "<span class='badge badge-success'>Activo</span>"
                            : "<span class='badge badge-secondary'>Inactivo</span>";
                        return $badge;
                    })
                    ->addColumn('acciones', function ($row) {
                        $btns = '';
                        if (Auth::user()->can('conceptos.edit')) {
                            $btns .= '<button type="button" onclick="upConcepto(' . $row->id . ')" class="btn btn-warning btn-circle btn-sm mr-1" title="Editar"><i class="fas fa-pencil-alt"></i></button>';
                            $toggleIcon = $row->active ? 'fa-toggle-on text-success' : 'fa-toggle-off text-secondary';
                            $toggleTitle = $row->active ? 'Desactivar' : 'Activar';
                            $btns .= '<button type="button" onclick="toggleConcepto(' . $row->id . ')" class="btn btn-light btn-circle btn-sm mr-1" title="' . $toggleTitle . '"><i class="fas ' . $toggleIcon . '"></i></button>';
                        }
                        if (Auth::user()->can('conceptos.delete')) {
                            $btns .= '<button type="button" onclick="deleteConcepto(' . $row->id . ')" class="btn btn-danger btn-circle btn-sm" title="Eliminar"><i class="fas fa-trash"></i></button>';
                        }
                        return $btns;
                    })
                    ->rawColumns(['active', 'acciones'])
                    ->make(true);
            }

            return view('parametrizacion.conceptos.index');
        } catch (Exception $e) {
            return response()->json(['error' => 'Error al obtener los conceptos: ' . $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validation = Validator::make($request->all(), [
                'nombre' => 'required|string|max:100|unique:conceptos,nombre',
                'tipo' => 'nullable|string|max:50',
                'porcentaje_defecto' => 'nullable|numeric|min:0|max:100',
            ]);

            if ($validation->fails()) {
                return response()->json(['errors' => $validation->errors()], 422);
            }

            $concepto = Concepto::create([
                'nombre' => $request->nombre,
                'tipo' => $request->tipo ?: null,
                'porcentaje_defecto' => $request->porcentaje_defecto ?? 0,
                'active' => true,
            ]);

            if ($concepto) {
                return response()->json(['message' => 'El concepto se creó con éxito.'], 200);
            }

            return response()->json(['message' => 'No fue posible crear el concepto.'], 502);
        } catch (Exception $e) {
            return response()->json(['error' => 'Error al guardar el concepto: ' . $e->getMessage()], 500);
        }
    }

    public function edit(int $id)
    {
        try {
            $concepto = Concepto::select(['id', 'nombre', 'tipo', 'porcentaje_defecto', 'active'])->findOrFail($id);
            return response()->json(['concepto' => $concepto, 'message' => 'Concepto obtenido con éxito.'], 200);
        } catch (Exception $e) {
            return response()->json(['error' => 'Error al obtener el concepto: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request, int $id)
    {
        try {
            $validation = Validator::make($request->all(), [
                'nombre' => 'required|string|max:100|unique:conceptos,nombre,' . $id,
                'tipo' => 'nullable|string|max:50',
                'porcentaje_defecto' => 'nullable|numeric|min:0|max:100',
            ]);

            if ($validation->fails()) {
                return response()->json(['errors' => $validation->errors()], 422);
            }

            $concepto = Concepto::findOrFail($id);
            $concepto->update([
                'nombre' => $request->nombre,
                'tipo' => $request->tipo ?: null,
                'porcentaje_defecto' => $request->porcentaje_defecto ?? 0,
            ]);

            return response()->json(['message' => 'El concepto se actualizó con éxito.'], 200);
        } catch (Exception $e) {
            return response()->json(['error' => 'Error al actualizar el concepto: ' . $e->getMessage()], 500);
        }
    }

    public function toggleActive(int $id)
    {
        try {
            $concepto = Concepto::findOrFail($id);
            $concepto->update(['active' => !$concepto->active]);
            $status = $concepto->active ? 'activado' : 'desactivado';
            return response()->json(['message' => "El concepto fue {$status} con éxito."], 200);
        } catch (Exception $e) {
            return response()->json(['error' => 'Error al cambiar el estado: ' . $e->getMessage()], 500);
        }
    }

    public function destroy(int $id)
    {
        try {
            $concepto = Concepto::findOrFail($id);

            if ($concepto->cotizacionConceptos()->exists()) {
                return response()->json(['error' => 'No se puede eliminar: el concepto está en uso en cotizaciones.'], 409);
            }

            $concepto->delete();
            return response()->json(['message' => 'El concepto se eliminó con éxito.'], 200);
        } catch (Exception $e) {
            return response()->json(['error' => 'Error al eliminar el concepto: ' . $e->getMessage()], 500);
        }
    }
}
