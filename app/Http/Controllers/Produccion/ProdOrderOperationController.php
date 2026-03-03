<?php

namespace App\Http\Controllers\Produccion;

use App\Http\Controllers\Controller;
use App\Models\Produccion\ProdOrder;
use Dflydev\DotAccessData\Data;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class ProdOrderOperationController extends Controller
{
    public function index(Request $request, int $orderId)
    {
        $companyId = (int) session('company_id');

        $q = DB::table('prod_order_operations as oo')
            ->join('prod_orders as o','o.id','=','oo.order_id')
            ->join('prod_operations as op','op.id','=','oo.operation_id')
            ->where('o.company_id', $companyId)
            ->where('oo.order_id', $orderId)
            ->select([
                'oo.id','oo.operation_id','oo.seq','oo.qty_per_unit','oo.required_qty','oo.status',
                DB::raw("CONCAT(op.code,' - ',op.name) as operacion")
            ]);

        return DataTables::of($q)
            ->addIndexColumn()
            ->editColumn('qty_per_unit', fn($r)=> number_format((float)$r->qty_per_unit, 4, ',', '.'))
            ->editColumn('required_qty', fn($r)=> number_format((float)$r->required_qty, 4, ',', '.'))
            ->addColumn('acciones', fn($r)=> '<button class="btn btn-sm btn-primary" onclick="upRouting('.$r->id.')"><i class="fas fa-edit"></i></button>')
            ->rawColumns(['acciones'])
            ->make(true);
    }

    public function show(int $orderId)
    {
        $companyId = (int) session('company_id');

        $rows = DB::table('prod_order_operations as oo')
            ->join('prod_orders as o','o.id','=','oo.order_id')
            ->join('prod_operations as op','op.id','=','oo.operation_id')
            ->where('o.company_id', $companyId)
            ->where('oo.order_id', $orderId)
            ->select([
                'oo.id','oo.operation_id','oo.seq','oo.qty_per_unit','oo.required_qty','oo.status',
                DB::raw("CONCAT(op.code,' - ',op.name) as operacion")
            ])
            ->orderBy('oo.seq')
            ->get();

        return DataTables::of($rows)
            ->addIndexColumn()
            ->editColumn('operacion', fn($r)=> '<b>'.$r->seq.'</b> | '.$r->operacion)
            ->editColumn('seq', fn($r)=> '<span class="badge badge-secondary">'.$r->seq.'</span>')
            ->editColumn('qty_per_unit', fn($r)=> number_format((float)$r->qty_per_unit, 4, ',', '.'))
            ->editColumn('required_qty', fn($r)=> number_format((float)$r->required_qty, 4, ',', '.'))
            ->editColumn('status', fn($r)=> '<span class="badge badge-'.($r->status=='PENDING'?'warning':($r->status=='IN_PROGRESS'?'primary':'success')).'">'.$r->status.'</span>')
            ->editColumn('acciones', fn($r)=> '<button class="btn btn-sm btn-primary" onclick="upRouting('.$r->id.')"><i class="fas fa-edit"></i></button>')
            ->rawColumns(['operacion','seq','status','acciones'])
            ->make(true);

        return response()->json(['data'=>$rows]);
    }

    public function store(Request $request, int $orderId)
    {
        $companyId = (int) session('company_id');

        $data = $request->validate([
            'operation_id' => 'required|integer',
            'seq' => 'required|integer|min:1',
            'qty_per_unit' => 'required|numeric|min:0.0001',
            'status' => 'required|in:PENDING,IN_PROGRESS,DONE',
        ]);

        $order = DB::table('prod_orders')->where('company_id',$companyId)->where('id',$orderId)->first();
        if(!$order) return response()->json(['message'=>'Orden no encontrada'], 404);

        $required = (float)$order->objective_qty * (float)$data['qty_per_unit'];

        DB::table('prod_order_operations')->insert([
            'order_id' => $orderId,
            'operation_id' => (int)$data['operation_id'],
            'seq' => (int)$data['seq'],
            'qty_per_unit' => (float)$data['qty_per_unit'],
            'required_qty' => $required,
            'status' => $data['status'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['message'=>'Operación agregada al routing.']);
    }

    public function edit(int $id)
    {
        $companyId = (int) session('company_id');

        $row = DB::table('prod_order_operations as oo')
            ->join('prod_orders as o','o.id','=','oo.order_id')
            ->where('o.company_id',$companyId)
            ->where('oo.id',$id)
            ->select(['oo.*'])
            ->first();

        return response()->json(['data'=>$row]);
    }

    public function update(Request $request, int $id)
    {
        $companyId = (int) session('company_id');

        $data = $request->validate([
            'operation_id' => 'required|integer',
            'seq' => 'required|integer|min:1',
            'qty_per_unit' => 'required|numeric|min:0.0001',
            'status' => 'required|in:PENDING,IN_PROGRESS,DONE',
        ]);

        $row = DB::table('prod_order_operations as oo')
            ->join('prod_orders as o','o.id','=','oo.order_id')
            ->where('o.company_id',$companyId)
            ->where('oo.id',$id)
            ->select(['oo.id','oo.order_id'])
            ->first();

        if(!$row) return response()->json(['message'=>'Registro no encontrado'], 404);

        $order = DB::table('prod_orders')->where('company_id',$companyId)->where('id',$row->order_id)->first();
        $required = (float)$order->objective_qty * (float)$data['qty_per_unit'];

        DB::table('prod_order_operations')
            ->where('id',$id)
            ->update([
                'operation_id' => (int)$data['operation_id'],
                'seq' => (int)$data['seq'],
                'qty_per_unit' => (float)$data['qty_per_unit'],
                'required_qty' => $required,
                'status' => $data['status'],
                'updated_at' => now(),
            ]);

        return response()->json(['message'=>'Routing actualizado.']);
    }

    // dropdown para logs
    public function listByOrder(Request $request)
    {
        $companyId = (int) session('company_id');
        $orderId = (int) $request->query('order_id');

        $items = DB::table('prod_order_operations as oo')
            ->join('prod_orders as o','o.id','=','oo.order_id')
            ->join('prod_operations as op','op.id','=','oo.operation_id')
            ->where('o.company_id',$companyId)
            ->where('oo.order_id',$orderId)
            ->orderBy('oo.seq')
            ->get([
                'oo.id',
                DB::raw("CONCAT(oo.seq,' | ',op.name,' | Req: ',oo.required_qty) as text")
            ]);

        return response()->json(['data'=>$items]);
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
            ->leftJoinSub($doneSub, 'd', function($j){
                $j->on('d.order_operation_id','=','poo.id');
            })
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
            ->orderBy('poo.seq')
            ->get();

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
}
