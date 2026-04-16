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
        Schema::table('ord_cotizacion', function (Blueprint $table) {
            $table->string('token_aprobacion', 64)->nullable()->unique()->after('fecha_respuesta');
            $table->timestamp('token_expira_en')->nullable()->after('token_aprobacion');
            $table->text('motivo_rechazo')->nullable()->after('token_expira_en');
            $table->string('respondido_por', 200)->nullable()->after('motivo_rechazo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ord_cotizacion', function (Blueprint $table) {
            $table->dropColumn(['token_aprobacion', 'token_expira_en', 'motivo_rechazo', 'respondido_por']);
        });
    }
};
