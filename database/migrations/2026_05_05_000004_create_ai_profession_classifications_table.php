<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_profession_classifications', function (Blueprint $table) {
            $table->id();
            $table->string('name_key')->unique();
            $table->string('display_name');
            $table->string('profession_group');
            $table->decimal('confidence', 4, 2)->default(0);
            $table->text('evidence')->nullable();
            $table->string('source_context')->default('project_participant');
            $table->json('raw_response')->nullable();
            $table->timestamp('classified_at')->nullable();
            $table->timestamps();

            $table->index('profession_group');
            $table->index('source_context');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_profession_classifications');
    }
};
