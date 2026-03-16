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

        $q = DB::table('production_order_activities as oo')
            ->join('production_orders as o','o.id','=','oo.production_order_id')
            ->join('activities as op','op.id','=','oo.activity_id')
            ->where('o.company_id', $companyId)
            ->where('oo.production_order_id', $orderId)
            ->select([
                'oo.id','oo.activity_id as operation_id','oo.position as seq','oo.qty_per_unit','oo.required_qty','oo.status',
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

        $rows = DB::table('production_order_activities as oo')
            ->join('production_orders as o','o.id','=','oo.production_order_id')
            ->join('activities as op','op.id','=','oo.activity_id')
            ->where('o.company_id', $companyId)
            ->where('oo.production_order_id', $orderId)
            ->select([
                'oo.id','oo.activity_id as operation_id','oo.position as seq','oo.qty_per_unit','oo.required_qty','oo.status',
                DB::raw("CONCAT(op.code,' - ',op.name) as operacion")
            ])
            ->orderBy('oo.position')
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

        $order = DB::table('production_orders')->where('company_id',$companyId)->whereNull('deleted_at')->where('id',$orderId)->first();
        if(!$order) return response()->json(['message'=>'Orden no encontrada'], 404);

        $required = (float)$order->total_units * (float)$data['qty_per_unit'];

        DB::table('production_order_activities')->insert([
            'production_order_id' => $orderId,
            'activity_id' => (int)$data['operation_id'],
            'position' => (int)$data['seq'],
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

        $row = DB::table('production_order_activities as oo')
            ->join('production_orders as o','o.id','=','oo.production_order_id')
            ->where('o.company_id',$companyId)
            ->where('oo.id',$id)
            ->select([
                'oo.id','oo.production_order_id as order_id','oo.activity_id as operation_id',
                'oo.position as seq','oo.qty_per_unit','oo.required_qty','oo.status',
                'oo.created_at','oo.updated_at'
            ])
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

        $row = DB::table('production_order_activities as oo')
            ->join('production_orders as o','o.id','=','oo.production_order_id')
            ->where('o.company_id',$companyId)
            ->where('oo.id',$id)
            ->select(['oo.id','oo.production_order_id as order_id'])
            ->first();

        if(!$row) return response()->json(['message'=>'Registro no encontrado'], 404);

        $order = DB::table('production_orders')->where('company_id',$companyId)->whereNull('deleted_at')->where('id',$row->order_id)->first();
        $required = (float)$order->total_units * (float)$data['qty_per_unit'];

        DB::table('production_order_activities')
            ->where('id',$id)
            ->update([
                'activity_id' => (int)$data['operation_id'],
                'position' => (int)$data['seq'],
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

        $items = DB::table('production_order_activities as oo')
            ->join('production_orders as o','o.id','=','oo.production_order_id')
            ->join('activities as op','op.id','=','oo.activity_id')
            ->where('o.company_id',$companyId)->whereNull('o.deleted_at')->where('oo.production_order_id',$orderId)
            ->orderBy('oo.position')
            ->get([
                'oo.id',
                DB::raw("CONCAT(oo.position,' | ',op.name,' | Req: ',oo.required_qty) as text")
            ]);

        return response()->json(['data'=>$items]);
    }

    public function operationsTable(ProdOrder $order)
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
            ->leftJoinSub($doneSub, 'd', function($j){
                $j->on('d.order_operation_id','=','poo.id');
            })
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
            ->orderBy('poo.position')
            ->get();

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
}
