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
        Schema::create('ord_cotizaciones_conceptos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cotizacion_id')->constrained('ord_cotizacion')->onDelete('cascade');
            $table->unsignedBigInteger('concepto_id'); // Sin FK por ahora
            $table->decimal('porcentaje', 5, 2)->default(0.00); // Porcentaje del concepto (IVA: 19%)
            $table->decimal('valor', 12, 2)->default(0.00); // Valor calculado del concepto
            $table->decimal('base_calculo', 12, 2)->default(0.00); // Base sobre la cual se calcula
            $table->boolean('incluido_precio')->default(false); // Si está incluido en el precio unitario
            $table->integer('orden')->default(1); // Para ordenar conceptos
            $table->timestamps();

            // Índices
            $table->index(['cotizacion_id', 'concepto_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ord_cotizaciones_conceptos');
    }
};
