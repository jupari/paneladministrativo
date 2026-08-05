<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('terceros', function (Blueprint $table) {
            $table->string('telefono_indicativo', 5)->nullable()->default('+57')->after('telefono');
            $table->string('celular_indicativo', 5)->nullable()->default('+57')->after('celular');
        });

        Schema::table('terceros_sucursales', function (Blueprint $table) {
            $table->string('telefono_indicativo', 5)->nullable()->default('+57')->after('telefono');
            $table->string('celular_indicativo', 5)->nullable()->default('+57')->after('celular');
        });

        Schema::table('terceros_contactos', function (Blueprint $table) {
            $table->string('telefono_indicativo', 5)->nullable()->default('+57')->after('telefono');
            $table->string('celular_indicativo', 5)->nullable()->default('+57')->after('celular');
        });
    }

    public function down(): void
    {
        Schema::table('terceros', function (Blueprint $table) {
            $table->dropColumn(['telefono_indicativo', 'celular_indicativo']);
        });

        Schema::table('terceros_sucursales', function (Blueprint $table) {
            $table->dropColumn(['telefono_indicativo', 'celular_indicativo']);
        });

        Schema::table('terceros_contactos', function (Blueprint $table) {
            $table->dropColumn(['telefono_indicativo', 'celular_indicativo']);
        });
    }
};
