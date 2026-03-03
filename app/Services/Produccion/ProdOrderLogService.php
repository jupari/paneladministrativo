<?php

namespace App\Services\Produccion;

use App\Models\Produccion\ProdOrder;
use App\Models\Produccion\ProdWorkerLog;
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
            $poo = DB::table('prod_order_operations')
                ->where('id', $orderOperationId)
                ->where('order_id', $order->id)
                ->first();

            if (!$poo) {
                throw new \RuntimeException('La operación seleccionada no pertenece a la orden.');
            }

            // done actual
            $done = (float) DB::table('prod_worker_logs')
                ->where('company_id', $companyId)
                ->where('order_id', $order->id)
                ->where('order_operation_id', $orderOperationId)
                ->sum('qty');

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
                    'order_id' => $order->id,
                    'order_operation_id' => $orderOperationId,
                    'operation_id' => (int)$poo->operation_id,
                    'employee_id' => (int)$empId,
                    'qty' => $qty,
                    'worked_at' => $workedAt,
                    'notes' => $notes,
                    'created_by' => $userId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            DB::table('prod_worker_logs')->insert($rows);

            // estado “computado” (opcional actualizar columna status)
            $newDone = $done + $incomingTotal;
            $newStatus = ($newDone <= 0) ? 'PENDING' : (($newDone >= $required) ? 'DONE' : 'IN_PROGRESS');

            DB::table('prod_order_operations')
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
