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
        Schema::create('logs_files', function (Blueprint $table) {
            $table->id();
            $table->text('dato1')->nullable();
            $table->text('dato2')->nullable();
            $table->text('dato3')->nullable();
            $table->text('dato4')->nullable();
            $table->text('dato5')->nullable();
            $table->text('dato6')->nullable();
            $table->text('dato7')->nullable();
            $table->text('dato8')->nullable();
            $table->text('dato9')->nullable();
            $table->text('dato10')->nullable();
            $table->string('lote')->nullable();
            $table->string('accused')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logs_files');
    }
};
