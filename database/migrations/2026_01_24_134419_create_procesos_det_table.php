<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('prd_procesos_det', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('proceso_id');
            $table->string('actividad');
            $table->text('descripcion')->nullable();
            $table->decimal('tiempo', 10, 2)->nullable();
            $table->decimal('costo', 15, 4)->nullable();
            $table->boolean('active')->default(1);

            // Foreign key constraints
            $table->foreign('proceso_id')->references('id')->on('prd_procesos')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('procesos_det');
    }
};
