<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('back_office_action_logs', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('task_id')->nullable()->constrained('back_office_tasks')->nullOnDelete();
            $table->foreignId('entry_id')->nullable()->constrained('back_office_entries')->nullOnDelete();
            $table->foreignId('output_id')->nullable()->constrained('back_office_task_outputs')->nullOnDelete();
            $table->string('action', 100);
            $table->string('subject_type', 50)->nullable();
            $table->string('subject_label', 255)->nullable();
            $table->string('task_title', 255)->nullable();
            $table->string('entry_person_name', 255)->nullable();
            $table->string('output_title', 255)->nullable();
            $table->string('route_name', 120)->nullable();
            $table->string('http_method', 10)->nullable();
            $table->longText('url')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->longText('user_agent')->nullable();
            $table->string('device_type', 30)->nullable();
            $table->longText('changes')->nullable();
            $table->timestamps();

            $table->index(['action', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index(['task_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('back_office_action_logs');
    }
};
