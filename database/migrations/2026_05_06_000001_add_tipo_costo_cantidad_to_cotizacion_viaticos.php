<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ord_cotizacion_viaticos', function (Blueprint $table) {
            $table->string('tipo_costo', 10)->nullable()->after('concepto');
            $table->decimal('cantidad', 10, 3)->nullable()->after('tipo_costo');
        });
    }

    public function down(): void
    {
        Schema::table('ord_cotizacion_viaticos', function (Blueprint $table) {
            $table->dropColumn(['tipo_costo', 'cantidad']);
        });
    }
};
