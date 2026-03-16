<?php

namespace App\Services\Produccion;

use App\Models\Produccion\ProdOrder;
use App\Models\Produccion\ProdWorkerSettlement;
use App\Models\ProductionOrder;
use Illuminate\Support\Facades\DB;

class ProdSettlementService
{
    /**
     * Calcula settlement por empleado para la orden.
     * - qty pagable = SUM(quantity - rejected_qty) por employee + order_operation
     * - rate = tarifa vigente por product_id + operation_id
     */
    public function calculate(int $orderId, int $companyId): int
    {
        $order = ProductionOrder::where('company_id', $companyId)->findOrFail($orderId);

        // Agrupar logs por operario + operación de la orden
        // Resuelve employee_id: directo → vía workshop_operators → fallback workshop_operator_id
        $rows = DB::table('production_operations as l')
            ->leftJoin('workshop_operators as wo', 'wo.id', '=', 'l.workshop_operator_id')
            ->leftJoin('production_order_activities as oo', 'oo.id', '=', 'l.order_operation_id')
            ->leftJoin('production_order_activities as oo_fb', function ($j) use ($orderId) {
                $j->on('oo_fb.activity_id', '=', 'l.activity_id')
                  ->where('oo_fb.production_order_id', '=', $orderId);
            })
            ->where('l.production_order_id', $orderId)
            ->where(function ($q) use ($companyId) {
                $q->where('l.company_id', $companyId)
                  ->orWhereNull('l.company_id');
            })
            ->groupByRaw('
                COALESCE(l.order_operation_id, oo_fb.id),
                COALESCE(l.employee_id, wo.employee_id, l.workshop_operator_id),
                COALESCE(oo.activity_id, oo_fb.activity_id)
            ')
            ->selectRaw('
                COALESCE(l.order_operation_id, oo_fb.id) as order_operation_id,
                COALESCE(l.employee_id, wo.employee_id, l.workshop_operator_id) as employee_id,
                COALESCE(oo.activity_id, oo_fb.activity_id) as operation_id,
                SUM(l.quantity) as sum_qty,
                SUM(COALESCE(l.rejected_qty,0)) as sum_rejected,
                SUM(l.quantity - COALESCE(l.rejected_qty,0)) as accepted_qty
            ')
            ->havingRaw('employee_id IS NOT NULL AND order_operation_id IS NOT NULL')
            ->get();

        $upserts = 0;

        foreach ($rows as $r) {
            $accepted = (float) $r->accepted_qty;
            if ($accepted <= 0) continue;

            // Tarifa vigente: primero prod_operation_product_rates, fallback activities.unit_price
            $rate = DB::table('prod_operation_product_rates')
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

            if ($rate === null || (float)$rate <= 0) {
                $rate = DB::table('activities')
                    ->where('id', (int)$r->operation_id)
                    ->value('unit_price');
            }

            $rate = (float)($rate ?? 0);
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
                    'status' => 'APPROVED',
                ]
            );

            $upserts++;
        }

        return $upserts;
    }

    public function calculateOrderSettlements(int $orderId, int $companyId): int
    {
        $order = ProductionOrder::query()
            ->where('company_id', $companyId)
            ->findOrFail($orderId);

        // 1) Logs agrupados por order_operation_id + employee_id
        //    Incluye operaciones móviles: resuelve employee_id vía workshop_operators.employee_id
        //    Para filas API sin order_operation_id, lo resolvemos desde production_order_activities
        $grouped = DB::table('production_operations as l')
            ->leftJoin('workshop_operators as wo', 'wo.id', '=', 'l.workshop_operator_id')
            ->leftJoin('production_order_activities as poa', function ($j) use ($order) {
                $j->on('poa.activity_id', '=', 'l.activity_id')
                  ->where('poa.production_order_id', '=', $order->id);
            })
            ->where('l.production_order_id', $order->id)
            ->where(function ($q) use ($companyId) {
                $q->where('l.company_id', $companyId)
                  ->orWhereNull('l.company_id');
            })
            ->whereRaw('COALESCE(l.employee_id, wo.employee_id) IS NOT NULL')
            ->selectRaw('
                COALESCE(l.order_operation_id, poa.id) as order_operation_id,
                COALESCE(l.employee_id, wo.employee_id) as employee_id,
                SUM(l.quantity - l.rejected_qty) as qty
            ')
            ->groupByRaw('COALESCE(l.order_operation_id, poa.id), COALESCE(l.employee_id, wo.employee_id)')
            ->get();

        if ($grouped->isEmpty()) {
            return 0;
        }

        // 2) Mapa order_operation_id -> operation_id (para buscar tarifa)
        $opMap = DB::table('production_order_activities')
            ->where('production_order_id', $order->id)
            ->pluck('activity_id', 'id'); // [order_operation_id => activity_id]

        // 3) Tarifas vigentes para el producto de la orden
        $today = now()->toDateString();

        $rates = DB::table('prod_operation_product_rates as r')
            ->where('r.company_id', $companyId)
            ->where('r.product_id', $order->product_id)
            ->where('r.is_active', 1)
            ->where(function ($q) use ($today) {
                $q->whereNull('r.valid_from')->orWhereDate('r.valid_from', '<=', $today);
            })
            ->where(function ($q) use ($today) {
                $q->whereNull('r.valid_to')->orWhereDate('r.valid_to', '>=', $today);
            })
            ->select(['r.operation_id', 'r.amount'])
            ->get()
            ->keyBy('operation_id');

        // 4) Upsert settlements
        return DB::transaction(function () use ($grouped, $opMap, $rates, $companyId, $order) {

            $affected = 0;

            foreach ($grouped as $g) {
                $orderOperationId = (int)$g->order_operation_id;
                $employeeId = (int)$g->employee_id;
                $qty = (float)$g->qty;

                $operationId = (int)($opMap[$orderOperationId] ?? 0);
                if ($operationId <= 0) continue;

                $rateRow = $rates->get($operationId);
                $rate = (float)($rateRow->amount ?? 0);

                // Fallback a activities.unit_price si no hay tarifa configurada
                if ($rate <= 0) {
                    $rate = (float) DB::table('activities')
                        ->where('id', $operationId)
                        ->value('unit_price');
                }

                if ($rate <= 0) {
                    continue;
                }

                $gross = round($qty * $rate, 2);

                // No tocar si ya fue sincronizado a nómina
                $existing = DB::table('prod_worker_settlements')
                    ->where('company_id', $companyId)
                    ->where('order_id', $order->id)
                    ->where('order_operation_id', $orderOperationId)
                    ->where('employee_id', $employeeId)
                    ->first();

                if ($existing && $existing->status === 'SYNCED_TO_NOMINA') {
                    continue;
                }

                if ($existing) {
                    DB::table('prod_worker_settlements')
                        ->where('id', $existing->id)
                        ->update([
                            'qty' => $qty,
                            'rate' => $rate,
                            'gross_amount' => $gross,
                            'status' => 'DRAFT',
                            'updated_at' => now(),
                        ]);
                } else {
                    DB::table('prod_worker_settlements')->insert([
                        'company_id' => $companyId,
                        'order_id' => $order->id,
                        'order_operation_id' => $orderOperationId,
                        'employee_id' => $employeeId,
                        'qty' => $qty,
                        'rate' => $rate,
                        'gross_amount' => $gross,
                        'status' => 'DRAFT',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                $affected++;
            }

            return $affected;
        });
    }
}




