<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('privilege_access_requests', function (Blueprint $table) {
        $table->id();
        $table->uuid('request_uuid')->unique(); // unique UUID for correlation
        $table->string('requestor_username')->nullable()->index(); // requestor username
        $table->string('requestor_fullname')->nullable(); // ✅ full name
        $table->string('system_name')->nullable(); // ✅ originating system
        $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // grantor (approver)
        $table->string('reason')->nullable();
        $table->string('status')->default('pending');
        $table->string('type')->default('ssh')->index(); // ssh, sftp, rdp, etc.
        $table->string('hostname')->nullable(); // ✅ host requested
        $table->ipAddress('host_ip')->nullable(); // ✅ target host IP
        $table->ipAddress('requestor_ip')->nullable(); // ✅ IP of requesting user
        $table->timestamps();
    });

    }

    public function down(): void
    {
        Schema::dropIfExists('privilege_access_requests');
    }
};
