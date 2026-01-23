<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('back_office_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('back_office_tasks')->onDelete('cascade');
            $table->string('person_name');
            $table->enum('category', ['event', 'work', 'consult', 'other']);
            $table->string('job_detail');
            $table->decimal('hours', 8, 2)->default(0);
            $table->decimal('fte_value', 12, 8)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('back_office_entries');
    }
};
