<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ord_cotizaciones_condiciones_comerciales', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cotizacion_id');
            $table->text('tiempo_entrega')->nullable();
            $table->text('lugar_obra')->nullable();
            $table->text('duracion_oferta')->nullable();
            $table->text('garantia')->nullable();
            $table->text('forma_pago')->nullable();
            $table->text('incluye')->nullable();
            $table->text('no_incluye')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();

            // Foreign key se agregará después
            // $table->foreign('cotizacion_id')->references('id')->on('ord_cotizacion')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ord_cotizaciones_condiciones_comerciales');
    }
};
