<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ciudades', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->unsignedBigInteger('departamento_id');
            $table->unsignedBigInteger('pais_id');
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->foreign('departamento_id')->references('id')->on('departamentos')->onDelete('cascade');
            $table->foreign('pais_id')->references('id')->on('paises')->onDelete('cascade');
        });

        // Insertar algunas ciudades principales
        DB::table('ciudades')->insert([
            // Valle del Cauca
            ['nombre' => 'Cali', 'departamento_id' => 1, 'pais_id' => 1, 'active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Palmira', 'departamento_id' => 1, 'pais_id' => 1, 'active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Buenaventura', 'departamento_id' => 1, 'pais_id' => 1, 'active' => true, 'created_at' => now(), 'updated_at' => now()],

            // Cundinamarca
            ['nombre' => 'Bogotá', 'departamento_id' => 2, 'pais_id' => 1, 'active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Soacha', 'departamento_id' => 2, 'pais_id' => 1, 'active' => true, 'created_at' => now(), 'updated_at' => now()],

            // Antioquia
            ['nombre' => 'Medellín', 'departamento_id' => 3, 'pais_id' => 1, 'active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Bello', 'departamento_id' => 3, 'pais_id' => 1, 'active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('ciudades');
    }
};
