<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parametrizacion', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('categoria_id')->nullable();
            $table->unsignedBigInteger('cargo_id')->nullable();
            $table->unsignedBigInteger('novedad_detalle_id')->nullable();
            $table->decimal('valor_porcentaje', 5, 2)->default(0);
            $table->decimal('valor_admon', 15, 2)->default(0);
            $table->decimal('valor_obra', 15, 2)->default(0);
            $table->boolean('active')->default(true);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            // Foreign keys se agregarán después
            // $table->foreign('categoria_id')->references('id')->on('categorias');
            // $table->foreign('cargo_id')->references('id')->on('cargos');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parametrizacion');
    }
};
