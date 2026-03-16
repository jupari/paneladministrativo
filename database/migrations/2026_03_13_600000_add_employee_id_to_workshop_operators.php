<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fase 6 – Puente de operarios.
 *
 * Añade employee_id (nullable FK) a workshop_operators para vincular
 * operarios de la app móvil con empleados del sistema de nómina.
 *
 * Esto permite resolver liquidaciones y reportes para operaciones
 * registradas desde la app que solo tienen workshop_operator_id.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('workshop_operators', function (Blueprint $table) {
            $table->unsignedBigInteger('employee_id')->nullable()->after('is_active');

            $table->foreign('employee_id')
                  ->references('id')
                  ->on('empleados')
                  ->nullOnDelete();

            // Un empleado puede ser operario en varios talleres,
            // pero no puede estar duplicado dentro del mismo taller.
            $table->unique(['workshop_id', 'employee_id'], 'wo_workshop_employee_unique');
        });
    }

    public function down(): void
    {
        Schema::table('workshop_operators', function (Blueprint $table) {
            $table->dropUnique('wo_workshop_employee_unique');
            $table->dropForeign(['employee_id']);
            $table->dropColumn('employee_id');
        });
    }
};
