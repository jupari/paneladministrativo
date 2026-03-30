<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Los niveles ARL deben existir antes de agregar la FK a cargos.
        // La migración 000002 crea la tabla; si ya existe, este seed es idempotente.
        DB::table('nom_arl_niveles')->insertOrIgnore([
            ['nivel' => 1, 'descripcion' => 'Nivel I — Riesgo mínimo',    'porcentaje' => 0.5220],
            ['nivel' => 2, 'descripcion' => 'Nivel II — Riesgo bajo',     'porcentaje' => 1.0440],
            ['nivel' => 3, 'descripcion' => 'Nivel III — Riesgo medio',   'porcentaje' => 2.4360],
            ['nivel' => 4, 'descripcion' => 'Nivel IV — Riesgo alto',     'porcentaje' => 4.3500],
            ['nivel' => 5, 'descripcion' => 'Nivel V — Riesgo máximo',    'porcentaje' => 6.9600],
        ]);

        Schema::table('cargos', function (Blueprint $table) {
            $table->decimal('salario_base', 12, 2)->nullable()->after('nombre');
            $table->tinyInteger('arl_nivel')->unsigned()->default(1)->after('salario_base');
            $table->boolean('aplica_exoneracion_ley1607')->default(true)->after('arl_nivel');
            $table->foreign('arl_nivel')->references('nivel')->on('nom_arl_niveles');
        });
    }

    public function down(): void
    {
        Schema::table('cargos', function (Blueprint $table) {
            $table->dropForeign(['arl_nivel']);
            $table->dropColumn(['salario_base', 'arl_nivel', 'aplica_exoneracion_ley1607']);
        });
    }
};
