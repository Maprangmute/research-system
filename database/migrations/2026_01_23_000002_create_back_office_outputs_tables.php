<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('back_office_task_outputs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('back_office_tasks')->onDelete('cascade');
            $table->string('title');
            $table->string('delivered_at')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::create('back_office_entry_outputs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entry_id')->constrained('back_office_entries')->onDelete('cascade');
            $table->string('title');
            $table->string('delivered_at')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('back_office_entry_outputs');
        Schema::dropIfExists('back_office_task_outputs');
    }
};
