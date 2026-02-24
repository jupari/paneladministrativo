<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('terceros_tipos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100)->unique();
            $table->boolean('active')->default(true);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });

        // Insertar tipos de terceros básicos
        DB::table('terceros_tipos')->insert([
            ['nombre' => 'Cliente', 'active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Proveedor', 'active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Empleado', 'active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('terceros_tipos');
    }
};
