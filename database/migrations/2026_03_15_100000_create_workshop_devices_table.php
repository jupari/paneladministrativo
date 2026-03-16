<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workshop_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workshop_id')->constrained()->cascadeOnDelete();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();

            $table->string('device_uuid', 120);
            $table->string('device_name', 120)->nullable();
            $table->enum('platform', ['android', 'ios']);
            $table->string('app_version', 30)->nullable();
            $table->string('os_version', 30)->nullable();
            $table->enum('status', ['active', 'blocked', 'revoked'])->default('active');

            $table->timestamp('last_login_at')->nullable();
            $table->timestamp('last_sync_at')->nullable();

            $table->foreignId('registered_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('revoked_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('revoked_at')->nullable();

            $table->timestamps();

            $table->unique(['workshop_id', 'device_uuid'], 'wd_workshop_device_unique');
            $table->index(['company_id', 'status']);
            $table->index(['workshop_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workshop_devices');
    }
};
