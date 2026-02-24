<?php

namespace App\Http\Controllers\Nomina;

use App\Http\Controllers\Controller;
use App\Models\Nomina\NominaConcept;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class NominaConceptoController extends Controller
{
    public function index(Request $request)
    {
        try {
            // ✅ Endpoint liviano para llenar el select de conceptos:
            // GET /admin/admin.nomina.concepts.index?list=1
            if ($request->query('list') == 1) {
                $items = NominaConcept::query()
                    ->where('is_active', 1)
                    ->orderBy('code')
                    ->get(['id','code','name'])
                    ->map(fn($c) => [
                        'id' => $c->id,
                        'text' => "{$c->code} - {$c->name}"
                    ])
                    ->values();

                return response()->json(['data' => $items]);
            }

            $query = NominaConcept::query()
                ->select(['id','code','name','scope','kind','calc_method','tax_nature','base_code','priority','is_active','created_at'])
                ->orderBy('code');

            if ($request->ajax()) {
                return DataTables::of($query)
                    ->addIndexColumn()
                    ->editColumn('is_active', fn($c) =>
                        $c->is_active
                            ? "<span class='badge badge-success'>Sí</span>"
                            : "<span class='badge badge-secondary'>No</span>"
                    )
                    ->addColumn('acciones', fn($c) =>
                        "<button class='btn btn-sm btn-warning'
                            onclick='upConcept({$c->id})'
                            data-toggle='tooltip' title='Editar'>
                            <i class='fas fa-edit'></i>
                        </button>"
                    )
                    ->rawColumns(['is_active','acciones'])
                    ->make(true);
            }
            return view('nomina.conceptos.index');
        } catch (Exception $e) {
            Log::error("Error loading NominaConcepts: {$e->getMessage()}");
            return response()->json([
                'message' => 'Error al cargar los conceptos.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'code' => ['required','string','max:40','unique:nomina_concepts,code'],
            'name' => ['required','string','max:150'],
            'scope' => ['required','in:LABORAL,CONTRATISTA,AMBOS'],
            'kind' => ['required','in:DEVENGADO,DEDUCCION,APORTE,INFORMATIVO'],
            'calc_method' => ['required','in:FIJO,PORCENTAJE,FORMULA,MANUAL'],
            'tax_nature' => ['required','in:SALARIAL,NO_SALARIAL,N_A'],
            'base_code' => ['nullable','string','max:40'],
            'priority' => ['nullable','integer','min:0','max:9999'],
            'is_active' => ['required','in:0,1'],
        ]);

        $concept = NominaConcept::create([
            ...$data,
            'priority' => $data['priority'] ?? 100,
        ]);

        return response()->json([
            'message' => 'Concepto creado correctamente.',
            'data' => $concept
        ]);
    }

    public function edit(int $id): JsonResponse
    {
        $concept = NominaConcept::findOrFail($id);
        return response()->json(['data' => $concept]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $concept = NominaConcept::findOrFail($id);

        $data = $request->validate([
            'code' => ['required','string','max:40',"unique:nomina_concepts,code,{$concept->id}"],
            'name' => ['required','string','max:150'],
            'scope' => ['required','in:LABORAL,CONTRATISTA,AMBOS'],
            'kind' => ['required','in:DEVENGADO,DEDUCCION,APORTE,INFORMATIVO'],
            'calc_method' => ['required','in:FIJO,PORCENTAJE,FORMULA,MANUAL'],
            'tax_nature' => ['required','in:SALARIAL,NO_SALARIAL,N_A'],
            'base_code' => ['nullable','string','max:40'],
            'priority' => ['nullable','integer','min:0','max:9999'],
            'is_active' => ['required','in:0,1'],
        ]);

        $concept->update([
            ...$data,
            'priority' => $data['priority'] ?? $concept->priority ?? 100,
        ]);

        return response()->json([
            'message' => 'Concepto actualizado correctamente.',
            'data' => $concept
        ]);
    }

    public function list()
    {
        $companyId = (int) session('company_id');

        $items = DB::table('nomina_concepts')
            ->where(function($q) use ($companyId){
                $q->whereNull('company_id')->orWhere('company_id',$companyId);
            })
            ->where('is_active',1)
            ->orderBy('priority')
            ->get(['id','code','name'])
            ->map(fn($c)=>['id'=>$c->id, 'text'=> "{$c->code} - {$c->name}"])
            ->values();

        return response()->json(['data'=>$items]);
    }
}
