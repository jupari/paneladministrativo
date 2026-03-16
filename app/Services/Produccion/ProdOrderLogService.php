<?php

namespace App\Services\Produccion;

use App\Models\Produccion\ProdOrder;
use Illuminate\Support\Facades\DB;

class ProdOrderLogService
{
    /**
     * Registra producción real (logs) para 1 operación de orden y múltiples empleados.
     * Retorna cuántos registros se insertaron.
     */
    public function storeLogs(
        ProdOrder $order,
        int $companyId,
        int $orderOperationId,
        array $employeeIds,
        float $qty,
        string $workedAt,
        ?string $notes,
        int $userId
    ): int {
        if ((int)$order->company_id !== (int)$companyId) {
            throw new \RuntimeException('La orden no pertenece a la empresa.');
        }

        return DB::transaction(function () use (
            $order,
            $companyId,
            $orderOperationId,
            $employeeIds,
            $qty,
            $workedAt,
            $notes,
            $userId
        ) {
            // validar operación de orden pertenece a la orden
            $poo = DB::table('production_order_activities')
                ->where('id', $orderOperationId)
                ->where('production_order_id', $order->id)
                ->first();

            if (!$poo) {
                throw new \RuntimeException('La operación seleccionada no pertenece a la orden.');
            }

            // done actual
            $done = (float) DB::table('production_operations')
                ->where('company_id', $companyId)
                ->where('production_order_id', $order->id)
                ->where('order_operation_id', $orderOperationId)
                ->sum('quantity');

            $required = (float) $poo->required_qty;
            $remaining = max($required - $done, 0);

            // Política: bloquear excedente (puedes cambiarlo)
            $incomingTotal = $qty * count($employeeIds);
            if ($required > 0 && $incomingTotal > $remaining) {
                throw new \RuntimeException("Excede lo pendiente de esta operación. Pendiente: {$remaining}");
            }

            $rows = [];
            foreach ($employeeIds as $empId) {
                $rows[] = [
                    'company_id' => $companyId,
                    'production_order_id' => $order->id,
                    'order_operation_id' => $orderOperationId,
                    'activity_id' => (int)$poo->activity_id,
                    'employee_id' => (int)$empId,
                    'quantity' => $qty,
                    'registered_at' => $workedAt,
                    'notes' => $notes,
                    'created_by' => $userId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            DB::table('production_operations')->insert($rows);

            // estado “computado” (opcional actualizar columna status)
            $newDone = $done + $incomingTotal;
            $newStatus = ($newDone <= 0) ? 'PENDING' : (($newDone >= $required) ? 'DONE' : 'IN_PROGRESS');

            DB::table('production_order_activities')
                ->where('id', $orderOperationId)
                ->update(['status' => $newStatus, 'updated_at' => now()]);

            // Orden a IN_PROGRESS si estaba en DRAFT
            if ($order->status === 'DRAFT') {
                $order->update(['status' => 'IN_PROGRESS']);
            }

            return count($rows);
        });
    }
}
