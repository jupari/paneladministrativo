<?php

namespace App\Http\Controllers\Produccion;

use App\Http\Controllers\Controller;
use App\Http\Requests\Produccion\StoreProdOrderRequest;
use App\Http\Requests\Produccion\StoreProdWorkerLogRequest;
use App\Http\Requests\Produccion\UpdateProdOrderRequest;
use App\Models\Produccion\ProdOrder;
use App\Models\ProductionOrder;
use App\Services\Produccion\ProdOrderLogService;
use App\Services\Produccion\ProdOrderService;
use App\Services\Produccion\ProdSettlementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class ProdOrderController extends Controller
{
    public function __construct(private ProdOrderService $service, private ProdOrderLogService $logService) {}

    public function index(Request $request)
    {
        if (!$request->ajax()) {
            return view('produccion.orders.index');
        }

        $companyId = (int) session('company_id');

        $q = DB::table('production_orders as o')
            ->join('inv_productos as p', 'p.id', '=', 'o.product_id')
            ->where('o.company_id', $companyId)
            ->whereNull('o.deleted_at')
            ->select([
                'o.id',
                'o.order_code as code',
                'o.total_units as objective_qty',
                'o.start_date',
                'o.deadline as end_date',
                DB::raw("UPPER(o.status) as status"),
                'o.created_at',
                DB::raw("CONCAT(p.codigo,' - ',p.nombre) as producto")
            ]);

        return DataTables::of($q)
            ->addIndexColumn()
            ->addColumn('acciones', function ($r) {
                $showUrl = route('admin.produccion.orders.show', $r->id);
                return '
                  <a href="'.$showUrl.'" class="btn btn-sm btn-info" title="Ver detalle"><i class="fas fa-eye"></i></a>
                  <button class="btn btn-sm btn-primary" onclick="upOrder('.$r->id.')"><i class="fas fa-edit"></i></button>
                ';
            })
            ->editColumn('status', fn($r) => "<span class='badge badge-info'>{$r->status}</span>")
            ->rawColumns(['acciones','status'])
            ->make(true);
    }

    public function store(StoreProdOrderRequest $request)
    {
        $companyId = (int) session('company_id');
        $userId = (int) auth()->id();

        $order = $this->service->create($request->validated(), $companyId, $userId);

        return response()->json(['message' => 'Orden creada.', 'data' => $order]);
    }

    public function edit(int $id)
    {
        $companyId = (int) session('company_id');
        $order = ProductionOrder::where('company_id', $companyId)
                        ->findOrFail($id)
                        ->select([
                            'id',
                            'order_code as code',
                            'total_units as objective_qty',
                            'start_date',
                            'deadline as end_date',
                            DB::raw("UPPER(status) as status"),
                            'created_at',
                            'notes',
                            'completed_at',
                            'product_id',
                            DB::raw("CONCAT(p.codigo,' - ',p.nombre) as producto")
                        ]);

        return response()->json(['data' => $order]);
    }

    public function update(UpdateProdOrderRequest $request, int $id)
    {
        $companyId = (int) session('company_id');
        $order = ProductionOrder::where('company_id', $companyId)->findOrFail($id);

        $this->service->update($order, $request->validated());

        return response()->json(['message' => 'Orden actualizada.', 'data' => $order]);
    }

    // Select productos
    public function productsList()
    {
        $items = DB::table('inv_productos')
            ->orderBy('nombre')
            ->limit(500)
            ->get(['id','codigo','nombre'])
            ->map(fn($p) => ['id' => $p->id, 'text' => "{$p->codigo} - {$p->nombre}"])
            ->values();

        return response()->json(['data' => $items]);
    }

    public function show(ProductionOrder $order)
    {
        $companyId = (int) session('company_id');
        abort_if((int)$order->company_id !== $companyId, 403);

        $totalDamaged = (int) DB::table('damaged_garments')
            ->where('production_order_id', $order->id)
            ->sum('quantity');

        $producto = DB::table('inv_productos')
            ->where('id', $order->product_id)
            ->first(['codigo','nombre']);

        $productName = $producto
            ? "{$producto->codigo} - {$producto->nombre}"
            : 'N/A';

        $totalProduced = (int) DB::table('production_operations')
            ->where('production_order_id', $order->id)
            ->where(function ($q) use ($companyId) {
                $q->where('company_id', $companyId)->orWhereNull('company_id');
            })
            ->sum('quantity');

        return view('produccion.orders.show', compact('order', 'totalDamaged', 'productName', 'totalProduced'));
    }

    public function operationsTable(ProductionOrder $order)
    {
        $companyId = (int) session('company_id');
        abort_if((int)$order->company_id !== $companyId, 403);

        // Total de prendas dañadas para esta orden
        $totalDamaged = (int) DB::table('damaged_garments')
            ->where('production_order_id', $order->id)
            ->sum('quantity');

        $doneSub = DB::table('production_operations as po')
            ->leftJoin('production_order_activities as poa_resolve', function ($j) use ($order) {
                $j->on('poa_resolve.activity_id', '=', 'po.activity_id')
                  ->where('poa_resolve.production_order_id', '=', $order->id);
            })
            ->selectRaw('COALESCE(po.order_operation_id, poa_resolve.id) as order_operation_id, SUM(po.quantity) as done_qty')
            ->where('po.production_order_id', $order->id)
            ->where(function ($q) use ($companyId) {
                $q->where('po.company_id', $companyId)
                  ->orWhereNull('po.company_id');
            })
            ->groupByRaw('COALESCE(po.order_operation_id, poa_resolve.id)');

        $rows = DB::table('production_order_activities as poo')
            ->join('activities as op','op.id','=','poo.activity_id')
            ->leftJoinSub($doneSub, 'd', fn($j) => $j->on('d.order_operation_id','=','poo.id'))
            ->where('poo.production_order_id', $order->id)
            ->selectRaw("
                poo.id,
                poo.position as seq,
                op.code,
                op.name,
                poo.qty_per_unit,
                poo.required_qty,
                COALESCE(d.done_qty,0) as done_qty,
                GREATEST(poo.required_qty - COALESCE(d.done_qty,0),0) as remaining_qty,
                CASE
                WHEN COALESCE(d.done_qty,0) <= 0 THEN 'PENDING'
                WHEN COALESCE(d.done_qty,0) >= poo.required_qty THEN 'DONE'
                ELSE 'IN_PROGRESS'
                END as computed_status
            ")
            ->orderBy('poo.position');

        return datatables()->of($rows)
            ->addIndexColumn()
            ->addColumn('damaged_qty', function($r) use ($totalDamaged) {
                return round($totalDamaged * (float)$r->qty_per_unit, 2);
            })
            ->addColumn('adjusted_required', function($r) use ($totalDamaged) {
                return max(0, (float)$r->required_qty - ($totalDamaged * (float)$r->qty_per_unit));
            })
            ->addColumn('progress', function($r) use ($totalDamaged) {
                $adjusted = max(0, (float)$r->required_qty - ($totalDamaged * (float)$r->qty_per_unit));
                $done = (float)$r->done_qty;
                $pct = $adjusted > 0 ? min(100, round(($done/$adjusted)*100, 1)) : ($done > 0 ? 100 : 0);
                return $pct.'%';
            })
            ->editColumn('remaining_qty', function($r) use ($totalDamaged) {
                $adjusted = max(0, (float)$r->required_qty - ($totalDamaged * (float)$r->qty_per_unit));
                return max(0, round($adjusted - (float)$r->done_qty, 2));
            })
            ->editColumn('computed_status', function($r) use ($totalDamaged) {
                $adjusted = max(0, (float)$r->required_qty - ($totalDamaged * (float)$r->qty_per_unit));
                $done = (float)$r->done_qty;
                if ($done <= 0) $status = 'PENDING';
                elseif ($done >= $adjusted) $status = 'DONE';
                else $status = 'IN_PROGRESS';
                $badge = match($status){
                    'DONE' => 'success',
                    'IN_PROGRESS' => 'warning',
                    default => 'secondary'
                };
                return '<span class="badge badge-'.$badge.'">'.$status.'</span>';
            })
            ->with(['total_damaged' => $totalDamaged])
            ->rawColumns(['computed_status'])
            ->make(true);
    }

    public function logsTable(ProductionOrder $order)
    {
        $companyId = (int) session('company_id');
        abort_if((int)$order->company_id !== $companyId, 403);

        $rows = DB::table('production_operations as l')
            ->leftJoin('production_order_activities as poo','poo.id','=','l.order_operation_id')
            ->join('activities as op','op.id','=', DB::raw('COALESCE(poo.activity_id, l.activity_id)'))
            ->leftJoin('empleados as e','e.id','=','l.employee_id')
            ->leftJoin('workshop_operators as wo','wo.id','=','l.workshop_operator_id')
            ->where('l.production_order_id',$order->id)
            ->where(function ($q) use ($companyId) {
                $q->where('l.company_id', $companyId)
                  ->orWhereNull('l.company_id');
            })
            ->selectRaw("
                l.id,
                l.registered_at as worked_at,
                op.name as operation,
                l.quantity as qty,
                COALESCE(
                    CONCAT(e.nombres,' ',e.apellidos),
                    wo.name,
                    CONCAT('Op#',l.workshop_operator_id)
                ) as employee,
                l.notes
            ")
            ->orderByDesc('l.registered_at');

        return datatables()->of($rows)
            ->addIndexColumn()
            ->make(true);
    }

    public function storeLog(
            StoreProdWorkerLogRequest $request,
            ProductionOrder $order,
            ProdOrderLogService $service
    ){
        $companyId = (int) session('company_id');
        $userId = (int) auth()->id();

        $created = $service->storeLogs(
            $order,
            $companyId,
            (int)$request->order_operation_id,
            $request->employee_ids,
            (float)$request->qty,
            (string)$request->worked_at,
            $request->notes,
            $userId
        );

        return response()->json([
            'message' => "Se registraron {$created} registros de producción.",
        ]);
    }

    public function operationsSelect2(ProductionOrder $order)
    {
        $companyId = (int) session('company_id');
        abort_if((int)$order->company_id !== $companyId, 403);

        $q = request('q');

        $results = DB::table('production_order_activities as poo')
            ->join('activities as op','op.id','=','poo.activity_id')
            ->where('poo.production_order_id',$order->id)
            ->when($q, fn($w)=>$w->where('op.name','like',"%$q%"))
            ->orderBy('poo.position')
            ->limit(50)
            ->get()
            ->map(fn($r)=>[
                'id' => $r->id,
                'text' => "#{$r->position} - {$r->name} (Req: {$r->required_qty})"
            ]);

        return response()->json(['results'=>$results]);
    }

    public function employeesSelect2()
    {
        $companyId = (int) session('company_id');
        $q = request('q');

        $results = DB::table('empleados')
            ->where('company_id', $companyId)
            ->where('active', 1)
            ->when($q, function($w) use ($q) {
                $w->where(function($w2) use ($q) {
                    $w2->where('nombres', 'like', "%$q%")
                       ->orWhere('apellidos', 'like', "%$q%")
                       ->orWhere('identificacion', 'like', "%$q%");
                });
            })
            ->orderBy('apellidos')
            ->orderBy('nombres')
            ->limit(50)
            ->get(['id','identificacion','nombres','apellidos'])
            ->map(fn($e) => ['id' => $e->id, 'text' => "{$e->identificacion} - {$e->nombres} {$e->apellidos}"]);

        return response()->json(['results' => $results]);
    }

    public function operationsList(ProductionOrder $order)
    {
        $companyId = (int) session('company_id');
        abort_if((int)$order->company_id !== $companyId, 403);

        $results = DB::table('activities')
            ->where('company_id', $companyId)
            ->limit(50)
            ->get();

        return response()->json(['results'=>$results]);
    }

    public function damagesTable(ProductionOrder $order)
    {
        $companyId = (int) session('company_id');
        abort_if((int)$order->company_id !== $companyId, 403);

        $rows = DB::table('damaged_garments as dg')
            ->leftJoin('damage_types as dt', 'dt.id', '=', 'dg.damage_type_id')
            ->leftJoin('users as u', 'u.id', '=', 'dg.user_id')
            ->where('dg.production_order_id', $order->id)
            ->select([
                'dg.id',
                'dg.quantity',
                'dg.registered_at',
                'dg.notes',
                'dg.created_at',
                DB::raw("COALESCE(CONCAT(dt.code,' - ',dt.name), 'Sin tipo') as damage_type"),
                DB::raw("COALESCE(u.name, CONCAT('User#',dg.user_id)) as registered_by"),
            ])
            ->orderByDesc('dg.registered_at');

        return datatables()->of($rows)
            ->addIndexColumn()
            ->make(true);
    }

    public function settlementData(ProductionOrder $order)
    {
        $companyId = (int) session('company_id');
        abort_if((int)$order->company_id !== $companyId, 403);

        // Resumen por operación: cantidad producida, tarifa, monto
        $byOperation = DB::table('prod_worker_settlements as s')
            ->join('production_order_activities as poa', 'poa.id', '=', 's.order_operation_id')
            ->join('activities as a', 'a.id', '=', 'poa.activity_id')
            ->where('s.order_id', $order->id)
            ->where('s.company_id', $companyId)
            ->selectRaw("
                a.code as op_code,
                a.name as op_name,
                SUM(s.qty) as total_qty,
                s.rate,
                SUM(s.gross_amount) as total_amount,
                COUNT(DISTINCT s.employee_id) as workers
            ")
            ->groupBy('poa.id','a.code','a.name','s.rate')
            ->orderBy('poa.position')
            ->get();

        // Resumen por empleado
        $byEmployee = DB::table('prod_worker_settlements as s')
            ->leftJoin('empleados as e', 'e.id', '=', 's.employee_id')
            ->leftJoin('workshop_operators as wo', 'wo.id', '=', 's.employee_id')
            ->where('s.order_id', $order->id)
            ->where('s.company_id', $companyId)
            ->selectRaw("
                COALESCE(e.identificacion, CONCAT('OP-',s.employee_id)) as doc,
                COALESCE(CONCAT(e.nombres,' ',e.apellidos), wo.name, CONCAT('Operario #',s.employee_id)) as name,
                SUM(s.qty) as total_qty,
                SUM(s.gross_amount) as total_amount
            ")
            ->groupBy('s.employee_id','e.identificacion','e.nombres','e.apellidos','wo.name')
            ->orderByDesc(DB::raw('SUM(s.gross_amount)'))
            ->get();

        // Totales generales
        $totals = DB::table('prod_worker_settlements')
            ->where('order_id', $order->id)
            ->where('company_id', $companyId)
            ->selectRaw("
                COALESCE(SUM(qty),0) as total_qty,
                COALESCE(SUM(gross_amount),0) as total_amount,
                COUNT(DISTINCT employee_id) as total_workers,
                COUNT(*) as total_rows
            ")
            ->first();

        // Costo unitario de la orden
        $costPerUnit = (float) $order->cost_per_unit;
        $objective = (int) $order->total_units;
        $costProjected = $costPerUnit * $objective;

        // Estado de la liquidación
        $statusCounts = DB::table('prod_worker_settlements')
            ->where('order_id', $order->id)
            ->where('company_id', $companyId)
            ->selectRaw("status, COUNT(*) as cnt")
            ->groupBy('status')
            ->pluck('cnt','status');

        return response()->json([
            'by_operation' => $byOperation,
            'by_employee'  => $byEmployee,
            'totals'       => $totals,
            'cost_per_unit' => $costPerUnit,
            'objective'     => $objective,
            'cost_projected' => $costProjected,
            'status_counts' => $statusCounts,
        ]);
    }

    public function calculateSettlement(ProductionOrder $order, ProdSettlementService $service)
    {
        $companyId = (int) session('company_id');
        abort_if((int)$order->company_id !== $companyId, 403);

        $count = $service->calculate($order->id, $companyId);

        return response()->json(['message' => "Liquidación calculada. Registros: {$count}"]);
    }
}
