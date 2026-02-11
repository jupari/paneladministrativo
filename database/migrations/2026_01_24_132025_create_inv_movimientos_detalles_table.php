<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inv_movimientos_detalles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('movimiento_id');
            $table->string('num_doc', 50);
            $table->unsignedBigInteger('producto_id');
            $table->string('codigo_producto', 50)->nullable();
            $table->string('talla', 20)->nullable();
            $table->string('color', 50)->nullable();
            $table->unsignedBigInteger('bodega_id');
            $table->string('tipo', 50);
            $table->decimal('cantidad', 10, 3)->default(0);
            $table->decimal('costo_unitario', 15, 2)->default(0);
            $table->decimal('costo_total', 15, 2)->default(0);
            $table->string('referencia', 100)->nullable();
            $table->unsignedBigInteger('usuario_id');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            // Foreign keys se agregarán después
            // $table->foreign('movimiento_id')->references('id')->on('inv_movimientos');
            // $table->foreign('producto_id')->references('id')->on('inv_productos');
            // $table->foreign('bodega_id')->references('id')->on('inv_bodegas');
            // $table->foreign('usuario_id')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inv_movimientos_detalles');
    }
};
