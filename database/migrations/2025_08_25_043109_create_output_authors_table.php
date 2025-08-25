<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('output_authors', function (Blueprint $table) {
            $table->id();

            // FK → outputs
            $table->foreignId('output_id')
                  ->constrained('outputs')
                  ->cascadeOnDelete(); // ถ้า output ถูกลบ → ลบผู้เขียนด้วย

            // ข้อมูลผู้เขียน
            $table->enum('role', ['first', 'co', 'corresponding'])->index();
            $table->string('name', 255)->index(); // เก็บชื่อ-สกุลเต็ม

            $table->timestamps();

            // กันซ้ำ: output เดียวกัน role + name ห้ามซ้ำ
            $table->unique(['output_id', 'role', 'name'], 'output_role_name_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('output_authors');
    }
};
