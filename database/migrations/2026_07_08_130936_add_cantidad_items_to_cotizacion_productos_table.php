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
        Schema::table('ord_cotizacion_productos', function (Blueprint $table) {
            $table->decimal('cantidad_items', 10, 2)->default(1)->after('cantidad');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ord_cotizacion_productos', function (Blueprint $table) {
            $table->dropColumn('cantidad_items');
        });
    }
};
