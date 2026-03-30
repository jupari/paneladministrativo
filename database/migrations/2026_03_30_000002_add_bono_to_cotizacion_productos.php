<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ord_cotizacion_productos', function (Blueprint $table) {
            $table->decimal('bono', 15, 2)->nullable()->default(0)->after('tipo_costo');
        });
    }

    public function down(): void
    {
        Schema::table('ord_cotizacion_productos', function (Blueprint $table) {
            $table->dropColumn('bono');
        });
    }
};
