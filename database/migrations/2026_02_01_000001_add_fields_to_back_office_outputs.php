<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('back_office_task_outputs', function (Blueprint $table) {
            $table->string('type')->nullable()->after('title');
            $table->string('project_output')->nullable()->after('type');
            $table->string('year')->nullable()->after('project_output');
            $table->string('journal')->nullable()->after('year');
            $table->string('quartile')->nullable()->after('journal');
            $table->string('impact_factor')->nullable()->after('quartile');
            $table->string('document_link')->nullable()->after('description');
            $table->json('authors')->nullable()->after('document_link');
            $table->json('conferences')->nullable()->after('authors');
        });

        Schema::table('back_office_entry_outputs', function (Blueprint $table) {
            $table->string('type')->nullable()->after('title');
            $table->string('project_output')->nullable()->after('type');
            $table->string('year')->nullable()->after('project_output');
            $table->string('journal')->nullable()->after('year');
            $table->string('quartile')->nullable()->after('journal');
            $table->string('impact_factor')->nullable()->after('quartile');
            $table->string('document_link')->nullable()->after('description');
            $table->json('authors')->nullable()->after('document_link');
            $table->json('conferences')->nullable()->after('authors');
        });
    }

    public function down(): void
    {
        Schema::table('back_office_task_outputs', function (Blueprint $table) {
            $table->dropColumn([
                'type',
                'project_output',
                'year',
                'journal',
                'quartile',
                'impact_factor',
                'document_link',
                'authors',
                'conferences',
            ]);
        });

        Schema::table('back_office_entry_outputs', function (Blueprint $table) {
            $table->dropColumn([
                'type',
                'project_output',
                'year',
                'journal',
                'quartile',
                'impact_factor',
                'document_link',
                'authors',
                'conferences',
            ]);
        });
    }
};
