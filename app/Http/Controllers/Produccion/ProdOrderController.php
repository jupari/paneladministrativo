<?php

namespace App\Http\Controllers\Produccion;

use App\Http\Controllers\Controller;
use App\Http\Requests\Produccion\StoreProdOrderRequest;
use App\Http\Requests\Produccion\StoreProdWorkerLogRequest;
use App\Http\Requests\Produccion\UpdateProdOrderRequest;
use App\Models\Produccion\ProdOrder;
use App\Services\Produccion\ProdOrderLogService;
use App\Services\Produccion\ProdOrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class ProdOrderController extends Controller
{
    public function __construct(private ProdOrderService $service) {}

    public function index(Request $request)
    {
        if (!$request->ajax()) {
            return view('produccion.orders.index');
        }

        $companyId = (int) session('company_id');

        $q = DB::table('prod_orders as o')
            ->join('inv_productos as p', 'p.id', '=', 'o.product_id')
            ->where('o.company_id', $companyId)
            ->select([
                'o.id','o.code','o.objective_qty','o.start_date','o.end_date','o.status','o.created_at',
                DB::raw("CONCAT(p.codigo,' - ',p.nombre) as producto")
            ]);

        return DataTables::of($q)
            ->addIndexColumn()
            ->addColumn('acciones', function ($r) {
                return '
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
        $order = ProdOrder::where('company_id', $companyId)->findOrFail($id);

        return response()->json(['data' => $order]);
    }

    public function update(UpdateProdOrderRequest $request, int $id)
    {
        $companyId = (int) session('company_id');
        $order = ProdOrder::where('company_id', $companyId)->findOrFail($id);

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

    public function show(ProdOrder $order)
    {
        $companyId = (int) session('company_id');
        abort_if((int)$order->company_id !== $companyId, 403);

        return view('produccion.orders.show', compact('order'));
    }

    public function operationsTable(ProdOrder $order)
    {
        $companyId = (int) session('company_id');
        abort_if((int)$order->company_id !== $companyId, 403);

        $doneSub = DB::table('prod_worker_logs')
            ->selectRaw('order_operation_id, SUM(qty) as done_qty')
            ->where('company_id', $companyId)
            ->where('order_id', $order->id)
            ->groupBy('order_operation_id');

        $rows = DB::table('prod_order_operations as poo')
            ->join('prod_operations as op','op.id','=','poo.operation_id')
            ->leftJoinSub($doneSub, 'd', fn($j) => $j->on('d.order_operation_id','=','poo.id'))
            ->where('poo.order_id', $order->id)
            ->selectRaw("
                poo.id,
                poo.seq,
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
            ->orderBy('poo.seq');

        return datatables()->of($rows)
            ->addIndexColumn()
            ->addColumn('progress', function($r){
                $req = (float)$r->required_qty;
                $done = (float)$r->done_qty;
                $pct = $req > 0 ? min(100, round(($done/$req)*100, 1)) : 0;
                return $pct.'%';
            })
            ->editColumn('computed_status', function($r){
                $badge = match($r->computed_status){
                    'DONE' => 'success',
                    'IN_PROGRESS' => 'warning',
                    default => 'secondary'
                };
                return '<span class="badge badge-'.$badge.'">'.$r->computed_status.'</span>';
            })
            ->rawColumns(['computed_status'])
            ->make(true);
    }

    public function logsTable(ProdOrder $order)
    {
        $companyId = (int) session('company_id');
        abort_if((int)$order->company_id !== $companyId, 403);

        $rows = DB::table('prod_worker_logs as l')
            ->join('prod_order_operations as poo','poo.id','=','l.order_operation_id')
            ->join('prod_operations as op','op.id','=','poo.operation_id')
            ->leftJoin('empleados as e','e.id','=','l.employee_id')
            ->where('l.company_id',$companyId)
            ->where('l.order_id',$order->id)
            ->selectRaw("
                l.id,
                l.worked_at,
                op.name as operation,
                l.qty,
                COALESCE(CONCAT(e.nombres,' ',e.apellidos), CONCAT('Empleado#',l.employee_id)) as employee,
                l.notes
            ")
            ->orderByDesc('l.worked_at');

        return datatables()->of($rows)
            ->addIndexColumn()
            ->make(true);
    }

    public function storeLog(
            StoreProdWorkerLogRequest $request,
            ProdOrder $order,
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

    public function operationsSelect2(ProdOrder $order)
    {
        $companyId = (int) session('company_id');
        abort_if((int)$order->company_id !== $companyId, 403);

        $q = request('q');

        $results = DB::table('prod_order_operations as poo')
            ->join('prod_operations as op','op.id','=','poo.operation_id')
            ->where('poo.order_id',$order->id)
            ->when($q, fn($w)=>$w->where('op.name','like',"%$q%"))
            ->orderBy('poo.seq')
            ->limit(50)
            ->get()
            ->map(fn($r)=>[
                'id' => $r->id,
                'text' => "#{$r->seq} - {$r->name} (Req: {$r->required_qty})"
            ]);

        return response()->json(['results'=>$results]);
    }
}
