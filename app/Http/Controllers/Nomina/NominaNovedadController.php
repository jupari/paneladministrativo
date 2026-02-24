<?php
namespace App\Http\Controllers\Nomina;

use App\Http\Controllers\Controller;
use App\Models\Empleado;
use App\Models\Nomina\NominaNovelty;
use App\Models\Nomina\NominaConcept;
use App\Models\Tercero;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;
use App\Services\Nomina\NominaDestajoNoveltyRecalculateService;
use App\Services\Nomina\NominaDestajoFromSettlementsService;
use Illuminate\Support\Facades\DB;

class NominaNovedadController extends Controller
{
    // public function index(Request $request)
    // {
    //     // Novedades NO tienen company_id en tabla (por ser polimórficas)
    //     // En un proyecto real deberías filtrar por company vía joins o guardarlo en la tabla.
    //     // Por ahora, devolvemos todo (o aplica tu filtro si ya lo tienes).

    //     $query = NominaNovelty::query()
    //         ->where('company_id', $this->getCompanyId())
    //         ->select([
    //             'id','participant_type','participant_id','link_type',
    //             'nomina_concept_id','period_start','period_end',
    //             'amount','quantity','status','created_at'
    //         ])
    //         ->orderByDesc('id');
    //     if ($request->ajax()) {
    //         return DataTables::of($query)
    //         ->addIndexColumn()
    //         ->addColumn('participant', function ($n) {
    //             $type = str_contains($n->participant_type, 'Empleado') ? 'Empleado' : 'Tercero';
    //             return "{$type} #{$n->participant_id}";
    //         })
    //         ->addColumn('concept', function ($n) {
    //             $c = NominaConcept::find($n->nomina_concept_id);
    //             return $c ? "{$c->code} - {$c->name}" : "Concepto #{$n->nomina_concept_id}";
    //         })
    //         ->addColumn('period', fn($n) => "{$n->period_start} a {$n->period_end}")
    //         ->editColumn('amount', fn($n) => number_format((float)$n->amount, 2, ',', '.'))
    //         ->editColumn('status', function ($n) {
    //             $map = [
    //                 'PENDING' => ['warning', 'Pendiente'],
    //                 'APPLIED' => ['success', 'Aplicada'],
    //                 'CANCELLED' => ['secondary', 'Cancelada'],
    //             ];
    //             [$color, $text] = $map[$n->status] ?? ['secondary', $n->status];
    //             return "<span class='badge badge-{$color}'>{$text}</span>";
    //         })
    //         ->addColumn('acciones', fn($n) =>
    //             "<button class='btn btn-sm btn-warning'
    //                 onclick='upNovelty({$n->id})'
    //                 data-toggle='tooltip' title='Editar'>
    //                 <i class='fas fa-edit'></i>
    //              </button>"
    //         )
    //         ->rawColumns(['status', 'acciones'])
    //         ->make(true);
    //     }

    //     return view('nomina.novedades.index');
    // }

    //inicio
    public function index(Request $request)
    {
        if(!$request->ajax()){
            return view('nomina.novedades.index');
        }

        $companyId = (int) session('company_id');

        $q = DB::table('nomina_novelties as n')
            ->leftJoin('empleados as e', function($j){
                $j->on('e.id','=','n.participant_id')
                ->where('n.participant_type','=', Empleado::class);
            })
            ->leftJoin('nomina_concepts as c','c.id','=','n.nomina_concept_id')
            ->where('n.company_id',$companyId);

        if($request->filled('status')){
            $q->where('n.status', $request->status);
        }
        if($request->filled('period_start')){
            $q->whereDate('n.period_start','>=',$request->period_start);
        }
        if($request->filled('period_end')){
            $q->whereDate('n.period_end','<=',$request->period_end);
        }

        $q->select([
            'n.id','n.link_type','n.status','n.period_start','n.period_end','n.quantity','n.amount','n.created_at',
            DB::raw("COALESCE(CONCAT(e.nombres,' ',e.apellidos), CONCAT(n.participant_type,'#',n.participant_id)) as participant_name"),
            DB::raw("CONCAT(c.code,' - ',c.name) as concept"),
        ]);

        return DataTables::of($q)
            ->addIndexColumn()
            ->editColumn('quantity', fn($r)=> number_format((float)$r->quantity, 4, ',', '.'))
            ->editColumn('amount', fn($r)=> number_format((float)$r->amount, 2, ',', '.'))
            ->addColumn('period', fn($r)=> "{$r->period_start} a {$r->period_end}")
            ->addColumn('status_badge', function($r){
                return match($r->status){
                    'PENDING' => "<span class='badge badge-warning'>PENDING</span>",
                    'APPLIED' => "<span class='badge badge-success'>APPLIED</span>",
                    'CANCELLED' => "<span class='badge badge-secondary'>CANCELLED</span>",
                    default => "<span class='badge badge-light'>{$r->status}</span>"
                };
            })
            ->addColumn('acciones', fn($r)=>
                    '<button class="btn btn-sm btn-primary" onclick="upNovelty('.$r->id.')" data-toggle="tooltip" title="Editar"><i class="fas fa-edit"></i></button>
                     <button class="btn btn-sm btn-secondary" onclick="openDuplicateModal('.$r->id.')" data-toggle="tooltip" title="Duplicar"><i class="fas fa-copy"></i></button>')
            ->rawColumns(['status_badge','acciones'])
            ->make(true);
    }
    //fin

    public function participants(Request $request): JsonResponse
    {
        $linkType = $request->get('link_type');
        if (!$linkType) {
            return response()->json(['data' => []]);
        }

        $companyId = $this->getCompanyId();
        $search = $request->get('search');

        [$model, $table] = $linkType === 'CONTRATISTA'
            ? [Tercero::class, 'terceros']
            : [Empleado::class, 'empleados'];

        $query = $model::query()
            ->where('company_id', $companyId);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nombres', 'like', "%{$search}%")
                    ->orWhere('apellidos', 'like', "%{$search}%")
                    ->orWhere('identificacion', 'like', "%{$search}%");
            });
        }

        $items = $query
            ->orderBy('nombres')
            ->limit(20)
            ->get()
            ->map(fn($p) => [
                'id' => $p->id,
                'text' => $this->formatParticipantLabel($p),
            ]);

        return response()->json(['data' => $items]);
    }

    public function store(Request $request): JsonResponse
    {
        $companyId = $this->getCompanyId();

        $data = $request->validate([
            'link_type' => ['required', 'in:LABORAL,CONTRATISTA'],
            'employee_ids' => ['required', 'array', 'min:1'],
            'employee_ids.*' => ['integer'],
            'nomina_concept_id' => ['required', 'exists:nomina_concepts,id'],
            'period_start' => ['required', 'date'],
            'period_end' => ['required', 'date', 'after_or_equal:period_start'],
            'quantity' => ['nullable', 'numeric'],
            'amount' => ['nullable', 'numeric'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        if (($data['quantity'] ?? null) === null && ($data['amount'] ?? null) === null) {
            return response()->json([
                'message' => 'Debe enviar amount o quantity.',
                'errors' => ['amount' => ['Debe enviar amount o quantity.']]
            ], 422);
        }

        [$participantType, $table] = $this->resolveParticipantTypeAndTable($data['link_type']);

        $existsRule = Rule::exists($table, 'id')->where('company_id', $companyId);
        validator(
            ['employee_ids' => $data['employee_ids']],
            ['employee_ids.*' => [$existsRule]]
        )->validate();

        $created = [];
        foreach (collect($data['employee_ids'])->unique() as $employeeId) {
            $created[] = NominaNovelty::create([
                'participant_type' => $participantType,
                'participant_id' => $employeeId,
                'link_type' => $data['link_type'],
                'nomina_concept_id' => $data['nomina_concept_id'],
                'period_start' => $data['period_start'],
                'period_end' => $data['period_end'],
                'quantity' => $data['quantity'] ?? null,
                'amount' => $data['amount'] ?? null,
                'description' => $data['description'] ?? null,
                'status' => 'PENDING',
                'company_id' => $companyId,
            ]);
        }

        return response()->json([
            'message' => 'Novedad asignada a ' . count($created) . ' empleado(s).',
            'data' => $created,
        ]);
    }

    public function edit(int $id): JsonResponse
    {
        $nov = NominaNovelty::where('id', $id)->where('company_id', $this->getCompanyId())->firstOrFail();
        return response()->json([
            'data' => array_merge($nov->toArray(), [
                'participant_label' => $this->formatParticipantLabelByIds($nov->participant_type, $nov->participant_id),
            ])
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $nov = NominaNovelty::where('id', $id)->where('company_id', $this->getCompanyId())->firstOrFail();

        if ($nov->status !== 'PENDING') {
            return response()->json(['message' => 'Solo se pueden editar novedades en estado PENDING.'], 422);
        }

        $companyId = $this->getCompanyId();

        $data = $request->validate([
            'link_type' => ['required', 'in:LABORAL,CONTRATISTA'],
            'employee_id' => ['required', 'integer'],
            'nomina_concept_id' => ['required', 'exists:nomina_concepts,id'],
            'period_start' => ['required', 'date'],
            'period_end' => ['required', 'date', 'after_or_equal:period_start'],
            'quantity' => ['nullable', 'numeric'],
            'amount' => ['nullable', 'numeric'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        if (($data['quantity'] ?? null) === null && ($data['amount'] ?? null) === null) {
            return response()->json([
                'message' => 'Debe enviar amount o quantity.',
                'errors' => ['amount' => ['Debe enviar amount o quantity.']]
            ], 422);
        }

        [$participantType, $table] = $this->resolveParticipantTypeAndTable($data['link_type']);
        validator(
            ['employee_id' => $data['employee_id']],
            ['employee_id' => [Rule::exists($table, 'id')->where('company_id', $companyId)]]
        )->validate();

        $nov->update([
            'participant_type' => $participantType,
            'participant_id' => $data['employee_id'],
            'link_type' => $data['link_type'],
            'nomina_concept_id' => $data['nomina_concept_id'],
            'period_start' => $data['period_start'],
            'period_end' => $data['period_end'],
            'quantity' => $data['quantity'] ?? null,
            'amount' => $data['amount'] ?? null,
            'description' => $data['description'] ?? null,
            'company_id' => $companyId,
        ]);

        return response()->json([
            'message' => 'Novedad actualizada correctamente.',
            'data' => $nov
        ]);
    }

    private function getCompanyId(): int
    {
        $companyId = session('company_id') ?? auth()->user()?->company_id;

        if (!$companyId) {
            Log::error('Usuario sin company_id', [
                'user_id' => auth()->id(),
                'session' => session()->all()
            ]);
            abort(403, 'No tienes una empresa asignada.');
        }

        return (int) $companyId;
    }

    private function resolveParticipantTypeAndTable(string $linkType): array
    {
        return $linkType === 'CONTRATISTA'
            ? [Tercero::class, 'terceros']
            : [Empleado::class, 'empleados'];
    }

    private function formatParticipantLabel($participant): string
    {
        $fullName = trim(($participant->nombres ?? '') . ' ' . ($participant->apellidos ?? ''));
        $idn = $participant->identificacion ?? $participant->id;
        return $fullName ? $fullName . " ({$idn})" : "ID {$participant->id}";
    }

    private function formatParticipantLabelByIds(string $type, int $id): string
    {
        $model = $type === Tercero::class ? Tercero::class : Empleado::class;
        $p = $model::query()
            ->where('company_id', $this->getCompanyId())
            ->find($id);
        return $p ? $this->formatParticipantLabel($p) : "Participante #{$id}";
    }

    public function recalculateDestajo(Request $request, NominaDestajoNoveltyRecalculateService $svc)
    {
        $companyId = (int) session('company_id');
        $userId = (int) auth()->id();

        $data = $request->validate([
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
        ]);

        $result = $svc->recalculate($companyId, $data['period_start'], $data['period_end'], $userId);

        return response()->json([
            'message' => "Recalculado destajo. Creadas: {$result['created']} | Actualizadas: {$result['updated']}",
            'data' => $result
        ]);
    }

    public function recalculateDestajoFromSettlements(Request $request, NominaDestajoFromSettlementsService $svc)
    {
        $companyId = (int) session('company_id');

        $data = $request->validate([
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
            'pay_run_id' => 'nullable|integer',
        ]);

        $result = $svc->recalculate(
            $companyId,
            $data['period_start'],
            $data['period_end'],
            $data['pay_run_id'] ?? null
        );

        return response()->json([
            'message' => "Destajo recalculado. Creadas: {$result['created']} | Actualizadas: {$result['updated']} | Empleados: {$result['employees']}",
            'data' => $result,
        ]);
    }

    public function duplicate(Request $request, int $id)
    {
        $companyId = (int) session('company_id');

        $data = $request->validate([
            'employee_ids' => 'required|array|min:1',
            'employee_ids.*' => 'integer|exists:empleados,id',
            'skip_existing' => 'nullable|boolean',
        ]);

        $skipExisting = (int)($data['skip_existing'] ?? 1);

        $original = DB::table('nomina_novelties')
            ->where('company_id', $companyId)
            ->where('id', $id)
            ->first();

        if(!$original){
            return response()->json(['message'=>'Novedad no encontrada'], 404);
        }

        $now = now();
        $created = 0;

        DB::transaction(function() use ($companyId, $original, $data, $skipExisting, $now, &$created){

            foreach ($data['employee_ids'] as $empId) {

                if ($skipExisting) {
                    $exists = DB::table('nomina_novelties')
                        ->where('company_id', $companyId)
                        ->where('participant_type', \App\Models\Empleado::class)
                        ->where('participant_id', (int)$empId)
                        ->where('nomina_concept_id', (int)$original->nomina_concept_id)
                        ->whereDate('period_start', $original->period_start)
                        ->whereDate('period_end', $original->period_end)
                        ->where('status', 'PENDING')
                        ->exists();

                    if ($exists) continue;
                }

                DB::table('nomina_novelties')->insert([
                    'company_id' => $companyId,
                    'participant_type' => \App\Models\Empleado::class,
                    'participant_id' => (int)$empId,
                    'link_type' => $original->link_type ?? 'LABORAL',
                    'nomina_concept_id' => (int)$original->nomina_concept_id,
                    'period_start' => $original->period_start,
                    'period_end' => $original->period_end,
                    'quantity' => (float)$original->quantity,
                    'amount' => (float)$original->amount,
                    'description' => $original->description,
                    'status' => $original->status,
                    'meta' => json_encode(array_merge((array) json_decode($original->meta ?? '[]', true), ['duplicated_from' => (int)$original->id])),
                    'source_ref' => null, // importante: no reutilizar source_ref
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                $created++;
            }
        });

        return response()->json([
            'message' => "Duplicación realizada. Creadas: {$created}",
            'created' => $created,
        ]);
    }

}
