<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ord_cotizacion_productos', function (Blueprint $table) {
            if (!Schema::hasColumn('ord_cotizacion_productos', 'cotizacion_item_id')) {
                $table->unsignedBigInteger('cotizacion_item_id')->nullable()->after('cotizacion_id');
            }
            if (!Schema::hasColumn('ord_cotizacion_productos', 'cotizacion_subitem_id')) {
                $table->unsignedBigInteger('cotizacion_subitem_id')->nullable()->after('cotizacion_item_id');
            }
            if (!Schema::hasColumn('ord_cotizacion_productos', 'item_propio_id')) {
                $table->unsignedBigInteger('item_propio_id')->nullable()->after('cotizacion_subitem_id');
            }
            if (!Schema::hasColumn('ord_cotizacion_productos', 'parametrizacion_id')) {
                $table->unsignedBigInteger('parametrizacion_id')->nullable()->after('item_propio_id');
            }
            if (!Schema::hasColumn('ord_cotizacion_productos', 'tabla_precios_id')) {
                $table->unsignedBigInteger('tabla_precios_id')->nullable()->after('parametrizacion_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('ord_cotizacion_productos', function (Blueprint $table) {
            $columns = ['cotizacion_item_id', 'cotizacion_subitem_id', 'item_propio_id', 'parametrizacion_id', 'tabla_precios_id'];
            foreach ($columns as $col) {
                if (Schema::hasColumn('ord_cotizacion_productos', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
