<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Branches\StoreBranchRequest;
use App\Http\Requests\Branches\UpdateBranchRequest;
use App\Services\Organizacion\BranchService;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class BranchController extends Controller
{
    public function index(Request $request)
    {
        if (!$request->ajax()) return view('organizacion.branches.index');

        $companyId = (int) session('company_id');

        $q = \App\Models\CompanyBranch::query()
            ->where('company_id',$companyId)
            ->select(['id','code','name','city','phone','is_active','created_at']);

        return DataTables::of($q)
            ->addIndexColumn()
            ->editColumn('is_active', fn($r)=> $r->is_active
                ? "<span class='badge badge-success'>Activo</span>"
                : "<span class='badge badge-secondary'>Inactivo</span>"
            )
            ->addColumn('acciones', fn($r)=>
                '<button class="btn btn-sm btn-primary" onclick="upBranch('.$r->id.')"><i class="fas fa-edit"></i></button>'
            )
            ->rawColumns(['is_active','acciones'])
            ->make(true);
    }

    public function store(StoreBranchRequest $request, BranchService $svc)
    {
        $companyId = (int) session('company_id');
        $svc->create($companyId, $request->validated());
        return response()->json(['message'=>'Sucursal creada correctamente.']);
    }

    public function edit(int $id, BranchService $svc)
    {
        $companyId = (int) session('company_id');
        $b = $svc->find($companyId, $id);
        return response()->json(['data'=>$b]);
    }

    public function update(UpdateBranchRequest $request, int $id, BranchService $svc)
    {
        $companyId = (int) session('company_id');
        $svc->update($companyId, $id, $request->validated());
        return response()->json(['message'=>'Sucursal actualizada correctamente.']);
    }

    public function list(BranchService $svc)
    {
        $companyId = (int) session('company_id');
        return response()->json(['data' => $svc->listForSelect($companyId)]);
    }
}
