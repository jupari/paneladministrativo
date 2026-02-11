<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cuentas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('estado_id');
            $table->unsignedBigInteger('usuario_dist')->nullable();
            $table->string('nombre_cuenta', 200);
            $table->string('password_cuenta', 200)->nullable();
            $table->timestamp('fecha_asig')->nullable();
            $table->timestamps();

            // Foreign keys se agregarán después
            // $table->foreign('user_id')->references('id')->on('users');
            // $table->foreign('estado_id')->references('id')->on('estados');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cuentas');
    }
};
