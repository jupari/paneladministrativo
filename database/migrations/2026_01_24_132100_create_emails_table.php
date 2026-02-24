<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('emails', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('subject', 500)->nullable();
            $table->longText('body')->nullable();
            $table->text('bodyPreview')->nullable();
            $table->json('from')->nullable();
            $table->json('sender')->nullable();
            $table->json('toRecipients')->nullable();
            $table->json('ccRecipients')->nullable();
            $table->json('bccRecipients')->nullable();
            $table->boolean('isRead')->default(false);
            $table->timestamp('receivedDateTime')->nullable();
            $table->timestamp('sentDateTime')->nullable();
            $table->string('internetMessageId', 500)->nullable();
            $table->string('conversationId', 255)->nullable();
            $table->boolean('hasAttachments')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('emails');
    }
};
