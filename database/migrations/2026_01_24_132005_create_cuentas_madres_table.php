<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cuentas_madres', function (Blueprint $table) {
            $table->id();
            $table->string('email', 255);
            $table->string('password', 255);
            $table->string('nombre', 200);
            $table->unsignedBigInteger('usuario_dist')->nullable();
            $table->string('cm_asociada', 100)->nullable();
            $table->string('cta_ppal', 100)->nullable();
            $table->timestamps();

            // Foreign key se agregará después
            // $table->foreign('usuario_dist')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cuentas_madres');
    }
};
