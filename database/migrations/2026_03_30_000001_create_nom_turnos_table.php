<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nom_turnos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->string('descripcion', 255)->nullable();
            $table->enum('tipo_ordinaria', ['diurna', 'nocturna'])->default('diurna');
            $table->boolean('es_dominical_festivo')->default(false);
            $table->tinyInteger('max_horas_ordinarias')->default(7);   // 1–7
            $table->boolean('tiene_extras_diurnas')->default(true);
            $table->boolean('tiene_extras_nocturnas')->default(false);
            $table->tinyInteger('max_horas_extras')->default(2);       // 0–2
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nom_turnos');
    }
};
