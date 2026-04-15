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
        Schema::create('ord_cotizacion_viaticos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cotizacion_id');
            $table->string('concepto', 255);
            $table->decimal('valor', 15, 2)->default(0);
            $table->integer('orden')->default(0);
            $table->timestamps();

            $table->foreign('cotizacion_id')
                ->references('id')
                ->on('ord_cotizacion')
                ->onDelete('cascade');
        });

        Schema::table('ord_cotizacion', function (Blueprint $table) {
            $table->decimal('viaticos', 15, 2)->default(0)->after('total_impuesto');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ord_cotizacion_viaticos');

        Schema::table('ord_cotizacion', function (Blueprint $table) {
            $table->dropColumn('viaticos');
        });
    }
};
