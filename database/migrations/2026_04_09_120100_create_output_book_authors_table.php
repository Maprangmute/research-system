<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('output_book_authors')) {
            return;
        }

        Schema::create('output_book_authors', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->id();
            $table->foreignId('output_id')->constrained('outputs')->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();

            $table->index('name');
            $table->unique(['output_id', 'name'], 'output_book_authors_output_name_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('output_book_authors');
    }
};

