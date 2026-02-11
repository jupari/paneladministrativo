<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('unidades_medida', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->string('sigla', 10);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        // Insertar unidades de medida básicas
        DB::table('unidades_medida')->insert([
            ['nombre' => 'Metro', 'sigla' => 'm', 'active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Kilómetro', 'sigla' => 'km', 'active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Centímetro', 'sigla' => 'cm', 'active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Kilogramo', 'sigla' => 'kg', 'active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Gramo', 'sigla' => 'g', 'active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Litro', 'sigla' => 'L', 'active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Unidad', 'sigla' => 'und', 'active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Paquete', 'sigla' => 'pqt', 'active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Caja', 'sigla' => 'cja', 'active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Metro cuadrado', 'sigla' => 'm²', 'active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Metro cúbico', 'sigla' => 'm³', 'active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Hora', 'sigla' => 'hr', 'active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Día', 'sigla' => 'día', 'active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('unidades_medida');
    }
};
