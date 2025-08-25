<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('output_authors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('output_id')->constrained('outputs')->onDelete('cascade');
            $table->enum('role', ['first', 'co', 'corresponding']);
            $table->string('name');
            $table->timestamps();

            $table->unique(['output_id', 'role', 'name']); // กันซ้ำ
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('output_authors');
    }
};
