<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * Fase 7 – Limpieza: renombrar tablas legacy consolidadas a _bak.
 *
 * Tablas que fueron migradas en fases anteriores:
 *   - prod_operations       → activities          (Fase 1)
 *   - prod_orders           → production_orders    (Fase 2)
 *   - prod_order_operations → production_order_activities (Fase 3)
 *   - prod_worker_logs      → production_operations (Fase 4)
 *
 * Se renombran a *_bak en lugar de DROP para mayor seguridad.
 * Después de verificar en producción que todo funciona, se pueden
 * eliminar manualmente:
 *   DROP TABLE IF EXISTS prod_operations_bak, prod_orders_bak,
 *       prod_order_operations_bak, prod_worker_logs_bak;
 */
return new class extends Migration
{
    private array $tables = [
        'prod_operations',
        'prod_orders',
        'prod_order_operations',
        'prod_worker_logs',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::rename($table, $table . '_bak');
            }
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            if (Schema::hasTable($table . '_bak')) {
                Schema::rename($table . '_bak', $table);
            }
        }
    }
};
