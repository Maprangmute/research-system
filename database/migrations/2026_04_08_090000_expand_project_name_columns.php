<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->text('project_name_th')->change();
            $table->text('project_name_en')->nullable()->change();
        });

        Schema::table('project_logs', function (Blueprint $table) {
            $table->text('project_name_th')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('project_logs', function (Blueprint $table) {
            $table->string('project_name_th')->nullable()->change();
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->string('project_name_th')->change();
            $table->string('project_name_en')->nullable()->change();
        });
    }
};
