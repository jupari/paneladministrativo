<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ord_cotizaciones_observaciones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cotizacion_id');
            $table->unsignedBigInteger('observacion_id');
            $table->boolean('active')->default(true);
            $table->timestamps();

            // Foreign keys se agregarán después
            // $table->foreign('cotizacion_id')->references('id')->on('ord_cotizacion')->onDelete('cascade');
            // $table->foreign('observacion_id')->references('id')->on('ord_observaciones')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ord_cotizaciones_observaciones');
    }
};
