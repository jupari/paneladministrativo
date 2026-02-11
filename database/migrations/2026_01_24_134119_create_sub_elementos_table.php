<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sub_elementos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('elemento_id');
            $table->string('codigo', 20);
            $table->string('nombre', 100);
            $table->decimal('valor', 15, 2)->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();

            // Foreign key se agregará después
            // $table->foreign('elemento_id')->references('id')->on('elementos');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sub_elementos');
    }
};
