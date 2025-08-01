<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('privilege_access_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('privilege_access_request_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->uuid('request_uuid');  // for correlation with request
            $table->foreignId('actor_id')->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('action');       // e.g. submitted, approved, denied
            $table->string('type')->default('ssh')->index();  // ssh, sftp, rdp, etc.

            $table->string('hostname')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->string('reason')->nullable();
            $table->string('status')->nullable();

            $table->string('requestor_username')->nullable();

            // ✅ New Teams metadata column
        
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('privilege_access_logs');
    }
};
