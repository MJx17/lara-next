<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('privilege_access_requests', function (Blueprint $table) {
            $table->id();
            $table->uuid('request_uuid')->unique(); // unique UUID

            // The user who makes the request (requestor)
            $table->string('requestor_username')->nullable()->index(); // or use foreignId if you want

            // The admin or grantor user who approves, nullable initially
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            $table->string('reason')->nullable();
            $table->string('status')->default('pending');
            $table->string('type')->default('ssh')->index(); // ssh, sftp, rdp, etc.
            $table->string('hostname')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('privilege_access_requests');
    }
};
