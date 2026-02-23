<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $addColumnsIfMissing = function (string $tableName): void {
            if (!Schema::hasTable($tableName)) {
                return;
            }

            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (!Schema::hasColumn($tableName, 'type')) {
                    $table->string('type')->nullable()->after('title');
                }
                if (!Schema::hasColumn($tableName, 'project_output')) {
                    $table->string('project_output')->nullable()->after('type');
                }
                if (!Schema::hasColumn($tableName, 'year')) {
                    $table->string('year')->nullable()->after('project_output');
                }
                if (!Schema::hasColumn($tableName, 'journal')) {
                    $table->string('journal')->nullable()->after('year');
                }
                if (!Schema::hasColumn($tableName, 'quartile')) {
                    $table->string('quartile')->nullable()->after('journal');
                }
                if (!Schema::hasColumn($tableName, 'impact_factor')) {
                    $table->string('impact_factor')->nullable()->after('quartile');
                }
                if (!Schema::hasColumn($tableName, 'document_link')) {
                    $table->string('document_link')->nullable()->after('description');
                }
                if (!Schema::hasColumn($tableName, 'authors')) {
                    $table->json('authors')->nullable()->after('document_link');
                }
                if (!Schema::hasColumn($tableName, 'conferences')) {
                    $table->json('conferences')->nullable()->after('authors');
                }
            });
        };

        $addColumnsIfMissing('back_office_task_outputs');
        $addColumnsIfMissing('back_office_entry_outputs');
    }

    public function down(): void
    {
        $dropColumnsIfExists = function (string $tableName): void {
            if (!Schema::hasTable($tableName)) {
                return;
            }

            $columns = [
                'type',
                'project_output',
                'year',
                'journal',
                'quartile',
                'impact_factor',
                'document_link',
                'authors',
                'conferences',
            ];

            $existing = array_values(array_filter($columns, fn($column) => Schema::hasColumn($tableName, $column)));
            if (!$existing) {
                return;
            }

            Schema::table($tableName, function (Blueprint $table) use ($existing) {
                $table->dropColumn($existing);
            });
        };

        $dropColumnsIfExists('back_office_task_outputs');
        $dropColumnsIfExists('back_office_entry_outputs');
    }
};
