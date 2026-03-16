<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Actividades requeridas por orden de producción.
        // Define qué actividades debe pasar cada unidad para considerarse terminada.
        Schema::create('production_order_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('activity_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('position')->default(0); // orden de ejecución
            $table->timestamps();

            $table->unique(['production_order_id', 'activity_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_order_activities');
    }
};
