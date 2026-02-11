<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('autorizaciones', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100)->unique();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        // Insertar datos básicos
        DB::table('autorizaciones')->insert([
            ['nombre' => 'Pendiente por autorización', 'active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Autorizado', 'active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('autorizaciones');
    }
};
