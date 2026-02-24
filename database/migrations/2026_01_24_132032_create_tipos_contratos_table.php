<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tipos_contratos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 10)->nullable();
            $table->string('nombre', 100);
            $table->boolean('active')->default(true);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });

        // Insertar tipos de contrato básicos
        DB::table('tipos_contratos')->insert([
            ['codigo' => '001', 'nombre' => 'Contrato a término fijo', 'active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['codigo' => '002', 'nombre' => 'Contrato a término indefinido', 'active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['codigo' => '003', 'nombre' => 'Contrato de obra', 'active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['codigo' => '004', 'nombre' => 'Prestación de servicios', 'active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('tipos_contratos');
    }
};
