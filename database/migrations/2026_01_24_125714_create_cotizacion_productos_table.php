<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ord_cotizacion_productos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cotizacion_id');
            $table->unsignedBigInteger('producto_id')->nullable();
            $table->string('nombre', 200);
            $table->text('descripcion')->nullable();
            $table->string('codigo', 50)->nullable();
            $table->string('unidad_medida', 20)->nullable();
            $table->decimal('cantidad', 10, 3)->default(1);
            $table->decimal('valor_unitario', 15, 2)->default(0);
            $table->decimal('descuento_porcentaje', 5, 2)->default(0);
            $table->decimal('descuento_valor', 15, 2)->default(0);
            $table->decimal('valor_total', 15, 2)->default(0);
            $table->text('observaciones')->nullable();
            $table->integer('orden')->default(0);
            $table->boolean('active')->default(true);
            $table->unsignedBigInteger('categoria_id')->nullable();
            $table->unsignedBigInteger('cargo_id')->nullable();
            $table->decimal('costo_dia', 15, 2)->default(0);
            $table->decimal('costo_hora', 15, 2)->default(0);
            $table->decimal('costo_unitario', 15, 2)->default(0);
            $table->integer('dias_diurnos')->default(0);
            $table->integer('dias_nocturnos')->default(0);
            $table->integer('dias_remunerados_diurnos')->default(0);
            $table->integer('dias_remunerados_nocturnos')->default(0);
            $table->integer('dominicales_diurnos')->default(0);
            $table->integer('dominicales_nocturnos')->default(0);
            $table->integer('horas_diurnas')->default(0);
            $table->integer('horas_remuneradas')->default(0);
            $table->boolean('incluir_dominicales')->default(false);
            $table->string('tipo_costo', 50)->default('unitario');
            $table->timestamps();

            // No se agregan foreign keys por ahora para evitar conflictos
            // $table->foreign('cotizacion_id')->references('id')->on('ord_cotizacion')->onDelete('cascade');
            // $table->foreign('producto_id')->references('id')->on('inv_productos')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ord_cotizacion_productos');
    }
};
