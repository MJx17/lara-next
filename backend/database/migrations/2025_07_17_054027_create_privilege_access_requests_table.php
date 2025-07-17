<?php

// database/migrations/xxxx_xx_xx_create_login_requests_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('privilege_access_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('reason')->nullable();
            $table->string('status')->default('pending');
            $table->string('hostname')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->timestamps();
            $table->uuid('request_uuid')->unique()->after('id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('privilege_access_requests');
    }
};
