<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            // Ampliar code de 20 a 50 para acomodar datos legacy de prod_operations
            $table->string('code', 50)->change();

            // ID original en prod_operations (para trazabilidad durante migración)
            $table->unsignedBigInteger('legacy_prod_operation_id')->nullable()->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->string('code', 20)->change();
            $table->dropColumn('legacy_prod_operation_id');
        });
    }
};
