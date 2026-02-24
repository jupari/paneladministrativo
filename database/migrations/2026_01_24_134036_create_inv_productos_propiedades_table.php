<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inv_productos_propiedades', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('producto_id');
            $table->string('codigo', 50);
            $table->string('propiedad1', 100)->nullable();
            $table->string('propiedad2', 100)->nullable();
            $table->timestamps();

            // Foreign key se agregará después
            // $table->foreign('producto_id')->references('id')->on('inv_productos');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inv_productos_propiedades');
    }
};
