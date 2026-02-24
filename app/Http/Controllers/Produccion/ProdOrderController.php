<?php

namespace App\Http\Controllers\Produccion;

use App\Http\Controllers\Controller;
use App\Http\Requests\Produccion\StoreProdOrderRequest;
use App\Http\Requests\Produccion\UpdateProdOrderRequest;
use App\Models\Produccion\ProdOrder;
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
}
