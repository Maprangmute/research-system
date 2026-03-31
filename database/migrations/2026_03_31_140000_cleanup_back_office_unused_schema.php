<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('back_office_entry_outputs')) {
            Schema::drop('back_office_entry_outputs');
        }

        if (Schema::hasTable('back_office_task_outputs')) {
            $columnsToDrop = array_values(array_filter([
                Schema::hasColumn('back_office_task_outputs', 'delivered_at') ? 'delivered_at' : null,
                Schema::hasColumn('back_office_task_outputs', 'description') ? 'description' : null,
                Schema::hasColumn('back_office_task_outputs', 'document_link') ? 'document_link' : null,
            ]));

            if ($columnsToDrop !== []) {
                Schema::table('back_office_task_outputs', function (Blueprint $table) use ($columnsToDrop) {
                    $table->dropColumn($columnsToDrop);
                });
            }
        }

        if (Schema::hasTable('back_office_entries') && Schema::hasColumn('back_office_entries', 'document_link')) {
            Schema::table('back_office_entries', function (Blueprint $table) {
                $table->dropColumn('document_link');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('back_office_task_outputs')) {
            Schema::table('back_office_task_outputs', function (Blueprint $table) {
                if (!Schema::hasColumn('back_office_task_outputs', 'delivered_at')) {
                    $table->string('delivered_at')->nullable()->after('impact_factor');
                }
                if (!Schema::hasColumn('back_office_task_outputs', 'description')) {
                    $table->string('description')->nullable()->after('delivered_at');
                }
                if (!Schema::hasColumn('back_office_task_outputs', 'document_link')) {
                    $table->string('document_link')->nullable()->after('description');
                }
            });
        }

        if (Schema::hasTable('back_office_entries') && !Schema::hasColumn('back_office_entries', 'document_link')) {
            Schema::table('back_office_entries', function (Blueprint $table) {
                $table->string('document_link')->nullable()->after('end_date');
            });
        }

        if (!Schema::hasTable('back_office_entry_outputs')) {
            Schema::create('back_office_entry_outputs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('entry_id')->constrained('back_office_entries')->onDelete('cascade');
                $table->string('title');
                $table->string('type')->nullable();
                $table->string('project_output')->nullable();
                $table->string('year')->nullable();
                $table->string('journal')->nullable();
                $table->string('quartile')->nullable();
                $table->string('impact_factor')->nullable();
                $table->string('delivered_at')->nullable();
                $table->string('description')->nullable();
                $table->string('document_link')->nullable();
                $table->json('authors')->nullable();
                $table->json('conferences')->nullable();
                $table->timestamps();
            });
        }
    }
};
