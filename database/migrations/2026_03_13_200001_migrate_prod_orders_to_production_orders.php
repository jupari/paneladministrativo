<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Fase 2 – Migración de datos: prod_orders → production_orders.
 *
 * Mapeo de columnas:
 *   prod_orders.code          → production_orders.order_code
 *   prod_orders.objective_qty → production_orders.total_units
 *   prod_orders.end_date      → production_orders.deadline
 *   prod_orders.status        → production_orders.status (sin cambio, VARCHAR(20))
 *
 * Columnas que se copian directo:
 *   company_id, product_id, start_date, notes, created_by, created_at, updated_at
 *
 * Campos extra de production_orders que no existen en prod_orders:
 *   workshop_id, garment_type, garment_reference, color,
 *   completed_units, cost_per_unit, completed_at, deleted_at
 *   → se dejan en sus valores por defecto / NULL.
 *
 * Después de insertar, se re-apuntan FKs de tablas hijas:
 *   - prod_order_operations.order_id → nuevo ID en production_orders
 *   - prod_worker_logs.order_id      → nuevo ID en production_orders
 *   - prod_production_logs.order_id  → nuevo ID en production_orders
 *   - prod_worker_settlements.order_id → nuevo ID en production_orders
 */
return new class extends Migration
{
    public function up(): void
    {
        // Solo migrar si la tabla legacy existe
        if (!$this->tableExists('prod_orders')) {
            return;
        }

        $legacy = DB::table('prod_orders')->get();

        foreach ($legacy as $row) {
            $newId = DB::table('production_orders')->insertGetId([
                'company_id'            => $row->company_id ?? null,
                'product_id'            => $row->product_id ?? null,
                'order_code'            => $row->code,
                'garment_type'          => $row->code, // placeholder obligatorio (NOT NULL)
                'total_units'           => $row->objective_qty ?? 0,
                'completed_units'       => 0,
                'cost_per_unit'         => 0,
                'status'                => $row->status ?? 'DRAFT',
                'start_date'            => $row->start_date ?? now()->toDateString(),
                'deadline'              => $row->end_date ?? null,
                'notes'                 => $row->notes ?? null,
                'created_by'            => $row->created_by ?? null,
                'legacy_prod_order_id'  => $row->id,
                'created_at'            => $row->created_at,
                'updated_at'            => $row->updated_at,
            ]);

            // Re-apuntar tablas hijas al nuevo ID
            if ($this->tableExists('prod_order_operations')) {
                DB::table('prod_order_operations')
                    ->where('order_id', $row->id)
                    ->update(['order_id' => $newId]);
            }

            if ($this->tableExists('prod_worker_logs')) {
                DB::table('prod_worker_logs')
                    ->where('order_id', $row->id)
                    ->update(['order_id' => $newId]);
            }

            if ($this->tableExists('prod_production_logs')) {
                DB::table('prod_production_logs')
                    ->where('order_id', $row->id)
                    ->update(['order_id' => $newId]);
            }

            if ($this->tableExists('prod_worker_settlements')) {
                DB::table('prod_worker_settlements')
                    ->where('order_id', $row->id)
                    ->update(['order_id' => $newId]);
            }
        }
    }

    public function down(): void
    {
        // Eliminar registros migrados (se identifican por legacy_prod_order_id)
        DB::table('production_orders')
            ->whereNotNull('legacy_prod_order_id')
            ->delete();
    }

    private function tableExists(string $table): bool
    {
        return \Illuminate\Support\Facades\Schema::hasTable($table);
    }
};
