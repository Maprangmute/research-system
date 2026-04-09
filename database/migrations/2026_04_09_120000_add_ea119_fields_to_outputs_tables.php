<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $addColumns = function (string $tableName): void {
            if (!Schema::hasTable($tableName)) {
                return;
            }

            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (!Schema::hasColumn($tableName, 'book_title')) {
                    $table->string('book_title')->nullable()->after('impact_factor');
                }
                if (!Schema::hasColumn($tableName, 'publisher_name')) {
                    $table->string('publisher_name')->nullable()->after('book_title');
                }
                if (!Schema::hasColumn($tableName, 'chapter_pages')) {
                    $table->string('chapter_pages')->nullable()->after('publisher_name');
                }
                if (!Schema::hasColumn($tableName, 'book_authors')) {
                    $table->json('book_authors')->nullable()->after('chapter_pages');
                }
            });
        };

        $addColumns('outputs');
        $addColumns('back_office_task_outputs');
        $addColumns('back_office_entry_outputs');
    }

    public function down(): void
    {
        $dropColumns = function (string $tableName): void {
            if (!Schema::hasTable($tableName)) {
                return;
            }

            $columns = array_values(array_filter([
                Schema::hasColumn($tableName, 'book_title') ? 'book_title' : null,
                Schema::hasColumn($tableName, 'publisher_name') ? 'publisher_name' : null,
                Schema::hasColumn($tableName, 'chapter_pages') ? 'chapter_pages' : null,
                Schema::hasColumn($tableName, 'book_authors') ? 'book_authors' : null,
            ]));

            if (!$columns) {
                return;
            }

            Schema::table($tableName, function (Blueprint $table) use ($columns) {
                $table->dropColumn($columns);
            });
        };

        $dropColumns('outputs');
        $dropColumns('back_office_task_outputs');
        $dropColumns('back_office_entry_outputs');
    }
};

