<?php

namespace App\Services\Organizacion;

use App\Models\CostCenter;

class CostCenterService
{
    public function create(int $companyId, array $data): CostCenter
    {
        $data['company_id'] = $companyId;
        return CostCenter::create($data);
    }

    public function update(int $companyId, int $id, array $data): CostCenter
    {
        $cc = CostCenter::where('company_id',$companyId)->findOrFail($id);
        $cc->update($data);
        return $cc;
    }

    public function find(int $companyId, int $id): CostCenter
    {
        return CostCenter::where('company_id',$companyId)->findOrFail($id);
    }

    public function listForSelect(int $companyId): array
    {
        return CostCenter::where('company_id',$companyId)
            ->where('is_active',1)
            ->orderBy('code')
            ->get(['id','code','name'])
            ->map(fn($c)=>[
                'id'=>$c->id,
                'text'=> trim(($c->code ? $c->code.' - ' : '').$c->name),
            ])->values()->all();
    }
}
