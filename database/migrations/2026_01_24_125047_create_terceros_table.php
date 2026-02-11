<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('terceros', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tercerotipo_id');
            $table->unsignedBigInteger('tipoidentificacion_id');
            $table->string('identificacion', 20)->unique();
            $table->string('dv', 1)->nullable();
            $table->unsignedBigInteger('tipopersona_id');
            $table->string('nombres', 100)->nullable();
            $table->string('apellidos', 100)->nullable();
            $table->string('nombre_establecimiento', 200)->nullable();
            $table->string('telefono', 20)->nullable();
            $table->string('celular', 20)->nullable();
            $table->string('correo', 100)->nullable();
            $table->string('correo_fe', 100)->nullable();
            $table->unsignedBigInteger('ciudad_id');
            $table->text('direccion')->nullable();
            $table->unsignedBigInteger('vendedor_id')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->boolean('active')->default(true);

            $table->foreign('tercerotipo_id')->references('id')->on('terceros_tipos')->onDelete('cascade');
            $table->foreign('tipoidentificacion_id')->references('id')->on('tipo_identificacion')->onDelete('cascade');
            $table->foreign('tipopersona_id')->references('id')->on('tipo_persona')->onDelete('cascade');
            $table->foreign('ciudad_id')->references('id')->on('ciudades')->onDelete('cascade');
            $table->foreign('vendedor_id')->references('id')->on('vendedores')->onDelete('set null');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('terceros');
    }
};
