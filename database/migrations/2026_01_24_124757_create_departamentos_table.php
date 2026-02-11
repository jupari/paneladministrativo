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
        Schema::create('departamentos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pais_id');
            $table->string('nombre', 100);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
            $table->boolean('active')->default(true);

            $table->foreign('pais_id')->references('id')->on('paises');
            $table->index(['pais_id', 'nombre']);
        });

        // Insertar datos básicos
        DB::table('departamentos')->insert([
            ['pais_id' => 1, 'nombre' => 'Valle del Cauca', 'created_at' => now()],
            ['pais_id' => 1, 'nombre' => 'Cundinamarca', 'created_at' => now()],
            ['pais_id' => 1, 'nombre' => 'Antioquia', 'created_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('departamentos');
    }
};
