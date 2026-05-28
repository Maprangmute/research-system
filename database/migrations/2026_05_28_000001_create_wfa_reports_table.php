<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wfa_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('person_name');
            $table->string('title')->default('WFA Report');
            $table->date('work_date')->nullable();
            $table->string('filter_mode')->default('all');
            $table->unsignedSmallInteger('filter_year')->nullable();
            $table->date('filter_start')->nullable();
            $table->date('filter_end')->nullable();
            $table->longText('output_html');
            $table->longText('output_text')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index('person_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wfa_reports');
    }
};
