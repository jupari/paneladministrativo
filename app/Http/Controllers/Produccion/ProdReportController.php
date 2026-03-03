<?php

namespace App\Http\Controllers\Produccion;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class ProdReportController extends Controller
{
    public function summaryByPeriod(Request $request)
    {
        if (!$request->ajax()) {
            return view('produccion.reports.summary_period');
        }

        $companyId = (int) session('company_id');
        $from = (string) $request->query('from', '');
        $to = (string) $request->query('to', '');

        $base = DB::table('prod_production_logs as l')
            ->join('prod_orders as o', 'o.id', '=', 'l.order_id')
            ->join('inv_productos as p', 'p.id', '=', 'o.product_id')
            ->where('l.company_id', $companyId)
            ->when($from !== '', fn($q) => $q->whereDate('l.work_date', '>=', $from))
            ->when($to !== '', fn($q) => $q->whereDate('l.work_date', '<=', $to))
            ->groupBy('l.order_id', 'o.code', 'o.objective_qty', 'p.codigo', 'p.nombre')
            ->selectRaw("
                l.order_id,
                o.code as order_code,
                o.objective_qty,
                CONCAT(p.codigo,' - ',p.nombre) as producto,
                SUM(l.qty) as total_qty,
                SUM(l.rejected_qty) as total_rejected,
                SUM(l.qty - l.rejected_qty) as total_accepted
            ");

        $q = DB::query()->fromSub($base, 'x');

        $summary = DB::table('prod_production_logs as l')
            ->where('l.company_id', $companyId)
            ->when($from !== '', fn($q) => $q->whereDate('l.work_date', '>=', $from))
            ->when($to !== '', fn($q) => $q->whereDate('l.work_date', '<=', $to))
            ->selectRaw("
                ROUND(COALESCE(SUM(l.qty),0),2) as total_qty,
                ROUND(COALESCE(SUM(l.rejected_qty),0),2) as total_rejected,
                ROUND(COALESCE(SUM(l.qty - l.rejected_qty),0),2) as total_accepted,
                COUNT(DISTINCT l.order_id) as total_orders,
                COUNT(DISTINCT l.employee_id) as total_workers
            ")
            ->first();

        $totalQty = (float) ($summary->total_qty ?? 0);
        $totalRejected = (float) ($summary->total_rejected ?? 0);
        $rejectRate = $totalQty > 0 ? round(($totalRejected / $totalQty) * 100, 2) : 0;

        return DataTables::of($q)
            ->addIndexColumn()
            ->editColumn('objective_qty', fn($r) => number_format((float) $r->objective_qty, 2, ',', '.'))
            ->editColumn('total_qty', fn($r) => number_format((float) $r->total_qty, 2, ',', '.'))
            ->editColumn('total_rejected', fn($r) => number_format((float) $r->total_rejected, 2, ',', '.'))
            ->editColumn('total_accepted', fn($r) => number_format((float) $r->total_accepted, 2, ',', '.'))
            ->addColumn('progress_pct', function ($r) {
                $objective = (float) $r->objective_qty;
                $accepted = (float) $r->total_accepted;
                $pct = $objective > 0 ? min(100, round(($accepted / $objective) * 100, 2)) : 0;
                return number_format($pct, 2, ',', '.') . '%';
            })
            ->addColumn('reject_pct', function ($r) {
                $qty = (float) $r->total_qty;
                $rejected = (float) $r->total_rejected;
                $pct = $qty > 0 ? round(($rejected / $qty) * 100, 2) : 0;
                return number_format($pct, 2, ',', '.') . '%';
            })
            ->with([
                'summary' => [
                    'total_qty' => number_format($totalQty, 2, ',', '.'),
                    'total_rejected' => number_format($totalRejected, 2, ',', '.'),
                    'total_accepted' => number_format((float) ($summary->total_accepted ?? 0), 2, ',', '.'),
                    'reject_rate' => number_format($rejectRate, 2, ',', '.') . '%',
                    'total_orders' => (int) ($summary->total_orders ?? 0),
                    'total_workers' => (int) ($summary->total_workers ?? 0),
                ],
            ])
            ->make(true);
    }

    public function operatingCostsByPeriod(Request $request)
    {
        if (!$request->ajax()) {
            return view('produccion.reports.operating_costs_period');
        }

        $companyId = (int) session('company_id');
        $from = (string) $request->query('from', '');
        $to = (string) $request->query('to', '');

        $lineBase = DB::table('prod_production_logs as l')
            ->join('prod_orders as o', 'o.id', '=', 'l.order_id')
            ->join('inv_productos as p', 'p.id', '=', 'o.product_id')
            ->join('prod_order_operations as oo', 'oo.id', '=', 'l.order_operation_id')
            ->join('prod_operations as op', 'op.id', '=', 'oo.operation_id')
            ->where('l.company_id', $companyId)
            ->when($from !== '', fn($q) => $q->whereDate('l.work_date', '>=', $from))
            ->when($to !== '', fn($q) => $q->whereDate('l.work_date', '<=', $to))
            ->selectRaw("
                l.order_id,
                o.code as order_code,
                CONCAT(p.codigo,' - ',p.nombre) as producto,
                oo.operation_id,
                CONCAT(op.code,' - ',op.name) as operacion,
                l.qty,
                l.rejected_qty,
                (l.qty - l.rejected_qty) as accepted_qty,
                COALESCE((
                    SELECT r.amount
                    FROM prod_operation_product_rates r
                    WHERE r.company_id = l.company_id
                    AND r.product_id = o.product_id
                    AND r.operation_id = oo.operation_id
                    AND r.is_active = 1
                    AND (r.valid_from IS NULL OR DATE(r.valid_from) <= DATE(l.work_date))
                    AND (r.valid_to IS NULL OR DATE(r.valid_to) >= DATE(l.work_date))
                    ORDER BY r.valid_from DESC, r.id DESC
                    LIMIT 1
                ), 0) as rate
            ");

        $base = DB::query()->fromSub($lineBase, 'x')
            ->groupBy('x.order_id', 'x.order_code', 'x.producto', 'x.operation_id', 'x.operacion')
            ->selectRaw("
                x.order_id,
                x.order_code,
                x.producto,
                x.operation_id,
                x.operacion,
                SUM(x.qty) as total_qty,
                SUM(x.rejected_qty) as total_rejected,
                SUM(x.accepted_qty) as total_accepted,
                ROUND(AVG(x.rate), 2) as avg_rate,
                ROUND(SUM(x.accepted_qty * x.rate), 2) as labor_cost,
                ROUND(SUM(x.rejected_qty * x.rate), 2) as rejected_cost
            ");

        $q = DB::query()->fromSub($base, 'z');

        $summary = DB::query()->fromSub($lineBase, 's')
            ->selectRaw("
                ROUND(COALESCE(SUM(s.accepted_qty * s.rate),0),2) as total_labor_cost,
                ROUND(COALESCE(SUM(s.rejected_qty * s.rate),0),2) as total_rejected_cost,
                ROUND(COALESCE(SUM(s.qty),0),2) as total_qty,
                ROUND(COALESCE(SUM(s.accepted_qty),0),2) as total_accepted,
                ROUND(COALESCE(SUM(s.rejected_qty),0),2) as total_rejected,
                COUNT(DISTINCT s.order_id) as total_orders,
                COUNT(DISTINCT s.operation_id) as total_operations
            ")
            ->first();

        $totalAccepted = (float) ($summary->total_accepted ?? 0);
        $totalLaborCost = (float) ($summary->total_labor_cost ?? 0);
        $realUnitCost = $totalAccepted > 0 ? round($totalLaborCost / $totalAccepted, 2) : 0;

        return DataTables::of($q)
            ->addIndexColumn()
            ->editColumn('total_qty', fn($r) => number_format((float) $r->total_qty, 2, ',', '.'))
            ->editColumn('total_rejected', fn($r) => number_format((float) $r->total_rejected, 2, ',', '.'))
            ->editColumn('total_accepted', fn($r) => number_format((float) $r->total_accepted, 2, ',', '.'))
            ->editColumn('avg_rate', fn($r) => number_format((float) $r->avg_rate, 2, ',', '.'))
            ->editColumn('labor_cost', fn($r) => number_format((float) $r->labor_cost, 2, ',', '.'))
            ->editColumn('rejected_cost', fn($r) => number_format((float) $r->rejected_cost, 2, ',', '.'))
            ->addColumn('unit_cost', function ($r) {
                $accepted = (float) $r->total_accepted;
                $cost = (float) $r->labor_cost;
                $unit = $accepted > 0 ? round($cost / $accepted, 2) : 0;
                return number_format($unit, 2, ',', '.');
            })
            ->with([
                'summary' => [
                    'total_labor_cost' => number_format($totalLaborCost, 2, ',', '.'),
                    'total_rejected_cost' => number_format((float) ($summary->total_rejected_cost ?? 0), 2, ',', '.'),
                    'total_qty' => number_format((float) ($summary->total_qty ?? 0), 2, ',', '.'),
                    'total_accepted' => number_format($totalAccepted, 2, ',', '.'),
                    'total_rejected' => number_format((float) ($summary->total_rejected ?? 0), 2, ',', '.'),
                    'real_unit_cost' => number_format($realUnitCost, 2, ',', '.'),
                    'total_orders' => (int) ($summary->total_orders ?? 0),
                    'total_operations' => (int) ($summary->total_operations ?? 0),
                ],
            ])
            ->make(true);
    }
}
