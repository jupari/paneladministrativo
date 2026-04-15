<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('ord_cotizaciones_listas')) {
            Schema::create('ord_cotizaciones_listas', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('cotizacion_id');
                $table->unsignedBigInteger('cotizacion_producto_id')->nullable();
                $table->unsignedBigInteger('novedad_detalle_id');
                $table->decimal('valor', 15, 2)->default(0);
                $table->decimal('cantidad', 10, 3)->default(0);
                $table->decimal('subtotal', 15, 2)->default(0);
                $table->timestamps();

                $table->foreign('cotizacion_id')->references('id')->on('ord_cotizacion')->onDelete('cascade');
                $table->foreign('cotizacion_producto_id')->references('id')->on('ord_cotizacion_productos')->onDelete('set null');
                $table->foreign('novedad_detalle_id')->references('id')->on('novedades_detalle')->onDelete('cascade');
            });
        } else {
            // Add cotizacion_producto_id if not exists
            if (!Schema::hasColumn('ord_cotizaciones_listas', 'cotizacion_producto_id')) {
                Schema::table('ord_cotizaciones_listas', function (Blueprint $table) {
                    $table->unsignedBigInteger('cotizacion_producto_id')->nullable()->after('cotizacion_id');
                });
            }
            if (!Schema::hasColumn('ord_cotizaciones_listas', 'subtotal')) {
                Schema::table('ord_cotizaciones_listas', function (Blueprint $table) {
                    $table->decimal('subtotal', 15, 2)->default(0)->after('cantidad');
                });
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ord_cotizaciones_listas');
    }
};
