<?php

namespace App\Console\Commands;

use App\Models\ProductionOrder;
use Illuminate\Console\Command;

class RecalculateCompletedUnits extends Command
{
    protected $signature = 'orders:recalculate-units';
    protected $description = 'Recalcula completed_units en production_orders según actividades requeridas y prendas dañadas';

    public function handle(): int
    {
        $orders = ProductionOrder::whereNull('deleted_at')->get();

        $updated = 0;

        foreach ($orders as $order) {
            $before = (int) $order->completed_units;
            $order->recalculateCompletedUnits();
            $order->refresh();
            $after = (int) $order->completed_units;

            if ($before !== $after) {
                $this->line("  Orden #{$order->id} ({$order->order_code}): {$before} → {$after}");
                $updated++;
            }
        }

        $this->info("Recalculación completada. {$updated} órdenes actualizadas de {$orders->count()} totales.");

        return self::SUCCESS;
    }
}
