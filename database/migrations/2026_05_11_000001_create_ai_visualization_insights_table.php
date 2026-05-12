<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_visualization_insights', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->id();
            $table->string('insight_type');
            $table->string('scope_key')->default('global');
            $table->string('input_hash', 64);
            $table->string('provider')->nullable();
            $table->string('model')->nullable();
            $table->text('summary')->nullable();
            $table->json('payload')->nullable();
            $table->json('input_snapshot')->nullable();
            $table->timestamp('generated_at')->nullable();
            $table->timestamps();

            $table->unique(['insight_type', 'scope_key', 'input_hash'], 'ai_viz_insights_type_scope_hash_unique');
            $table->index(['insight_type', 'scope_key', 'generated_at'], 'ai_viz_insights_lookup_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_visualization_insights');
    }
};
