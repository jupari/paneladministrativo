<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inv_productos', function (Blueprint $table) {
            $table->id();
            $table->string('tipo_producto', 50)->nullable();
            $table->string('codigo', 50)->unique();
            $table->string('nombre', 200);
            $table->text('descripcion')->nullable();
            $table->string('unidad_medida', 20)->nullable();
            $table->decimal('stock_minimo', 10, 2)->default(0);
            $table->string('marca', 100)->nullable();
            $table->string('categoria', 100)->nullable();
            $table->string('subcategoria', 100)->nullable();
            $table->decimal('precio', 15, 2)->default(0);
            $table->boolean('active')->default(true);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inv_productos');
    }
};
