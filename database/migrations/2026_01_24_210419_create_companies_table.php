<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nombre de la empresa
            $table->string('legal_name')->nullable(); // Razón social
            $table->string('nit')->nullable(); // NIT o identificación fiscal
            $table->string('email')->nullable(); // Email empresarial
            $table->string('phone')->nullable(); // Teléfono
            $table->text('address')->nullable(); // Dirección

            // Configuración visual
            $table->string('logo_path')->nullable(); // Ruta del logo
            $table->string('primary_color')->default('#007bff'); // Color primario
            $table->string('secondary_color')->default('#6c757d'); // Color secundario
            $table->json('theme_settings')->nullable(); // Configuraciones adicionales de tema

            // Sistema de licencias
            $table->boolean('is_active')->default(true); // Estado activo/inactivo
            $table->date('license_expires_at')->nullable(); // Fecha vencimiento licencia
            $table->string('license_type')->default('standard'); // Tipo de licencia (trial, standard, premium)
            $table->integer('max_users')->default(10); // Máximo usuarios permitidos
            $table->json('features')->nullable(); // Características habilitadas

            // Configuraciones específicas
            $table->json('settings')->nullable(); // Configuraciones generales
            $table->text('notes')->nullable(); // Notas adicionales

            $table->timestamps();

            // Índices
            $table->index(['is_active', 'license_expires_at']);
            $table->unique('nit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
