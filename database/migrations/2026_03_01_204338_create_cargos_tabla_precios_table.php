<?php

// database/migrations/xxxx_xx_xx_create_cargos_tabla_precios_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cargos_tabla_precios', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cargo_id')->unique();

            $table->decimal('utilidad_pct', 8, 4)->default(0.3150);
            $table->unsignedInteger('horas_diarias')->default(8);

            // Bases (sin utilidad)
            $table->decimal('base_costo_dia', 18, 2)->default(0);
            $table->decimal('base_costo_hora', 18, 2)->default(0);

            // Tabla de precios (con utilidad y factores)
            $table->decimal('hora_ordinaria', 18, 2)->default(0);
            $table->decimal('recargo_nocturno', 18, 2)->default(0);
            $table->decimal('hora_extra_diurna', 18, 2)->default(0);
            $table->decimal('hora_extra_nocturna', 18, 2)->default(0);
            $table->decimal('hora_dominical', 18, 2)->default(0);
            $table->decimal('hora_extra_dominical_diurna', 18, 2)->default(0);
            $table->decimal('hora_extra_dominical_nocturna', 18, 2)->default(0);

            $table->decimal('valor_dia_ordinario', 18, 2)->default(0);

            $table->timestamps();

            $table->foreign('cargo_id')->references('id')->on('cargos');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cargos_tabla_precios');
    }
};
