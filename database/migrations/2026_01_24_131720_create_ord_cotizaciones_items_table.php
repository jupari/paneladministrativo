<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ord_cotizaciones_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cotizacion_id');
            $table->string('nombre', 200);
            $table->unsignedBigInteger('subitem_id')->nullable();
            $table->decimal('cantidad', 10, 3)->default(1);
            $table->decimal('valor_unitario', 15, 2)->default(0);
            $table->decimal('valor_total', 15, 2)->default(0);
            $table->text('observaciones')->nullable();
            $table->integer('orden')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();

            // Foreign keys se agregarán después
            // $table->foreign('cotizacion_id')->references('id')->on('ord_cotizacion')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ord_cotizaciones_items');
    }
};
