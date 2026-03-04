<?php

namespace App\Http\Controllers\Produccion;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class ProdRateController extends Controller
{
    public function index(Request $request)
    {
        if(!$request->ajax()){
            return view('produccion.rates.index');
        }

        $companyId = (int) session('company_id');

        $q = DB::table('prod_operation_product_rates as r')
            ->join('inv_productos as p','p.id','=','r.product_id')
            ->join('prod_operations as op','op.id','=','r.operation_id')
            ->where('r.company_id',$companyId)
            ->select([
                'r.id','r.product_id','r.operation_id','r.amount','r.valid_from','r.valid_to','r.is_active',
                DB::raw("CONCAT(p.codigo,' - ',p.nombre) as producto"),
                DB::raw("CONCAT(op.code,' - ',op.name) as operacion"),
            ]);

        return DataTables::of($q)
            ->addIndexColumn()
            ->editColumn('amount', fn($r)=> number_format((float)$r->amount, 2, ',', '.'))
            ->editColumn('is_active', fn($r)=> $r->is_active ? "<span class='badge badge-success'>Activo</span>" : "<span class='badge badge-secondary'>Inactivo</span>")
            ->addColumn('acciones', fn($r)=> '<button class="btn btn-sm btn-primary" onclick="upRate('.$r->id.')"><i class="fas fa-edit"></i></button>')
            ->rawColumns(['is_active','acciones'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $companyId = (int) session('company_id');

        $data = $request->validate([
            'product_id' => 'required|integer',
            'operation_id' => 'required|integer',
            'amount' => 'required|numeric|min:0.01',
            'valid_from' => 'nullable|date',
            'valid_to' => 'nullable|date|after_or_equal:valid_from',
            'is_active' => 'nullable|boolean',
        ]);

        DB::table('prod_operation_product_rates')->insert([
            'company_id' => $companyId,
            'product_id' => (int)$data['product_id'],
            'operation_id' => (int)$data['operation_id'],
            'amount' => (float)$data['amount'],
            'valid_from' => $data['valid_from'] ?? null,
            'valid_to' => $data['valid_to'] ?? null,
            'is_active' => isset($data['is_active']) ? (int)$data['is_active'] : 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['message'=>'Tarifa creada.']);
    }

    public function edit(int $id)
    {
        $companyId = (int) session('company_id');

        $row = DB::table('prod_operation_product_rates')
            ->where('company_id',$companyId)
            ->where('id',$id)
            ->first();

        return response()->json(['data'=>$row]);
    }

    public function update(Request $request, int $id)
    {
        $companyId = (int) session('company_id');

        $data = $request->validate([
            'product_id' => 'required|integer',
            'operation_id' => 'required|integer',
            'amount' => 'required|numeric|min:0.01',
            'valid_from' => 'nullable|date',
            'valid_to' => 'nullable|date|after_or_equal:valid_from',
            'is_active' => 'nullable|boolean',
        ]);

        DB::table('prod_operation_product_rates')
            ->where('company_id',$companyId)
            ->where('id',$id)
            ->update([
                'product_id' => (int)$data['product_id'],
                'operation_id' => (int)$data['operation_id'],
                'amount' => (float)$data['amount'],
                'valid_from' => $data['valid_from'] ?? null,
                'valid_to' => $data['valid_to'] ?? null,
                'is_active' => isset($data['is_active']) ? (int)$data['is_active'] : 0,
                'updated_at' => now(),
            ]);

        return response()->json(['message'=>'Tarifa actualizada.']);
    }
}
