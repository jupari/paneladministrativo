<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Talleres / líneas de producción (multi-compañía → ver pivote company_workshops)
        Schema::create('workshops', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 20)->unique();
            $table->string('address')->nullable();
            $table->string('coordinator_name')->nullable();
            $table->string('coordinator_phone', 30)->nullable();
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
            $table->timestamp('last_sync_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Operarios asignados a un taller
        Schema::create('workshop_operators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workshop_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code', 20)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Pivote usuario ↔ taller (qué talleres puede ver cada usuario móvil)
        Schema::create('user_workshops', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('workshop_id')->constrained()->cascadeOnDelete();
            $table->primary(['user_id', 'workshop_id']);
        });

        // Pivote compañía ↔ taller (un taller puede pertenecer a varias empresas)
        Schema::create('company_workshops', function (Blueprint $table) {
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('workshop_id')->constrained()->cascadeOnDelete();
            $table->primary(['company_id', 'workshop_id']);
        });

        // Órdenes de producción del módulo móvil
        Schema::create('production_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workshop_id')->constrained()->cascadeOnDelete();
            $table->string('order_code', 50)->unique();
            $table->string('garment_type');
            $table->string('garment_reference', 100)->nullable();
            $table->string('color', 80)->nullable();
            $table->unsignedInteger('total_units');
            $table->unsignedInteger('completed_units')->default(0);
            $table->decimal('cost_per_unit', 12, 2)->default(0);
            $table->enum('status', ['pending', 'in_progress', 'paused', 'completed', 'cancelled'])
                  ->default('pending');
            $table->date('start_date');
            $table->date('deadline')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['workshop_id', 'status']);
        });

        // Catálogo de actividades (operaciones que ejecutan los operarios)
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('code', 20)->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Tipos de daño para el reporte de prendas dañadas
        Schema::create('damage_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('code', 20)->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Registro de operaciones enviadas desde el móvil
        Schema::create('production_operations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workshop_id')->constrained();
            $table->foreignId('production_order_id')->constrained();
            $table->foreignId('activity_id')->constrained();
            $table->foreignId('workshop_operator_id')->constrained();
            $table->foreignId('user_id')->constrained(); // quién registró desde el móvil
            $table->unsignedInteger('quantity');
            $table->timestamp('registered_at'); // timestamp del dispositivo móvil
            $table->string('idempotency_key', 100)->nullable()->unique(); // evita duplicados
            $table->timestamps();

            $table->index(['production_order_id', 'registered_at']);
        });

        // Registro de prendas dañadas enviadas desde el móvil
        Schema::create('damaged_garments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workshop_id')->constrained();
            $table->foreignId('production_order_id')->constrained();
            $table->foreignId('damage_type_id')->constrained();
            $table->foreignId('user_id')->constrained();
            $table->unsignedInteger('quantity');
            $table->text('notes')->nullable();
            $table->timestamp('registered_at');
            $table->string('idempotency_key', 100)->nullable()->unique();
            $table->timestamps();

            $table->index(['production_order_id', 'registered_at']);
        });

        // Evidencias fotográficas de prendas dañadas
        Schema::create('damage_evidences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('damaged_garment_id')->constrained()->cascadeOnDelete();
            $table->string('path'); // ruta en storage
            $table->string('disk', 20)->default('local');
            $table->unsignedBigInteger('size_bytes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('damage_evidences');
        Schema::dropIfExists('damaged_garments');
        Schema::dropIfExists('production_operations');
        Schema::dropIfExists('damage_types');
        Schema::dropIfExists('activities');
        Schema::dropIfExists('production_orders');
        Schema::dropIfExists('company_workshops');
        Schema::dropIfExists('user_workshops');
        Schema::dropIfExists('workshop_operators');
        Schema::dropIfExists('workshops');
    }
};
