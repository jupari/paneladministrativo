<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('terceros_contactos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tercero_id');
            $table->string('nombres', 100);
            $table->string('apellidos', 100);
            $table->string('correo', 100)->nullable();
            $table->string('celular', 20)->nullable();
            $table->string('telefono', 20)->nullable();
            $table->string('ext', 10)->nullable();
            $table->string('cargo', 100)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();

            // Foreign key se agregará después
            // $table->foreign('tercero_id')->references('id')->on('terceros');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('terceros_contactos');
    }
};
