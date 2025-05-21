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
            $table->string('project_name_th'); // AA301
            $table->string('project_name_en')->nullable(); // AA302
            $table->string('project_type')->nullable(); // AA201
            $table->string('theme')->nullable(); // AA101
            $table->text('shortcut_link')->nullable(); // Google Drive

            // Funding
            $table->string('funding_type')->nullable(); // DA102
            $table->string('funding_source')->nullable(); // DA103
            $table->string('funding_org_type')->nullable(); // DA104
            $table->string('funding_name')->nullable(); // DA105
            $table->decimal('budget', 12, 2)->nullable(); // DA106
            $table->string('size')->nullable(); // size S/M/L

            // IRB and Codes (multiple values stored as comma-separated or JSON)
            $table->json('irb_codes')->nullable(); // AA601, AA601.2
            $table->json('r_codes')->nullable(); // AA602.1 (array of R:xxx)
            $table->string('io_code')->nullable(); // AA603.1
            $table->string('course_code')->nullable(); // AA605.1

            // Timeline and statuses
            $table->string('planning_status')->nullable(); // BA000
            $table->string('proposal_date')->nullable(); // BA001
            $table->string('execution_status')->nullable(); // BC000
            $table->string('contract_date')->nullable(); // BC101
            $table->string('start_date')->nullable(); // BC102
            $table->string('start_month')->nullable();
            $table->string('start_year')->nullable();
            $table->string('end_month')->nullable();
            $table->string('end_year')->nullable();
            $table->string('review_status')->nullable(); // BD000
            $table->string('closure_status')->nullable(); // BE000

            // Strategic alignment (boolean flags)
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
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
