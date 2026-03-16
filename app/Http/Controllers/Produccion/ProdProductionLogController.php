<?php

namespace App\Http\Controllers\Produccion;

use App\Http\Controllers\Controller;
use App\Http\Requests\Produccion\StoreProdLogRequest;
use App\Http\Requests\Produccion\UpdateProdLogRequest;
use App\Models\Empleado;
use App\Models\ProductionOperation;
use App\Services\Produccion\ProdLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class ProdProductionLogController extends Controller
{
    public function __construct(private ProdLogService $service) {}

    public function index(Request $request)
    {
        if (!$request->ajax()) {
            return view('produccion.logs.index');
        }

        $companyId = (int) session('company_id');

        $orderId = $request->query('order_id');

        $q = DB::table('production_operations as l')
            ->join('production_orders as o', 'o.id', '=', 'l.production_order_id')
            ->join('production_order_activities as oo', 'oo.id', '=', 'l.order_operation_id')
            ->join('activities as op', 'op.id', '=', 'oo.activity_id')
            ->join('empleados as e', 'e.id', '=', 'l.employee_id')
            ->join('inv_productos as p', 'p.id', '=', 'o.product_id')
            ->where('l.company_id', $companyId)
            ->whereNotNull('l.employee_id')
            ->select([
                'l.id','l.work_date','l.shift','l.quantity as qty','l.rejected_qty','l.created_at',
                'o.id as order_id', 'o.order_code as order_code',
                DB::raw("CONCAT(p.codigo,' - ',p.nombre) as producto"),
                DB::raw("CONCAT(e.identificacion,' - ',e.nombres,' ',e.apellidos) as empleado"),
                DB::raw("CONCAT(op.code,' - ',op.name) as operacion"),
            ]);

        if ($orderId) $q->where('l.production_order_id', (int)$orderId);

        return DataTables::of($q)
            ->addIndexColumn()
            ->addColumn('accepted_qty', fn($r) => max(0, (float)$r->qty - (float)$r->rejected_qty))
            ->addColumn('acciones', fn($r) => '<button class="btn btn-sm btn-primary" onclick="upLog('.$r->id.')"><i class="fas fa-edit"></i></button>')
            ->rawColumns(['acciones'])
            ->make(true);
    }

    public function store(StoreProdLogRequest $request)
    {
        $companyId = (int) session('company_id');
        $userId = (int) auth()->id();

        $log = $this->service->create($request->validated(), $companyId, $userId);

        return response()->json(['message' => 'Log registrado.', 'data' => $log]);
    }

    public function edit(int $id)
    {
        $companyId = (int) session('company_id');
        $log = ProductionOperation::where('company_id', $companyId)->findOrFail($id);

        // Mapear campos para compatibilidad con el frontend
        $data = $log->toArray();
        $data['order_id'] = $log->production_order_id;
        $data['qty'] = $log->quantity;

        return response()->json(['data' => $data]);
    }

    public function update(UpdateProdLogRequest $request, int $id)
    {
        $companyId = (int) session('company_id');
        $log = ProductionOperation::where('company_id', $companyId)->findOrFail($id);

        $this->service->update($log, $request->validated());

        return response()->json(['message' => 'Log actualizado.', 'data' => $log]);
    }

    public function employeesList()
    {
        $companyId = (int) session('company_id');

        $items = Empleado::query()
            ->where('company_id', $companyId)
            ->where('active', 1)
            ->orderBy('apellidos')
            ->orderBy('nombres')
            ->get(['id','identificacion','nombres','apellidos'])
            ->map(fn($e) => ['id' => $e->id, 'text' => "{$e->identificacion} - {$e->nombres} {$e->apellidos}"])
            ->values();

        return response()->json(['data' => $items]);
    }
}
