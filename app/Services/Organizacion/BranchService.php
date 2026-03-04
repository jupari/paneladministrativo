<?php

namespace App\Services\Organizacion;

use App\Models\CompanyBranch;

class BranchService
{
    public function create(int $companyId, array $data): CompanyBranch
    {
        $data['company_id'] = $companyId;
        return CompanyBranch::create($data);
    }

    public function update(int $companyId, int $id, array $data): CompanyBranch
    {
        $branch = CompanyBranch::where('company_id',$companyId)->findOrFail($id);
        $branch->update($data);
        return $branch;
    }

    public function find(int $companyId, int $id): CompanyBranch
    {
        return CompanyBranch::where('company_id',$companyId)->findOrFail($id);
    }

    public function listForSelect(int $companyId): array
    {
        return CompanyBranch::where('company_id',$companyId)
            ->where('is_active',1)
            ->orderBy('name')
            ->get(['id','name','code'])
            ->map(fn($b)=>[
                'id'=>$b->id,
                'text'=> trim(($b->code ? $b->code.' - ' : '').$b->name),
            ])->values()->all();
    }
}
