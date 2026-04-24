<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_activity_logs', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('activity_type', 50);
            $table->string('subject_type', 50)->nullable();
            $table->string('subject_label', 255)->nullable();
            $table->string('file_name', 255)->nullable();
            $table->string('route_name', 120)->nullable();
            $table->string('http_method', 10)->nullable();
            $table->longText('url')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->longText('user_agent')->nullable();
            $table->string('device_type', 30)->nullable();
            $table->string('session_id', 255)->nullable();
            $table->longText('meta')->nullable();
            $table->timestamps();

            $table->index(['activity_type', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index(['file_name', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_activity_logs');
    }
};
