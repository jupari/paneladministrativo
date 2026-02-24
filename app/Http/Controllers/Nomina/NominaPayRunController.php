<?php

namespace App\Http\Controllers\Nomina;

use App\Http\Controllers\Controller;
use App\Http\Requests\Nomina\StoreNominaPayRunRequest;
use App\Models\Nomina\NominaPayRun;
use App\Services\Nomina\NominaEngineService;
use App\Services\Nomina\NominaPayRunService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\DataTables;

class NominaPayRunController extends Controller
{
    public function __construct(
        private readonly NominaPayRunService $service,
        private readonly NominaEngineService $engine
    ) {}

    /**
     * Obtener company_id del usuario autenticado de forma segura
     */
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

   public function index(Request $request)
    {
        $companyId = $this->getCompanyId();

        $query = NominaPayRun::query()
            ->where('company_id', $companyId)
            ->select(['id', 'run_type', 'period_start', 'period_end', 'pay_date', 'status', 'created_at'])
            ->orderByDesc('id');
        if ($request->ajax()) {
            return DataTables::of($query)
                    ->addIndexColumn()
                    ->addColumn('period', fn($r) => "{$r->period_start} a {$r->period_end}")
                    ->editColumn('run_type', function ($r) {
                        return match ($r->run_type) {
                            'NOMINA' => 'Nómina (Laboral)',
                            'CONTRATISTAS' => 'Contratistas',
                            default => 'Mixto',
                        };
                    })
                    ->editColumn('status', function ($r) {
                        $map = [
                            'DRAFT' => ['secondary', 'Borrador'],
                            'CALCULATED' => ['info', 'Calculado'],
                            'APPROVED' => ['primary', 'Aprobado'],
                            'PAID' => ['success', 'Pagado'],
                            'CLOSED' => ['dark', 'Cerrado'],
                        ];
                        [$color, $text] = $map[$r->status] ?? ['secondary', $r->status];
                        return "<span class='badge badge-{$color}'>{$text}</span>";
                    })
                    ->addColumn('acciones', function ($r) {
                        $btnEdit = "<button class='btn btn-sm btn-warning mr-1'
                                        onclick='upPayRun({$r->id})'
                                        data-toggle='tooltip' title='Editar'>
                                        <i class='fas fa-edit'></i>
                                    </button>";

                        $btnCalc = "<button class='btn btn-sm btn-primary'
                                        onclick='calculatePayRun({$r->id})'
                                        data-toggle='tooltip' title='Calcular'>
                                        <i class='fas fa-calculator'></i>
                                    </button>";

                        return $btnEdit . $btnCalc;
                    })
                    ->rawColumns(['status', 'acciones'])
                    ->make(true);

        }

        return view('nomina.procesos_nomina.index');
    }


    public function store(StoreNominaPayRunRequest $request)
    {
        $companyId = $this->getCompanyId();
        $payRun = $this->service->create($request->validated(), $companyId, auth()->id());

        $this->service->attachParticipants(
            $payRun,
            (bool)$request->input('include_laboral', true),
            (bool)$request->input('include_contratistas', true)
        );

        return response()->json([
            'message' => 'Periodo creado y participantes incluidos.'
        ]);
    }

    public function show($id)
    {
        $payRun = NominaPayRun::with(['participants','lines.concept'])
            ->where('company_id', $this->getCompanyId())
            ->findOrFail($id);

        return response()->json([
            'data' => $payRun
        ]);
    }

    public function update(StoreNominaPayRunRequest $request, $id)
    {
        $companyId = $this->getCompanyId();
        $payRun = NominaPayRun::where('company_id', $companyId)->findOrFail($id);

        $this->service->update($payRun, $request->validated(), auth()->id());

        return response()->json([
            'message' => 'Periodo actualizado correctamente.'
        ]);
    }

    public function calculate($id)
    {
        try {
            $payRun = NominaPayRun::where('company_id', $this->getCompanyId())->findOrFail($id);
            Log::info('Iniciando cálculo de NominaPayRun', [
                'pay_run_id' => $payRun->id,
                'company_id' => $payRun->company_id,
                'status' => $payRun->status,
                'user_id' => auth()->id()
            ]);
            $this->engine->calculate($payRun);

            return response()->json([
                'message' => 'Cálculo realizado correctamente.'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error al calcular: ' . $e->getMessage()
            ], 500);
        }
    }

    public function list()
    {
        $companyId = (int) session('company_id');

        $items = DB::table('nomina_pay_runs')
            ->where('company_id',$companyId)
            ->orderByDesc('id')
            ->limit(50)
            ->get(['id','run_type','period_start','period_end','status'])
            ->map(fn($r)=>[
                'id'=>$r->id,
                'text'=> "#{$r->id} | {$r->run_type} | {$r->period_start} a {$r->period_end} | {$r->status}"
            ])->values();

        return response()->json(['data'=>$items]);
    }
}
