<?php

namespace App\Services\Produccion;

use App\Models\ProductionOperation;
use Illuminate\Support\Facades\DB;

class ProdLogService
{
    public function create(array $data, int $companyId, int $userId): ProductionOperation
    {
        $orderOperationId = (int) $data['order_operation_id'];

        // Resolver activity_id desde production_order_activities
        $activityId = (int) DB::table('production_order_activities')
            ->where('id', $orderOperationId)
            ->value('activity_id');

        return ProductionOperation::create([
            'company_id'          => $companyId,
            'production_order_id' => (int) $data['order_id'],
            'order_operation_id'  => $orderOperationId,
            'activity_id'         => $activityId,
            'employee_id'         => (int) $data['employee_id'],
            'work_date'           => $data['work_date'],
            'shift'               => $data['shift'] ?? null,
            'quantity'            => (float) $data['qty'],
            'rejected_qty'        => (float) ($data['rejected_qty'] ?? 0),
            'notes'               => $data['notes'] ?? null,
            'created_by'          => $userId,
            'registered_at'       => now(),
        ]);
    }

    public function update(ProductionOperation $log, array $data): ProductionOperation
    {
        $orderOperationId = (int) $data['order_operation_id'];

        $activityId = (int) DB::table('production_order_activities')
            ->where('id', $orderOperationId)
            ->value('activity_id');

        $log->update([
            'production_order_id' => (int) $data['order_id'],
            'order_operation_id'  => $orderOperationId,
            'activity_id'         => $activityId,
            'employee_id'         => (int) $data['employee_id'],
            'work_date'           => $data['work_date'],
            'shift'               => $data['shift'] ?? null,
            'quantity'            => (float) $data['qty'],
            'rejected_qty'        => (float) ($data['rejected_qty'] ?? 0),
            'notes'               => $data['notes'] ?? null,
        ]);

        return $log;
    }
}
