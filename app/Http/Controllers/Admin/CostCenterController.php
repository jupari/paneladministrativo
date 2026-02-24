<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CostCenters\StoreCostCenterRequest;
use App\Http\Requests\CostCenters\UpdateCostCenterRequest;
use App\Services\Organizacion\CostCenterService;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class CostCenterController extends Controller
{
    public function index(Request $request)
    {
        if (!$request->ajax()) return view('organizacion.cost_centers.index');

        $companyId = (int) session('company_id');

        $q = \App\Models\CostCenter::query()
            ->where('company_id',$companyId)
            ->with('parent:id,code,name')
            ->select(['id','code','name','parent_id','is_active','created_at']);

        return DataTables::of($q)
            ->addIndexColumn()
            ->addColumn('parent', fn($r)=> $r->parent ? ($r->parent->code.' - '.$r->parent->name) : '-')
            ->editColumn('is_active', fn($r)=> $r->is_active
                ? "<span class='badge badge-success'>Activo</span>"
                : "<span class='badge badge-secondary'>Inactivo</span>"
            )
            ->addColumn('acciones', fn($r)=>
                '<button class="btn btn-sm btn-primary" onclick="upCostCenter('.$r->id.')"><i class="fas fa-edit"></i></button>'
            )
            ->rawColumns(['is_active','acciones'])
            ->make(true);
    }

    public function store(StoreCostCenterRequest $request, CostCenterService $svc)
    {
        $companyId = (int) session('company_id');
        $svc->create($companyId, $request->validated());
        return response()->json(['message'=>'Centro de costo creado correctamente.']);
    }

    public function edit(int $id, CostCenterService $svc)
    {
        $companyId = (int) session('company_id');
        $cc = $svc->find($companyId, $id);
        return response()->json(['data'=>$cc]);
    }

    public function update(UpdateCostCenterRequest $request, int $id, CostCenterService $svc)
    {
        $companyId = (int) session('company_id');
        $svc->update($companyId, $id, $request->validated());
        return response()->json(['message'=>'Centro de costo actualizado correctamente.']);
    }

    public function list(CostCenterService $svc)
    {
        $companyId = (int) session('company_id');
        return response()->json(['data' => $svc->listForSelect($companyId)]);
    }
}
