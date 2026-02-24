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
        Schema::create('ord_cotizacion_sub_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cotizacion_item_id')->constrained('ord_cotizaciones_items')->onDelete('cascade');
            $table->string('nombre', 255); // Nombre del sub-ítem
            $table->text('descripcion')->nullable();
            $table->string('codigo', 50)->nullable();
            $table->string('unidad_medida', 20)->default('UND');
            $table->decimal('cantidad', 10, 3)->default(1.000);
            $table->decimal('valor_unitario', 12, 2)->default(0.00);
            $table->decimal('valor_total', 12, 2)->default(0.00);
            $table->text('observaciones')->nullable();
            $table->integer('orden')->default(1);
            $table->boolean('active')->default(true);
            $table->timestamps();

            // Índices
            $table->index(['cotizacion_item_id', 'orden']);
            $table->index(['cotizacion_item_id', 'active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ord_cotizacion_sub_items');
    }
};
