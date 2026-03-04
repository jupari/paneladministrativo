<?php

namespace App\Services\Produccion;

use App\Models\Produccion\ProdOrder;

class ProdOrderService
{
    public function create(array $data, int $companyId, int $userId): ProdOrder
    {
        return ProdOrder::create([
            'company_id' => $companyId,
            'code' => $data['code'],
            'product_id' => (int)$data['product_id'],
            'objective_qty' => (float)$data['objective_qty'],
            'start_date' => $data['start_date'] ?? null,
            'end_date' => $data['end_date'] ?? null,
            'status' => $data['status'],
            'notes' => $data['notes'] ?? null,
            'created_by' => $userId,
        ]);
    }

    public function update(ProdOrder $order, array $data): ProdOrder
    {
        $order->update([
            'code' => $data['code'],
            'product_id' => (int)$data['product_id'],
            'objective_qty' => (float)$data['objective_qty'],
            'start_date' => $data['start_date'] ?? null,
            'end_date' => $data['end_date'] ?? null,
            'status' => $data['status'],
            'notes' => $data['notes'] ?? null,
        ]);

        return $order;
    }
}
