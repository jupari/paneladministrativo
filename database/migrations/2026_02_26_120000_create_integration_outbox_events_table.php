<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('integration_outbox_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->string('event_type', 120)->index();
            $table->string('event_key', 180)->unique();
            $table->json('payload');
            $table->string('status', 20)->default('PENDING')->index(); // PENDING|PROCESSING|SENT|FAILED
            $table->unsignedInteger('retries')->default(0);
            $table->timestamp('next_retry_at')->nullable()->index();
            $table->timestamp('sent_at')->nullable();
            $table->text('last_error')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('integration_outbox_events');
    }
};

