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
        // Agregar company_id a cotizaciones
        if (Schema::hasTable('cotizaciones')) {
            Schema::table('cotizaciones', function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')->nullable()->after('id');
                $table->foreign('company_id')->references('id')->on('companies')->onDelete('set null');
                $table->index('company_id');
            });
        }

        // Agregar company_id a productos
        if (Schema::hasTable('productos')) {
            Schema::table('productos', function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')->nullable()->after('id');
                $table->foreign('company_id')->references('id')->on('companies')->onDelete('set null');
                $table->index('company_id');
            });
        }

        // Agregar company_id a terceros
        if (Schema::hasTable('terceros')) {
            Schema::table('terceros', function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')->nullable()->after('id');
                $table->foreign('company_id')->references('id')->on('companies')->onDelete('set null');
                $table->index('company_id');
            });
        }

        // Agregar company_id a fichas_tecnicas
        if (Schema::hasTable('fichas_tecnicas')) {
            Schema::table('fichas_tecnicas', function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')->nullable()->after('id');
                $table->foreign('company_id')->references('id')->on('companies')->onDelete('set null');
                $table->index('company_id');
            });
        }

        // Agregar company_id a movimientos
        if (Schema::hasTable('movimientos')) {
            Schema::table('movimientos', function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')->nullable()->after('id');
                $table->foreign('company_id')->references('id')->on('companies')->onDelete('set null');
                $table->index('company_id');
            });
        }

        // Agregar company_id a bodegas
        if (Schema::hasTable('bodegas')) {
            Schema::table('bodegas', function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')->nullable()->after('id');
                $table->foreign('company_id')->references('id')->on('companies')->onDelete('set null');
                $table->index('company_id');
            });
        }

        // Agregar company_id a empleados
        if (Schema::hasTable('empleados')) {
            Schema::table('empleados', function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')->nullable()->after('id');
                $table->foreign('company_id')->references('id')->on('companies')->onDelete('set null');
                $table->index('company_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = ['cotizaciones', 'productos', 'terceros', 'fichas_tecnicas', 'movimientos', 'bodegas', 'empleados'];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $table) {
                    $table->dropForeign(['company_id']);
                    $table->dropIndex(['company_id']);
                    $table->dropColumn('company_id');
                });
            }
        }
    }
};
