<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->string('email', 255)->unique();
            $table->text('oauth_token')->nullable();
            $table->text('refresh_token')->nullable();
            $table->timestamp('token_expires_at')->nullable();
            $table->string('clientId', 255)->nullable();
            $table->string('clientSecret', 255)->nullable();
            $table->string('tenant_id', 255)->nullable();
            $table->string('redirectUri', 500)->nullable();
            $table->string('urlAuthorize', 500)->nullable();
            $table->string('urlAccessToken', 500)->nullable();
            $table->string('urlResourceOwnerDetails', 500)->nullable();
            $table->text('scopes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
