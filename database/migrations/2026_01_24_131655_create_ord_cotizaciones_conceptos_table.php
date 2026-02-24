<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ord_cotizaciones_conceptos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cotizacion_id');
            $table->unsignedBigInteger('concepto_id');
            $table->decimal('porcentaje', 5, 2)->default(0);
            $table->decimal('valor', 15, 2)->default(0);
            $table->timestamps();

            // Foreign keys se agregarán después
            // $table->foreign('cotizacion_id')->references('id')->on('ord_cotizacion')->onDelete('cascade');
            // $table->foreign('concepto_id')->references('id')->on('conceptos')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ord_cotizaciones_conceptos');
    }
};
