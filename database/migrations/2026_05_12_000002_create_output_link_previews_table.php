<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('output_link_previews', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->id();
            $table->nullableMorphs('subject');
            $table->string('url_hash', 64);
            $table->text('url');
            $table->string('provider')->default('facebook');
            $table->string('oembed_type')->nullable();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->text('embed_html')->nullable();
            $table->text('thumbnail_url')->nullable();
            $table->unsignedInteger('thumbnail_width')->nullable();
            $table->unsignedInteger('thumbnail_height')->nullable();
            $table->string('author_name')->nullable();
            $table->text('author_url')->nullable();
            $table->string('provider_name')->nullable();
            $table->text('provider_url')->nullable();
            $table->string('fetch_status')->default('pending');
            $table->text('error_message')->nullable();
            $table->json('raw_payload')->nullable();
            $table->timestamp('fetched_at')->nullable();
            $table->timestamps();

            $table->unique('url_hash', 'output_link_previews_url_hash_unique');
            $table->index(['provider', 'fetch_status', 'fetched_at'], 'output_link_previews_fetch_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('output_link_previews');
    }
};
