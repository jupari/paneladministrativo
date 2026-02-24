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
        Schema::create('ord_cotizacion', function (Blueprint $table) {
            $table->id();
            $table->string('num_documento', 50)->unique(); // CT-2024-001
            $table->date('fecha');
            $table->string('tipo', 20)->default('Standard'); // Standard, Express, Personalizada
            $table->string('proyecto', 255)->nullable(); // Nombre del proyecto
            $table->unsignedBigInteger('autorizacion_id')->nullable(); // Temporal sin FK
            $table->string('doc_origen', 100)->nullable(); // Documento que origina la cotización
            $table->integer('version')->default(1); // Versión de la cotización
            
            // Relaciones con terceros (sin FK por ahora)
            $table->unsignedBigInteger('tercero_id');
            $table->unsignedBigInteger('tercero_sucursal_id')->nullable();
            $table->unsignedBigInteger('tercero_contacto_id')->nullable();
            
            // Información financiera
            $table->text('observacion')->nullable();
            $table->decimal('subtotal', 12, 2)->default(0.00);
            $table->decimal('descuento', 12, 2)->default(0.00);
            $table->decimal('total', 12, 2)->default(0.00);
            $table->decimal('total_impuesto', 12, 2)->default(0.00);
            
            // Estado y control
            $table->foreignId('estado_id')->default(1)->constrained('estados_cotizacion')->onDelete('restrict');
            $table->unsignedBigInteger('user_id'); // Sin FK por ahora
            $table->unsignedBigInteger('vendedor_id')->nullable(); // Sin FK por ahora
            
            // Fechas importantes
            $table->date('fecha_vencimiento')->nullable();
            $table->datetime('fecha_envio')->nullable();
            $table->datetime('fecha_respuesta')->nullable();
            
            $table->timestamps();
            
            // Índices
            $table->index(['estado_id', 'fecha']);
            $table->index(['tercero_id', 'fecha']);
            $table->index(['vendedor_id', 'fecha']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ord_cotizacion');
    }
};
