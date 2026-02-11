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
        Schema::create('ord_cotizacion_condiciones_comerciales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cotizacion_id')->constrained('ord_cotizacion')->onDelete('cascade');
            $table->string('tipo', 50); // plazo_entrega, forma_pago, garantia, validez_oferta, etc.
            $table->string('titulo', 100);
            $table->text('descripcion');
            $table->string('valor', 100)->nullable(); // Valor específico (30 días, $50,000, etc.)
            $table->integer('orden')->default(1);
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
        Schema::dropIfExists('ord_cotizacion_condiciones_comerciales');
    }
};
