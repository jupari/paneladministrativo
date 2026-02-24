<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inv_saldos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('producto_id');
            $table->string('codigo_producto', 50);
            $table->string('talla', 20)->nullable();
            $table->string('color', 50)->nullable();
            $table->unsignedBigInteger('bodega_id');
            $table->decimal('saldo', 10, 3)->default(0);
            $table->decimal('ultimo_costo', 15, 2)->default(0);
            $table->timestamps();

            // Foreign keys se agregarán después
            // $table->foreign('producto_id')->references('id')->on('inv_productos');
            // $table->foreign('bodega_id')->references('id')->on('inv_bodegas');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inv_saldos');
    }
};
