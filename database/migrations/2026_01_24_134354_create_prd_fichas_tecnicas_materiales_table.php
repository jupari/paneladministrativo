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
        Schema::create('prd_fichas_tecnicas_materiales', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('fichatecnica_id');
            $table->string('referencia_codigo')->nullable();
            $table->decimal('cantidad', 15, 4)->nullable();
            $table->string('unidad_medida')->nullable();
            $table->string('prop_1')->nullable();
            $table->string('prop_2')->nullable();
            $table->string('codigo')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            // Foreign key constraints
            $table->foreign('fichatecnica_id')->references('id')->on('prd_fichas_tecnicas')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prd_fichas_tecnicas_materiales');
    }
};
