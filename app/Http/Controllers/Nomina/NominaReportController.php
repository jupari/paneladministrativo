<?php

namespace App\Http\Controllers\Nomina;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class NominaReportController extends Controller
{
    // public function index(Request $request)
    // {
    //     // Vista
    //     if (!$request->ajax()) {
    //         return view('nomina.reports.participants');
    //     }

    //     $companyId = (int) session('company_id');

    //     $payRunId = $request->query('pay_run_id');
    //     $from     = $request->query('from');
    //     $to       = $request->query('to');

    //     $q = DB::table('nomina_pay_run_participants as p')
    //         ->join('nomina_pay_runs as r', 'r.id', '=', 'p.pay_run_id')
    //         ->leftJoin('empleados as e', function ($join) {
    //             $join->on('e.id', '=', 'p.participant_id')
    //                 ->where('p.participant_type', '=', 'App\\Models\\Empleado');
    //         })
    //         ->leftJoin('terceros as t', function ($join) {
    //             $join->on('t.id', '=', 'p.participant_id')
    //                 ->where('p.participant_type', '=', 'App\\Models\\Tercero');
    //         })
    //         ->where('r.company_id', $companyId)
    //         ->select([
    //             'p.pay_run_id',
    //             'r.run_type',
    //             'r.period_start',
    //             'r.period_end',
    //             'r.pay_date',
    //             'p.link_type',
    //             'p.participant_type',
    //             'p.participant_id',
    //             DB::raw("COALESCE(CONCAT(e.nombres,' ',e.apellidos), t.nombre_establecimiento, CONCAT(p.participant_type,'#',p.participant_id)) as participante"),
    //             'p.gross_total',
    //             'p.deductions_total',
    //             'p.net_total',
    //             'p.status',
    //         ]);

    //     Log::info('Generando reporte de participantes', [
    //         'company_id' => $companyId,
    //         'pay_run_id' => $payRunId,
    //         'from' => $from,
    //         'to' => $to,
    //         'query' => $q->toSql(),
    //         'bindings' => $q->getBindings(),
    //     ]);

    //     // Filtro por un payrun específico
    //     if ($payRunId) {
    //         $q->where('p.pay_run_id', (int) $payRunId);
    //     } else {
    //         // Filtro por rango (opcional)
    //         if ($from) $q->whereDate('r.period_start', '>=', $from);
    //         if ($to)   $q->whereDate('r.period_end', '<=', $to);
    //     }

    //     // Clonar para resumen (misma consulta, sin paginado)
    //     $summaryQ = clone $q;

    //     $summary = $summaryQ->selectRaw("
    //             ROUND(COALESCE(SUM(p.gross_total),0),2) as total_devengado,
    //             ROUND(COALESCE(SUM(p.deductions_total),0),2) as total_deducciones,
    //             ROUND(COALESCE(SUM(p.net_total),0),2) as total_a_pagar
    //         ")->first();

    //     return DataTables::of($q)
    //         ->addIndexColumn()
    //         ->editColumn('run_type', function ($r) {
    //             return match ($r->run_type) {
    //                 'NOMINA' => 'Nómina (Laboral)',
    //                 'CONTRATISTAS' => 'Contratistas',
    //                 default => 'Mixto',
    //             };
    //         })
    //         ->addColumn('period', fn($r) => "{$r->period_start} a {$r->period_end}")
    //         ->editColumn('gross_total', fn($r) => number_format((float)$r->gross_total, 2, ',', '.'))
    //         ->editColumn('deductions_total', fn($r) => number_format((float)$r->deductions_total, 2, ',', '.'))
    //         ->editColumn('net_total', fn($r) => number_format((float)$r->net_total, 2, ',', '.'))
    //         ->editColumn('status', function ($r) {
    //             $map = [
    //                 'PENDING' => ['warning', 'Pendiente'],
    //                 'CALCULATED' => ['info', 'Calculado'],
    //                 'APPROVED' => ['primary', 'Aprobado'],
    //                 'PAID' => ['success', 'Pagado'],
    //                 'CLOSED' => ['dark', 'Cerrado'],
    //             ];
    //             [$color, $text] = $map[$r->status] ?? ['secondary', $r->status];
    //             return "<span class='badge badge-{$color}'>{$text}</span>";
    //         })
    //         ->with([
    //             'summary' => [
    //                 'total_devengado' => number_format((float)($summary->total_devengado ?? 0), 2, ',', '.'),
    //                 'total_deducciones' => number_format((float)($summary->total_deducciones ?? 0), 2, ',', '.'),
    //                 'total_a_pagar' => number_format((float)($summary->total_a_pagar ?? 0), 2, ',', '.'),
    //             ]
    //         ])
    //         ->rawColumns(['status'])
    //         ->make(true);
    // }

    public function index(Request $request)
    {
        if (!$request->ajax()) {
            return view('nomina.reports.participants');
        }

        $companyId = (int) session('company_id');

        $payRunId = $request->query('pay_run_id');
        $from     = $request->query('from');
        $to       = $request->query('to');

        // ✅ 1) Subquery plano (sin DataTables encima todavía)
        $base = DB::table('nomina_pay_run_participants as p')
            ->join('nomina_pay_runs as r', 'r.id', '=', 'p.pay_run_id')
            ->leftJoin('empleados as e', function ($join) {
                $join->on('e.id', '=', 'p.participant_id')
                    ->where('p.participant_type', '=', 'App\\Models\\Empleado');
            })
            ->leftJoin('terceros as t', function ($join) {
                $join->on('t.id', '=', 'p.participant_id')
                    ->where('p.participant_type', '=', 'App\\Models\\Tercero');
            })
            ->where('r.company_id', $companyId)
            ->selectRaw("
                p.pay_run_id,
                r.run_type,
                r.period_start,
                r.period_end,
                r.pay_date,
                p.link_type,
                p.participant_type,
                p.participant_id,
                COALESCE(CONCAT(e.nombres,' ',e.apellidos), t.nombre_establecimiento, CONCAT(p.participant_type,'#',p.participant_id)) as participante,
                p.gross_total,
                p.deductions_total,
                p.net_total,
                p.status
            ");

        if ($payRunId) {
            $base->where('p.pay_run_id', (int)$payRunId);
        } else {
            if ($from) $base->whereDate('r.period_start', '>=', $from);
            if ($to)   $base->whereDate('r.period_end', '<=', $to);
        }

        // ✅ 2) Envolver para que DataTables haga COUNT() sin romper SQL
        $q = DB::query()->fromSub($base, 'x');

        // ✅ 3) Resumen (usa otro query separado, también desde subquery)
        $sum = DB::query()->fromSub($base, 's')
            ->selectRaw("
                ROUND(COALESCE(SUM(gross_total),0),2) as total_devengado,
                ROUND(COALESCE(SUM(deductions_total),0),2) as total_deducciones,
                ROUND(COALESCE(SUM(net_total),0),2) as total_a_pagar
            ")->first();

        return DataTables::of($q)
            ->addIndexColumn()
            ->addColumn('period', fn($r) => "{$r->period_start} a {$r->period_end}")
            ->editColumn('run_type', function ($r) {
                return match ($r->run_type) {
                    'NOMINA' => 'Nómina (Laboral)',
                    'CONTRATISTAS' => 'Contratistas',
                    default => 'Mixto',
                };
            })
            ->editColumn('gross_total', fn($r) => number_format((float)$r->gross_total, 2, ',', '.'))
            ->editColumn('deductions_total', fn($r) => number_format((float)$r->deductions_total, 2, ',', '.'))
            ->editColumn('net_total', fn($r) => number_format((float)$r->net_total, 2, ',', '.'))
            ->editColumn('status', function ($r) {
                $map = [
                    'PENDING' => ['warning', 'Pendiente'],
                    'CALCULATED' => ['info', 'Calculado'],
                    'APPROVED' => ['primary', 'Aprobado'],
                    'PAID' => ['success', 'Pagado'],
                    'CLOSED' => ['dark', 'Cerrado'],
                ];
                [$color, $text] = $map[$r->status] ?? ['secondary', $r->status];
                return "<span class='badge badge-{$color}'>{$text}</span>";
            })
            ->with([
                'summary' => [
                    'total_devengado' => number_format((float)($sum->total_devengado ?? 0), 2, ',', '.'),
                    'total_deducciones' => number_format((float)($sum->total_deducciones ?? 0), 2, ',', '.'),
                    'total_a_pagar' => number_format((float)($sum->total_a_pagar ?? 0), 2, ',', '.'),
                ]
            ])
            ->rawColumns(['status'])
            ->make(true);
    }

    /**
     * GET /admin/admin.nomina.reports.payruns.list
     * Response: { data: [ {id, text}, ... ] }
     */
    public function payRunsList(Request $request)
    {
        $companyId = (int) session('company_id');

        $rows = DB::table('nomina_pay_runs')
            ->where('company_id', $companyId)
            ->orderByDesc('id')
            ->limit(150)
            ->get(['id', 'period_start', 'period_end', 'pay_date', 'run_type', 'status']);

        $data = $rows->map(function ($r) {
            $rt = match ($r->run_type) {
                'NOMINA' => 'Laboral',
                'CONTRATISTAS' => 'Contratistas',
                default => 'Mixto'
            };
            return [
                'id' => $r->id,
                'text' => "#{$r->id} | {$rt} | {$r->period_start} a {$r->period_end} | Pago: {$r->pay_date} | {$r->status}"
            ];
        })->values();

        return response()->json(['data' => $data]);
    }

    public function lines(Request $request)
    {
        $companyId = (int) session('company_id');

        $payRunId = (int) $request->query('pay_run_id');
        $participantType = (string) $request->query('participant_type', '');
        $participantId = (int) $request->query('participant_id');

        if (!$payRunId || !$participantId || $participantType === '') {
            return response()->json([
                'message' => 'Parámetros requeridos: pay_run_id, participant_type, participant_id'
            ], 422);
        }

        // Seguridad multi-empresa: validar que el payrun pertenezca a la compañía
        $payRun = DB::table('nomina_pay_runs')
            ->where('id', $payRunId)
            ->where('company_id', $companyId)
            ->first(['id']);

        if (!$payRun) {
            return response()->json(['message' => 'PayRun no encontrado o no autorizado.'], 403);
        }

        $q = DB::table('nomina_pay_run_lines as l')
            ->join('nomina_concepts as c', 'c.id', '=', 'l.nomina_concept_id')
            ->where('l.pay_run_id', $payRunId)
            ->where('l.participant_type', $participantType)
            ->where('l.participant_id', $participantId)
            ->select([
                'l.id',
                'c.code',
                'c.name as concept_name',
                'c.kind',
                'c.tax_nature',
                'l.quantity',
                'l.base_amount',
                'l.rate',
                'l.amount',
                'l.direction',
                'l.source',
                'l.notes',
            ])
            ->orderBy('c.priority')
            ->orderBy('l.id');

        return DataTables::of($q)
            ->addIndexColumn()
            ->editColumn('quantity', fn($r) => rtrim(rtrim(number_format((float)$r->quantity, 4, '.', ''), '0'), '.'))
            ->editColumn('base_amount', fn($r) => number_format((float)$r->base_amount, 2, ',', '.'))
            ->editColumn('rate', fn($r) => rtrim(rtrim(number_format((float)$r->rate, 6, '.', ''), '0'), '.'))
            ->editColumn('amount', fn($r) => number_format((float)$r->amount, 2, ',', '.'))
            ->editColumn('direction', function ($r) {
                if ($r->direction === 'ADD') {
                    return "<span class='badge badge-success'>ADD</span>";
                }
                if ($r->direction === 'SUB') {
                    return "<span class='badge badge-danger'>SUB</span>";
                }
                return "<span class='badge badge-secondary'>{$r->direction}</span>";
            })
            ->editColumn('source', function ($r) {
                $map = [
                    'ENGINE' => ['info', 'ENGINE'],
                    'NOVELTY' => ['warning', 'NOVELTY'],
                    'MANUAL' => ['secondary', 'MANUAL'],
                ];
                [$color, $text] = $map[$r->source] ?? ['secondary', $r->source];
                return "<span class='badge badge-{$color}'>{$text}</span>";
            })
            ->rawColumns(['direction', 'source'])
            ->make(true);
    }
}
