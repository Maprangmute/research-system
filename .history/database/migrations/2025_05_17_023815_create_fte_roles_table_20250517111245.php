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
        Schema::create('fte_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade'); // เชื่อมกับ projects
            $table->string('role_group'); // เช่น Scientific, Management
            $table->string('name'); // ชื่อผู้ร่วม
            $table->decimal('fte_value', 5, 2); // FTE เช่น 0.25
            $table->string('month')->nullable(); // เดือนที่ทำ
            $table->string('year')->nullable();  // ปีที่ทำ
            $table->timestamps(); // created_at, updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fte_roles');
    }
};
