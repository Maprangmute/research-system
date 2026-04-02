<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('external_outputs', function (Blueprint $table) {
            $table->id();
            $table->string('type', 16);
            $table->string('title');
            $table->text('note')->nullable();
            $table->date('publish_date')->nullable();
            $table->string('journal')->nullable();
            $table->string('quartile', 8)->nullable();
            $table->decimal('impact_factor', 8, 3)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('external_output_authors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('external_output_id')->constrained('external_outputs')->cascadeOnDelete();
            $table->string('role', 32)->nullable();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('external_output_conferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('external_output_id')->constrained('external_outputs')->cascadeOnDelete();
            $table->string('name')->nullable();
            $table->string('presentation_type')->nullable();
            $table->string('location')->nullable();
            $table->string('presenter_name')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('external_output_conferences');
        Schema::dropIfExists('external_output_authors');
        Schema::dropIfExists('external_outputs');
    }
};
