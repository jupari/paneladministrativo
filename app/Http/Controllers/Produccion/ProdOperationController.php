<?php

namespace App\Http\Controllers\Produccion;

use App\Http\Controllers\Controller;
use App\Http\Requests\Produccion\StoreProdOperationRequest;
use App\Http\Requests\Produccion\UpdateProdOperationRequest;
use App\Services\Produccion\ProdOperationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class ProdOperationController extends Controller
{
    public function __construct(private ProdOperationService $service) {}

    public function index(Request $request)
    {
        if (!$request->ajax()) {
            return view('produccion.operations.index');
        }

        $companyId = (int) session('company_id');

        $q = DB::table('prod_operations')
            ->where('company_id', $companyId)
            ->select(['id','code','name','description','is_active','created_at']);

        return DataTables::of($q)
            ->addIndexColumn()
            ->editColumn('is_active', fn($r) => $r->is_active ? "<span class='badge badge-success'>Activo</span>" : "<span class='badge badge-secondary'>Inactivo</span>")
            ->addColumn('acciones', fn($r) => '<button class="btn btn-sm btn-primary" onclick="upOperation('.$r->id.')"><i class="fas fa-edit"></i></button>')
            ->rawColumns(['is_active','acciones'])
            ->make(true);
    }

    public function store(StoreProdOperationRequest $request)
    {
        $companyId = (int) session('company_id');
        $id = $this->service->create($request->validated(), $companyId);

        return response()->json(['message' => 'Operación creada.', 'id' => $id]);
    }

    public function edit(int $id)
    {
        $companyId = (int) session('company_id');

        $row = DB::table('prod_operations')
            ->where('company_id', $companyId)
            ->where('id', $id)
            ->first();

        return response()->json(['data' => $row]);
    }

    public function update(UpdateProdOperationRequest $request, int $id)
    {
        $companyId = (int) session('company_id');
        $this->service->update($id, $request->validated(), $companyId);

        return response()->json(['message' => 'Operación actualizada.']);
    }

    public function list()
    {
        $companyId = (int) session('company_id');

        $items = DB::table('prod_operations')
            ->where('company_id', $companyId)
            ->where('is_active', 1)
            ->orderBy('name')
            ->get(['id','code','name'])
            ->map(fn($o)=>['id'=>$o->id,'text'=> "{$o->code} - {$o->name}"])
            ->values();

        return response()->json(['data'=>$items]);
    }
}
