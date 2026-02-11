<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ord_cotizacion', function (Blueprint $table) {
            $table->id();
            $table->string('num_documento', 50)->unique();
            $table->date('fecha');
            $table->string('tipo', 50)->nullable();
            $table->string('proyecto', 200)->nullable();
            $table->unsignedBigInteger('autorizacion_id')->nullable();
            $table->string('doc_origen', 50)->nullable();
            $table->integer('version')->default(1);
            $table->unsignedBigInteger('tercero_id');
            $table->unsignedBigInteger('tercero_sucursal_id')->nullable();
            $table->unsignedBigInteger('tercero_contacto_id')->nullable();
            $table->text('observacion')->nullable();
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('descuento', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->decimal('total_impuesto', 15, 2)->default(0);
            $table->unsignedBigInteger('estado_id')->default(1); // Borrador por defecto
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('vendedor_id')->nullable();
            $table->date('fecha_vencimiento')->nullable();
            $table->datetime('fecha_envio')->nullable();
            $table->datetime('fecha_respuesta')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            // Foreign keys se agregarán después si es necesario
            // $table->foreign('autorizacion_id')->references('id')->on('autorizaciones')->onDelete('set null');
            // $table->foreign('tercero_id')->references('id')->on('terceros')->onDelete('cascade');
            // $table->foreign('estado_id')->references('id')->on('estados_cotizacion')->onDelete('cascade');
            // $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            // $table->foreign('vendedor_id')->references('id')->on('vendedores')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ord_cotizacion');
    }
};
