<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('project_name_th');
            $table->string('project_name_en')->nullable();
            $table->string('project_type')->nullable();
            $table->string('theme')->nullable();
            $table->text('shortcut_link')->nullable();
            $table->string('size')->nullable();
    
            $table->string('funding_type')->nullable();
            $table->string('funding_source')->nullable();
            $table->string('funding_org_type')->nullable();
            $table->string('funding_name')->nullable();
            $table->decimal('budget', 12, 2)->nullable();
    
            $table->json('irb_codes')->nullable();
            $table->json('r_codes')->nullable();
            $table->string('io_code')->nullable();
            $table->string('course_code')->nullable();
    
            $table->string('planning_status')->nullable();
            $table->string('proposal_date')->nullable();
            $table->string('execution_status')->nullable();
            $table->string('contract_date')->nullable();
            $table->string('start_date')->nullable();

            // $table->string('start_month')->nullable();
            $table->string('start_year')->nullable();
            // $table->string('end_month')->nullable();
            // $table->string('end_year')->nullable();

            $table->string('review_status')->nullable();
            $table->string('closure_status')->nullable();

            $table->string('team_start_date')->nullable();
            $table->string('team_end_date')->nullable();
    
            // Strategy flags...
            $table->boolean('strategy_1')->default(false);
            $table->boolean('strategy_1_1')->default(false);
            $table->boolean('strategy_1_2')->default(false);
            $table->boolean('strategy_1_3')->default(false);
            $table->boolean('strategy_1_4')->default(false);
            $table->boolean('strategy_1_5')->default(false);
    
            $table->boolean('strategy_2')->default(false);
            $table->boolean('strategy_2_1')->default(false);
            $table->boolean('strategy_2_2')->default(false);
            $table->boolean('strategy_2_3')->default(false);
    
            $table->boolean('strategy_3')->default(false);
            $table->boolean('strategy_3_1')->default(false);
            $table->boolean('strategy_3_2')->default(false);
            $table->boolean('strategy_3_3')->default(false);
            $table->boolean('strategy_3_4')->default(false);
            $table->boolean('strategy_3_5')->default(false);
    
            $table->boolean('strategy_4')->default(false);
            $table->boolean('strategy_4_1')->default(false);
            $table->boolean('strategy_4_2')->default(false);
    
            $table->boolean('strategy_5')->default(false);
            $table->boolean('strategy_5_1')->default(false);
            $table->boolean('strategy_5_2')->default(false);
    
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();
    
            // 🎯 Add index for filters
            $table->index(['user_id', 'start_year', 'project_type']);
        });
    }
    

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
