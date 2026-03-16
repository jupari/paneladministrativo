<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Fase 2: Agregar columnas de compatibilidad legacy a production_orders.
 *
 * Columnas nuevas (del esquema prod_orders):
 *   - company_id   → aislamiento por empresa
 *   - product_id   → referencia a inv_productos
 *   - notes        → observaciones de la orden
 *   - created_by   → usuario que creó la orden
 *   - end_date     → alias de deadline (para queries legacy)
 *   - legacy_prod_order_id → trazabilidad migración
 *
 * Columnas existentes que se reutilizan con mapeo:
 *   - order_code  ← prod_orders.code
 *   - total_units ← prod_orders.objective_qty
 *   - start_date  ← prod_orders.start_date
 *   - deadline    ← prod_orders.end_date
 *   - status      ← prod_orders.status (mapeado)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('production_orders', function (Blueprint $table) {
            // Aislar por empresa (los registros del API obtienen company via workshop)
            $table->unsignedBigInteger('company_id')->nullable()->after('id');
            $table->foreign('company_id')->references('id')->on('companies')->nullOnDelete();

            // Enlace al producto (legacy)
            $table->unsignedBigInteger('product_id')->nullable()->after('company_id');

            // Observaciones
            $table->string('notes', 255)->nullable()->after('cost_per_unit');

            // Usuario que creó
            $table->unsignedBigInteger('created_by')->nullable()->after('notes');

            // ID original en prod_orders (trazabilidad)
            $table->unsignedBigInteger('legacy_prod_order_id')->nullable()->after('deleted_at');

            // Quitar unique de order_code para permitir migración (puede haber colisiones)
            // Se re-agrega como unique compuesto por company
            $table->dropUnique(['order_code']);
            $table->unique(['company_id', 'order_code']);
        });

        // Cambiar status de ENUM a VARCHAR para soportar tanto valores API
        // (pending, in_progress, paused, completed, cancelled) como legacy
        // (DRAFT, IN_PROGRESS, CLOSED, CANCELLED)
        DB::statement("ALTER TABLE production_orders MODIFY COLUMN status VARCHAR(20) NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        // Restaurar ENUM original
        DB::statement("ALTER TABLE production_orders MODIFY COLUMN status ENUM('pending','in_progress','paused','completed','cancelled') NOT NULL DEFAULT 'pending'");

        Schema::table('production_orders', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropUnique(['company_id', 'order_code']);

            $table->dropColumn([
                'company_id', 'product_id', 'notes',
                'created_by', 'legacy_prod_order_id',
            ]);

            $table->unique('order_code');
        });
    }
};
