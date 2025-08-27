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
        Schema::create('output_conferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('output_id')
                ->constrained('outputs')
                ->cascadeOnDelete();

            $table->string('name')->nullable();              // ชื่อการประชุม/งานวิชาการ
            $table->string('presentation_type')->nullable(); // oral / poster
            $table->string('location')->nullable();          // สถานที่
            $table->text('note')->nullable();                // หมายเหตุเพิ่มเติม

            $table->timestamps();

            // ✅ ตั้งค่า charset และ collation ที่นี่
            $table->charset   = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('output_conferences');
    }
};
