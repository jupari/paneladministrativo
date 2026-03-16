<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Fase 3 – Migración de datos: prod_order_operations → production_order_activities.
 *
 * Mapeo de columnas:
 *   prod_order_operations.order_id     → production_order_activities.production_order_id
 *   prod_order_operations.operation_id → production_order_activities.activity_id
 *   prod_order_operations.seq          → production_order_activities.position
 *   prod_order_operations.qty_per_unit → production_order_activities.qty_per_unit  (nueva)
 *   prod_order_operations.required_qty → production_order_activities.required_qty  (nueva)
 *   prod_order_operations.status       → production_order_activities.status        (nueva)
 *
 * Después de insertar, re-apunta FKs de tablas hijas:
 *   - prod_worker_logs.order_operation_id       → nuevo ID
 *   - prod_production_logs.order_operation_id   → nuevo ID
 *   - prod_worker_settlements.order_operation_id → nuevo ID
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!$this->tableExists('prod_order_operations')) {
            return;
        }

        $legacy = DB::table('prod_order_operations')->get();

        foreach ($legacy as $row) {
            $newId = DB::table('production_order_activities')->insertGetId([
                'production_order_id'               => $row->order_id,
                'activity_id'                       => $row->operation_id,
                'position'                          => $row->seq ?? 0,
                'qty_per_unit'                      => $row->qty_per_unit,
                'required_qty'                      => $row->required_qty,
                'status'                            => $row->status ?? 'PENDING',
                'legacy_prod_order_operation_id'    => $row->id,
                'created_at'                        => $row->created_at,
                'updated_at'                        => $row->updated_at,
            ]);

            // Re-apuntar tablas hijas al nuevo ID
            if ($this->tableExists('prod_worker_logs')) {
                DB::table('prod_worker_logs')
                    ->where('order_operation_id', $row->id)
                    ->update(['order_operation_id' => $newId]);
            }

            if ($this->tableExists('prod_production_logs')) {
                DB::table('prod_production_logs')
                    ->where('order_operation_id', $row->id)
                    ->update(['order_operation_id' => $newId]);
            }

            if ($this->tableExists('prod_worker_settlements')) {
                DB::table('prod_worker_settlements')
                    ->where('order_operation_id', $row->id)
                    ->update(['order_operation_id' => $newId]);
            }
        }
    }

    public function down(): void
    {
        DB::table('production_order_activities')
            ->whereNotNull('legacy_prod_order_operation_id')
            ->delete();
    }

    private function tableExists(string $table): bool
    {
        return Schema::hasTable($table);
    }
};
