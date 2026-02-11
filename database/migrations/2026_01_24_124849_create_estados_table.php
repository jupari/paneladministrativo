<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('estados', function (Blueprint $table) {
            $table->id();
            $table->string('estado', 50)->unique();
            $table->string('descripcion', 255)->nullable();
            $table->string('color', 20)->default('#6c757d');
            $table->boolean('active')->default(true);
        });

        // Insertar datos básicos
        DB::table('estados')->insert([
            ['estado' => 'Activo', 'descripcion' => 'Estado activo', 'color' => '#28a745'],
            ['estado' => 'Inactivo', 'descripcion' => 'Estado inactivo', 'color' => '#dc3545'],
            ['estado' => 'Pendiente', 'descripcion' => 'Estado pendiente', 'color' => '#ffc107'],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('estados');
    }
};
