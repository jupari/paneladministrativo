<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fase 3: Ampliar production_order_activities con columnas legacy de prod_order_operations.
 *
 * Columnas nuevas:
 *   - qty_per_unit   → cantidad de esta operación por unidad de la orden
 *   - required_qty   → total_units × qty_per_unit (desnormalizado para performance)
 *   - status         → PENDING | IN_PROGRESS | DONE (computado desde worker logs)
 *   - legacy_prod_order_operation_id → trazabilidad migración
 *
 * Mapeo de columnas existentes:
 *   prod_order_operations.order_id     → production_order_activities.production_order_id
 *   prod_order_operations.operation_id → production_order_activities.activity_id
 *   prod_order_operations.seq          → production_order_activities.position
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('production_order_activities', function (Blueprint $table) {
            $table->decimal('qty_per_unit', 12, 4)->nullable()->after('position');
            $table->decimal('required_qty', 12, 4)->nullable()->after('qty_per_unit');
            $table->string('status', 20)->default('PENDING')->after('required_qty');
            $table->unsignedBigInteger('legacy_prod_order_operation_id')->nullable()->after('updated_at');
        });
    }

    public function down(): void
    {
        Schema::table('production_order_activities', function (Blueprint $table) {
            $table->dropColumn([
                'qty_per_unit',
                'required_qty',
                'status',
                'legacy_prod_order_operation_id',
            ]);
        });
    }
};
