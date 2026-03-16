<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Fase 4 – Migración de datos: prod_worker_logs → production_operations.
 *
 * Mapeo de columnas:
 *   prod_worker_logs.order_id            → production_operations.production_order_id
 *   prod_worker_logs.operation_id        → production_operations.activity_id
 *   prod_worker_logs.qty                 → production_operations.quantity
 *   prod_worker_logs.worked_at           → production_operations.registered_at
 *   prod_worker_logs.company_id          → production_operations.company_id    (nueva)
 *   prod_worker_logs.employee_id         → production_operations.employee_id   (nueva)
 *   prod_worker_logs.order_operation_id  → production_operations.order_operation_id (nueva)
 *   prod_worker_logs.notes               → production_operations.notes         (nueva)
 *   prod_worker_logs.created_by          → production_operations.created_by    (nueva)
 *
 * Columnas API que quedan NULL en registros legacy:
 *   workshop_id, workshop_operator_id, user_id, idempotency_key
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!$this->tableExists('prod_worker_logs')) {
            return;
        }

        $legacy = DB::table('prod_worker_logs')->get();

        foreach ($legacy as $row) {
            DB::table('production_operations')->insert([
                'company_id'                 => $row->company_id ?? null,
                'production_order_id'        => $row->order_id,
                'activity_id'                => $row->operation_id,
                'order_operation_id'         => $row->order_operation_id ?? null,
                'employee_id'                => $row->employee_id ?? null,
                'quantity'                   => $row->qty ?? 0,
                'registered_at'              => $row->worked_at ?? now(),
                'notes'                      => $row->notes ?? null,
                'created_by'                 => $row->created_by ?? null,
                'legacy_prod_worker_log_id'  => $row->id,
                'created_at'                 => $row->created_at,
                'updated_at'                 => $row->updated_at,
                // Columnas API - NULL para registros legacy
                'workshop_id'                => null,
                'workshop_operator_id'       => null,
                'user_id'                    => null,
                'idempotency_key'            => null,
            ]);
        }
    }

    public function down(): void
    {
        DB::table('production_operations')
            ->whereNotNull('legacy_prod_worker_log_id')
            ->delete();
    }

    private function tableExists(string $table): bool
    {
        return Schema::hasTable($table);
    }
};
