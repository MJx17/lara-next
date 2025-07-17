<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // In create_privilege_access_logs_table.php
        Schema::create('privilege_access_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('privilege_access_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action');
            $table->string('hostname')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->string('reason')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('privilege_access_logs');
    }
};
