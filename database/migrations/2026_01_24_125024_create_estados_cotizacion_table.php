<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('estados_cotizacion', function (Blueprint $table) {
            $table->id();
            $table->string('estado', 50)->unique();
            $table->text('descripcion')->nullable();
            $table->string('color', 20)->default('#6c757d');
            $table->boolean('active')->default(true);
            $table->integer('orden')->default(0);
        });

        // Insertar estados básicos para cotizaciones
        DB::table('estados_cotizacion')->insert([
            ['estado' => 'Borrador', 'descripcion' => 'Cotización en estado borrador', 'color' => '#6c757d', 'active' => true, 'orden' => 1],
            ['estado' => 'Enviado', 'descripcion' => 'Cotización enviada al cliente', 'color' => '#007bff', 'active' => true, 'orden' => 2],
            ['estado' => 'En revisión', 'descripcion' => 'Cotización en revisión', 'color' => '#ffc107', 'active' => true, 'orden' => 3],
            ['estado' => 'Aprobado', 'descripcion' => 'Cotización aprobada', 'color' => '#28a745', 'active' => true, 'orden' => 4],
            ['estado' => 'Rechazado', 'descripcion' => 'Cotización rechazada', 'color' => '#dc3545', 'active' => true, 'orden' => 5],
            ['estado' => 'Terminado', 'descripcion' => 'Cotización finalizada exitosamente', 'color' => '#28a745', 'active' => true, 'orden' => 6],
            ['estado' => 'Anulado', 'descripcion' => 'Cotización anulada', 'color' => '#dc3545', 'active' => true, 'orden' => 7],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('estados_cotizacion');
    }
};
