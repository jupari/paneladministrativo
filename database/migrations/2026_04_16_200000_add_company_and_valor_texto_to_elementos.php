<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Adds valor_texto (string) and composite unique (codigo, company_id) to elementos.
        // Columns may already exist if partially applied — all operations are guarded.
        if (!\Illuminate\Support\Facades\Schema::hasColumn('elementos', 'valor_texto')) {
            Schema::table('elementos', function (Blueprint $table) {
                $table->string('valor_texto', 500)->nullable()->after('valor');
            });
        }

        // Fix unique index: drop single-column and add composite if needed
        $hasComposite = collect(\Illuminate\Support\Facades\DB::select(
            "SHOW INDEX FROM elementos WHERE Key_name = 'elementos_codigo_company_unique'"
        ))->isNotEmpty();

        if (!$hasComposite) {
            Schema::table('elementos', function (Blueprint $table) {
                try { $table->dropUnique(['codigo']); } catch (\Throwable) {}
                $table->unique(['codigo', 'company_id'], 'elementos_codigo_company_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::table('elementos', function (Blueprint $table) {
            try { $table->dropUnique('elementos_codigo_company_unique'); } catch (\Throwable) {}
            try { $table->unique('codigo'); } catch (\Throwable) {}
        });
        if (\Illuminate\Support\Facades\Schema::hasColumn('elementos', 'valor_texto')) {
            Schema::table('elementos', function (Blueprint $table) {
                $table->dropColumn('valor_texto');
            });
        }
    }
};
