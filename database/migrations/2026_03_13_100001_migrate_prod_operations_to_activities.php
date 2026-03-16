<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migra los datos de prod_operations → activities.
 *
 * ⚠ IMPORTANTE: Después de migrar, las tablas que antes referenciaban
 *   prod_operations.id (ej: prod_order_operations.operation_id,
 *   prod_operation_product_rates.operation_id) deben actualizarse
 *   para apuntar al nuevo ID en activities.
 *
 * Esta migración:
 *   1. Copia cada registro de prod_operations a activities
 *      (evitando códigos duplicados por company_id).
 *   2. Guarda el ID original en activities.legacy_prod_operation_id.
 *   3. Re-apunta prod_order_operations.operation_id al nuevo ID.
 *   4. Re-apunta prod_operation_product_rates.operation_id al nuevo ID.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Solo ejecutar si la tabla legacy existe
        if (! Schema::hasTable('prod_operations')) {
            return;
        }

        $rows = DB::table('prod_operations')->get();

        foreach ($rows as $row) {
            // Verificar si ya existe en activities (por company_id + code)
            $existing = DB::table('activities')
                ->where('company_id', $row->company_id)
                ->where('code', $row->code)
                ->first();

            if ($existing) {
                // Ya existe → solo guardar el mapeo
                $newId = $existing->id;

                // Marcar el legacy_id si no está seteado
                if (empty($existing->legacy_prod_operation_id)) {
                    DB::table('activities')
                        ->where('id', $newId)
                        ->update(['legacy_prod_operation_id' => $row->id]);
                }
            } else {
                // Insertar nueva actividad
                $newId = DB::table('activities')->insertGetId([
                    'company_id'                => $row->company_id,
                    'code'                      => $row->code,
                    'name'                      => $row->name,
                    'description'               => $row->description,
                    'unit_price'                => 0,
                    'is_active'                 => (bool) $row->is_active,
                    'legacy_prod_operation_id'  => $row->id,
                    'created_at'                => $row->created_at ?? now(),
                    'updated_at'                => now(),
                ]);
            }

            // Re-apuntar FKs de tablas dependientes al nuevo ID
            if ((int) $row->id !== (int) $newId) {
                if (Schema::hasTable('prod_order_operations')) {
                    DB::table('prod_order_operations')
                        ->where('operation_id', $row->id)
                        ->update(['operation_id' => $newId]);
                }

                if (Schema::hasTable('prod_operation_product_rates')) {
                    DB::table('prod_operation_product_rates')
                        ->where('operation_id', $row->id)
                        ->update(['operation_id' => $newId]);
                }
            }
        }
    }

    public function down(): void
    {
        // Revertir: borrar los registros migrados (con legacy_prod_operation_id)
        DB::table('activities')
            ->whereNotNull('legacy_prod_operation_id')
            ->delete();
    }
};
