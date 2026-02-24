<?php

namespace App\Services\Produccion;

use Illuminate\Support\Facades\DB;

class ProdOperationService
{
    public function create(array $data, int $companyId): int
    {
        return DB::table('prod_operations')->insertGetId([
            'company_id' => $companyId,
            'code' => $data['code'],
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'is_active' => isset($data['is_active']) ? (int)$data['is_active'] : 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function update(int $id, array $data, int $companyId): void
    {
        DB::table('prod_operations')
            ->where('company_id', $companyId)
            ->where('id', $id)
            ->update([
                'code' => $data['code'],
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'is_active' => isset($data['is_active']) ? (int)$data['is_active'] : 0,
                'updated_at' => now(),
            ]);
    }
}
