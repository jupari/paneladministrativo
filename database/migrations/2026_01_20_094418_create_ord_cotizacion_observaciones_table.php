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
        Schema::create('ord_cotizacion_observaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cotizacion_id')->constrained('ord_cotizacion')->onDelete('cascade');
            $table->string('tipo', 50); // general, condicion_comercial, termino_legal, nota_tecnica
            $table->string('titulo', 100)->nullable();
            $table->text('observacion');
            $table->integer('orden')->default(1);
            $table->boolean('mostrar_cliente')->default(true); // Si se muestra al cliente
            $table->boolean('active')->default(true);
            $table->timestamps();

            // Índices
            $table->index(['cotizacion_id', 'tipo']);
            $table->index(['cotizacion_id', 'orden']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ord_cotizacion_observaciones');
    }
};
