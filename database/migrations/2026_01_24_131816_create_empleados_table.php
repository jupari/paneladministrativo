<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('empleados', function (Blueprint $table) {
            $table->id();
            $table->string('nombres', 100);
            $table->string('apellidos', 100);
            $table->unsignedBigInteger('tipo_identificacion_id');
            $table->string('identificacion', 20);
            $table->string('expedida_en', 100)->nullable();
            $table->date('fecha_nacimiento')->nullable();
            $table->date('fecha_inicio_labor')->nullable();
            $table->date('fecha_finalizacion_contrato')->nullable();
            $table->string('direccion', 200)->nullable();
            $table->string('ciudad_residencia', 100)->nullable();
            $table->string('telefono', 20)->nullable();
            $table->string('celular', 20)->nullable();
            $table->string('correo', 100)->nullable();
            $table->unsignedBigInteger('cliente_id')->nullable();
            $table->unsignedBigInteger('sucursal_id')->nullable();
            $table->unsignedBigInteger('cargo_id')->nullable();
            $table->unsignedBigInteger('tipo_contrato')->nullable();
            $table->string('ubicacion', 100)->nullable();
            $table->decimal('salario', 15, 2)->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();

            // Foreign keys se agregarán después
            // $table->foreign('tipo_identificacion_id')->references('id')->on('tipo_identificacion');
            // $table->foreign('cargo_id')->references('id')->on('cargos');
            // $table->foreign('tipo_contrato')->references('id')->on('tipos_contratos');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('empleados');
    }
};
