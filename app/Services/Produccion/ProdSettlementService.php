<?php

namespace App\Services\Produccion;

use App\Models\Produccion\ProdOrder;
use App\Models\Produccion\ProdWorkerSettlement;
use Illuminate\Support\Facades\DB;

class ProdSettlementService
{
    /**
     * Calcula settlement por empleado para la orden.
     * - qty pagable = SUM(qty - rejected_qty) por employee + order_operation
     * - rate = tarifa vigente por product_id + operation_id
     */
    public function calculate(int $orderId, int $companyId): int
    {
        $order = ProdOrder::where('company_id', $companyId)->findOrFail($orderId);

        // Agrupar logs por empleado + operación de la orden
        $rows = DB::table('prod_production_logs as l')
            ->join('prod_order_operations as oo', 'oo.id', '=', 'l.order_operation_id')
            ->where('l.company_id', $companyId)
            ->where('l.order_id', $orderId)
            ->groupBy('l.order_operation_id', 'l.employee_id', 'oo.operation_id')
            ->selectRaw('l.order_operation_id, l.employee_id, oo.operation_id,
                        SUM(l.qty) as sum_qty,
                        SUM(l.rejected_qty) as sum_rejected,
                        SUM(l.qty - l.rejected_qty) as accepted_qty')
            ->get();

        $upserts = 0;

        foreach ($rows as $r) {
            $accepted = (float) $r->accepted_qty;
            if ($accepted <= 0) continue;

            // Tarifa vigente por producto + operación
            $rate = (float) DB::table('prod_operation_product_rates')
                ->where('company_id', $companyId)
                ->where('product_id', $order->product_id)
                ->where('operation_id', (int)$r->operation_id)
                ->where('is_active', 1)
                ->where(function ($q) {
                    $q->whereNull('valid_from')->orWhereDate('valid_from', '<=', now()->toDateString());
                })
                ->where(function ($q) {
                    $q->whereNull('valid_to')->orWhereDate('valid_to', '>=', now()->toDateString());
                })
                ->orderByDesc('valid_from')
                ->value('amount');

            $gross = round($accepted * $rate, 2);

            ProdWorkerSettlement::updateOrCreate(
                [
                    'company_id' => $companyId,
                    'order_id' => $orderId,
                    'order_operation_id' => (int)$r->order_operation_id,
                    'employee_id' => (int)$r->employee_id,
                ],
                [
                    'qty' => $accepted,
                    'rate' => $rate,
                    'gross_amount' => $gross,
                    'status' => 'APPROVED', // si quieres: DRAFT primero
                ]
            );

            $upserts++;
        }

        return $upserts;
    }
}
