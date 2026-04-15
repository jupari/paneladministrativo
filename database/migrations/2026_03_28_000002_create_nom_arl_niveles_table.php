<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nom_arl_niveles', function (Blueprint $table) {
            $table->tinyInteger('nivel')->unsigned()->primary();
            $table->string('descripcion', 100);
            $table->decimal('porcentaje', 6, 4);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nom_arl_niveles');
    }
};
