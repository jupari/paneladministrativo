<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ord_cotizaciones_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cotizacion_id')->constrained('ord_cotizacion')->onDelete('cascade');
            $table->string('nombre', 255); // Nombre del ítem/producto/servicio
            $table->text('descripcion')->nullable(); // Descripción detallada
            $table->string('codigo', 50)->nullable(); // Código del producto/servicio
            $table->string('unidad_medida', 20)->default('UND'); // UND, KG, M2, M3, etc.
            $table->decimal('cantidad', 10, 3)->default(1.000);
            $table->decimal('valor_unitario', 12, 2)->default(0.00);
            $table->decimal('descuento_porcentaje', 5, 2)->default(0.00);
            $table->decimal('descuento_valor', 12, 2)->default(0.00);
            $table->decimal('valor_total', 12, 2)->default(0.00);
            $table->text('observaciones')->nullable();
            $table->integer('orden')->default(1); // Para ordenar los items
            $table->boolean('active')->default(true);
            $table->timestamps();

            // Índices
            $table->index(['cotizacion_id', 'orden']);
            $table->index(['cotizacion_id', 'active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ord_cotizaciones_items');
    }
};
