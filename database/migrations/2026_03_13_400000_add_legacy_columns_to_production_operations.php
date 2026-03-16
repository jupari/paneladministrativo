<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Fase 4: Ampliar production_operations para absorber prod_worker_logs.
 *
 * Cambios:
 *   1. Hacer nullable: workshop_id, workshop_operator_id, user_id
 *      (registros legacy del panel admin no tienen estos campos).
 *   2. Cambiar quantity de UNSIGNED INT → DECIMAL(12,4)
 *      (legacy maneja cantidades fraccionarias).
 *   3. Agregar columnas legacy:
 *      - company_id       → aislamiento por empresa
 *      - employee_id      → empleado del sistema de nómina (alternativa a workshop_operator_id)
 *      - order_operation_id → enlace a production_order_activities (para settlements)
 *      - notes            → observaciones del registro
 *      - created_by       → usuario que registró desde el admin
 *      - legacy_prod_worker_log_id → trazabilidad migración
 *
 * Mapeo de columnas existentes (para queries):
 *   prod_worker_logs.order_id       → production_operations.production_order_id
 *   prod_worker_logs.operation_id   → production_operations.activity_id
 *   prod_worker_logs.qty            → production_operations.quantity
 *   prod_worker_logs.worked_at      → production_operations.registered_at
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. Drop FK constraints que impiden hacer nullable
        Schema::table('production_operations', function (Blueprint $table) {
            $table->dropForeign(['workshop_id']);
            $table->dropForeign(['workshop_operator_id']);
            $table->dropForeign(['user_id']);
        });

        // 2. Hacer columnas nullable + cambiar quantity a decimal
        DB::statement("ALTER TABLE production_operations MODIFY workshop_id BIGINT UNSIGNED NULL");
        DB::statement("ALTER TABLE production_operations MODIFY workshop_operator_id BIGINT UNSIGNED NULL");
        DB::statement("ALTER TABLE production_operations MODIFY user_id BIGINT UNSIGNED NULL");
        DB::statement("ALTER TABLE production_operations MODIFY quantity DECIMAL(12,4) UNSIGNED NOT NULL DEFAULT 0");

        // 3. Re-agregar FK constraints (ahora permiten NULL)
        Schema::table('production_operations', function (Blueprint $table) {
            $table->foreign('workshop_id')->references('id')->on('workshops')->nullOnDelete();
            $table->foreign('workshop_operator_id')->references('id')->on('workshop_operators')->nullOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });

        // 4. Agregar columnas legacy
        Schema::table('production_operations', function (Blueprint $table) {
            $table->unsignedBigInteger('company_id')->nullable()->after('id');
            $table->unsignedBigInteger('employee_id')->nullable()->after('workshop_operator_id');
            $table->unsignedBigInteger('order_operation_id')->nullable()->after('activity_id');
            $table->string('notes', 255)->nullable()->after('idempotency_key');
            $table->unsignedBigInteger('created_by')->nullable()->after('notes');
            $table->unsignedBigInteger('legacy_prod_worker_log_id')->nullable()->after('updated_at');

            $table->index(['company_id', 'production_order_id', 'order_operation_id'], 'po_company_order_oo_idx');
        });
    }

    public function down(): void
    {
        Schema::table('production_operations', function (Blueprint $table) {
            $table->dropIndex('po_company_order_oo_idx');
            $table->dropColumn([
                'company_id', 'employee_id', 'order_operation_id',
                'notes', 'created_by', 'legacy_prod_worker_log_id',
            ]);
        });

        // Restaurar FK constraints originales
        Schema::table('production_operations', function (Blueprint $table) {
            $table->dropForeign(['workshop_id']);
            $table->dropForeign(['workshop_operator_id']);
            $table->dropForeign(['user_id']);
        });

        DB::statement("ALTER TABLE production_operations MODIFY workshop_id BIGINT UNSIGNED NOT NULL");
        DB::statement("ALTER TABLE production_operations MODIFY workshop_operator_id BIGINT UNSIGNED NOT NULL");
        DB::statement("ALTER TABLE production_operations MODIFY user_id BIGINT UNSIGNED NOT NULL");
        DB::statement("ALTER TABLE production_operations MODIFY quantity INT UNSIGNED NOT NULL DEFAULT 0");

        Schema::table('production_operations', function (Blueprint $table) {
            $table->foreign('workshop_id')->references('id')->on('workshops');
            $table->foreign('workshop_operator_id')->references('id')->on('workshop_operators');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }
};
