<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nom_parametros_globales', function (Blueprint $table) {
            $table->id();
            $table->year('vigencia')->unique();
            $table->decimal('smlv', 12, 2);
            $table->decimal('aux_transporte', 12, 2);
            $table->decimal('uvt', 12, 2);
            $table->tinyInteger('tope_exoneracion_ley1607')->default(10); // múltiplos de SMLV
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nom_parametros_globales');
    }
};
