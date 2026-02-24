<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('estados_cotizacion', function (Blueprint $table) {
            $table->id();
            $table->string('estado', 50)->unique(); // Borrador, En Proceso, Enviado, Aprobado, Rechazado, Vencido
            $table->string('descripcion', 255)->nullable();
            $table->string('color', 20)->default('#6c757d'); // Color para UI
            $table->boolean('active')->default(true);
            $table->integer('orden')->default(1); // Para ordenar estados en workflow
            $table->timestamps();
        });

        // Insertar estados por defecto
        DB::table('estados_cotizacion')->insert([
            ['estado' => 'Borrador', 'descripcion' => 'Cotización en proceso de elaboración', 'color' => '#6c757d', 'orden' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['estado' => 'En Proceso', 'descripcion' => 'Cotización siendo revisada internamente', 'color' => '#ffc107', 'orden' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['estado' => 'Enviado', 'descripcion' => 'Cotización enviada al cliente', 'color' => '#17a2b8', 'orden' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['estado' => 'Aprobado', 'descripcion' => 'Cotización aprobada por el cliente', 'color' => '#28a745', 'orden' => 4, 'created_at' => now(), 'updated_at' => now()],
            ['estado' => 'Rechazado', 'descripcion' => 'Cotización rechazada por el cliente', 'color' => '#dc3545', 'orden' => 5, 'created_at' => now(), 'updated_at' => now()],
            ['estado' => 'Vencido', 'descripcion' => 'Cotización vencida por tiempo', 'color' => '#6f42c1', 'orden' => 6, 'created_at' => now(), 'updated_at' => now()],
            ['estado' => 'Terminado', 'descripcion' => 'Cotización finalizada y convertida', 'color' => '#20c997', 'orden' => 7, 'created_at' => now(), 'updated_at' => now()],
            ['estado' => 'Anulado', 'descripcion' => 'Cotización anulada por el usuario', 'color' => '#dc3545', 'orden' => 8, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estados_cotizacion');
    }
};
