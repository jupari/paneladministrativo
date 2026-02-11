<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prd_fichas_tecnicas', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 50)->unique();
            $table->string('nombre', 200);
            $table->string('coleccion', 100)->nullable();
            $table->date('fecha')->nullable();
            $table->text('observacion')->nullable();
            $table->string('codigo_barras', 100)->nullable();
            $table->string('codigo_producto_terminado', 50)->nullable();
            $table->unsignedBigInteger('estado_ficha_tecnica_id')->default(1);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            // Foreign key se agregará después
            // $table->foreign('estado_ficha_tecnica_id')->references('id')->on('estado_ficha_tecnicas');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prd_fichas_tecnicas');
    }
};
