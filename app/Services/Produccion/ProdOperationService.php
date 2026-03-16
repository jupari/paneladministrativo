<?php

namespace App\Services\Produccion;

use App\Models\Activity;

class ProdOperationService
{
    public function create(array $data, int $companyId): int
    {
        $activity = Activity::create([
            'company_id'  => $companyId,
            'code'        => $data['code'],
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
            'unit_price'  => $data['unit_price'] ?? 0,
            'is_active'   => isset($data['is_active']) ? (bool) $data['is_active'] : true,
        ]);

        return $activity->id;
    }

    public function update(int $id, array $data, int $companyId): void
    {
        Activity::where('company_id', $companyId)
            ->where('id', $id)
            ->update([
                'code'        => $data['code'],
                'name'        => $data['name'],
                'description' => $data['description'] ?? null,
                'unit_price'  => $data['unit_price'] ?? 0,
                'is_active'   => isset($data['is_active']) ? (bool) $data['is_active'] : false,
            ]);
    }
}
