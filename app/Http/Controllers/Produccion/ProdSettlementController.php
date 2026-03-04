<?php

namespace App\Http\Controllers\Produccion;

use App\Http\Controllers\Controller;
use App\Models\Produccion\ProdWorkerSettlement;
use App\Services\Produccion\ProdSettlementService;
use App\Services\Produccion\ProductionPayrollIntegrationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class ProdSettlementController extends Controller
{
    public function __construct(
        private ProdSettlementService $settlementService,
        private ProductionPayrollIntegrationService $integrationService
    ) {}

    public function index(Request $request)
    {
        if (!$request->ajax()) {
            return view('produccion.settlements.index');
        }

        $companyId = (int) session('company_id');
        $orderId = (int) $request->query('order_id');

        $q = DB::table('prod_worker_settlements as s')
            ->join('prod_orders as o', 'o.id', '=', 's.order_id')
            ->join('prod_order_operations as oo', 'oo.id', '=', 's.order_operation_id')
            ->join('prod_operations as op', 'op.id', '=', 'oo.operation_id')
            ->join('empleados as e', 'e.id', '=', 's.employee_id')
            ->join('inv_productos as p', 'p.id', '=', 'o.product_id')
            ->where('s.company_id', $companyId)
            ->select([
                's.id','s.order_id','o.code as order_code','s.status',
                DB::raw("CONCAT(p.codigo,' - ',p.nombre) as producto"),
                DB::raw("CONCAT(op.code,' - ',op.name) as operacion"),
                DB::raw("CONCAT(e.identificacion,' - ',e.nombres,' ',e.apellidos) as empleado"),
                's.qty','s.rate','s.gross_amount','s.updated_at'
            ]);

        if ($orderId > 0) $q->where('s.order_id', $orderId);

        // Resumen desde subquery para evitar líos de count con raw
        $base = clone $q;
        $sum = DB::query()->fromSub($base, 'x')
            ->selectRaw("
              ROUND(COALESCE(SUM(gross_amount),0),2) as total_pagar,
              ROUND(COALESCE(SUM(qty),0),2) as total_qty
            ")->first();

        return DataTables::of($q)
            ->addIndexColumn()
            ->editColumn('qty', fn($r) => number_format((float)$r->qty, 2, ',', '.'))
            ->editColumn('rate', fn($r) => number_format((float)$r->rate, 2, ',', '.'))
            ->editColumn('gross_amount', fn($r) => number_format((float)$r->gross_amount, 2, ',', '.'))
            ->editColumn('status', fn($r) => "<span class='badge badge-info'>{$r->status}</span>")
            ->with([
                'summary' => [
                    'total_pagar' => number_format((float)($sum->total_pagar ?? 0), 2, ',', '.'),
                    'total_qty' => number_format((float)($sum->total_qty ?? 0), 2, ',', '.'),
                ]
            ])
            ->rawColumns(['status'])
            ->make(true);
    }

    public function calculate(int $orderId)
    {
        $companyId = (int) session('company_id');
        $count = $this->settlementService->calculate($orderId, $companyId);

        return response()->json(['message' => "Liquidación calculada. Registros: {$count}"]);
    }

    public function sendToNomina(Request $request, int $orderId)
    {
        $companyId = (int) session('company_id');
        $userId = (int) auth()->id();

        $periodStart = $request->input('period_start');
        $periodEnd = $request->input('period_end');

        if (!$periodStart || !$periodEnd) {
            return response()->json(['message' => 'period_start y period_end son requeridos.'], 422);
        }

        $created = $this->integrationService->syncSettlementsToNomina(
            $orderId,
            $periodStart,
            $periodEnd,
            $companyId,
            $userId
        );

        return response()->json(['message' => "Enviado a Nómina. Novedades creadas: {$created}"]);
    }

    public function ordersList()
    {
        $companyId = (int) session('company_id');

        $items = DB::table('prod_orders as o')
            ->join('inv_productos as p', 'p.id', '=', 'o.product_id')
            ->where('o.company_id', $companyId)
            ->orderByDesc('o.id')
            ->limit(200)
            ->get(['o.id','o.code','o.start_date','o.end_date','o.status', DB::raw("CONCAT(p.codigo,' - ',p.nombre) as producto")])
            ->map(fn($r) => ['id' => $r->id, 'text' => "#{$r->id} | {$r->code} | {$r->producto} | {$r->status}"])
            ->values();

        return response()->json(['data' => $items]);
    }
}
