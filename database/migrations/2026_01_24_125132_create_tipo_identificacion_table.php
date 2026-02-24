<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tipo_identificacion', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100)->unique();
            $table->boolean('active')->default(true);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });

        // Insertar tipos de identificación básicos
        DB::table('tipo_identificacion')->insert([
            ['nombre' => 'Cédula de Ciudadanía', 'active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'NIT', 'active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Cédula de Extranjería', 'active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Pasaporte', 'active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('tipo_identificacion');
    }
};
