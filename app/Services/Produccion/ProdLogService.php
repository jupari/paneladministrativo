<?php

namespace App\Services\Produccion;

use App\Models\Produccion\ProdProductionLog;

class ProdLogService
{
    public function create(array $data, int $companyId, int $userId): ProdProductionLog
    {
        return ProdProductionLog::create([
            'company_id' => $companyId,
            'order_id' => (int)$data['order_id'],
            'order_operation_id' => (int)$data['order_operation_id'],
            'employee_id' => (int)$data['employee_id'],
            'work_date' => $data['work_date'],
            'shift' => $data['shift'] ?? null,
            'qty' => (float)$data['qty'],
            'rejected_qty' => (float)($data['rejected_qty'] ?? 0),
            'notes' => $data['notes'] ?? null,
            'created_by' => $userId,
        ]);
    }

    public function update(ProdProductionLog $log, array $data): ProdProductionLog
    {
        $log->update([
            'order_id' => (int)$data['order_id'],
            'order_operation_id' => (int)$data['order_operation_id'],
            'employee_id' => (int)$data['employee_id'],
            'work_date' => $data['work_date'],
            'shift' => $data['shift'] ?? null,
            'qty' => (float)$data['qty'],
            'rejected_qty' => (float)($data['rejected_qty'] ?? 0),
            'notes' => $data['notes'] ?? null,
        ]);

        return $log;
    }
}
