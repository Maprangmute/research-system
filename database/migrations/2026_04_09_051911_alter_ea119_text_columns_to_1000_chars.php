<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = ['outputs', 'back_office_task_outputs', 'back_office_entry_outputs'];

        foreach ($tables as $table) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            if (Schema::hasColumn($table, 'book_title')) {
                DB::statement("ALTER TABLE `{$table}` MODIFY `book_title` VARCHAR(1000) NULL");
            }
            if (Schema::hasColumn($table, 'publisher_name')) {
                DB::statement("ALTER TABLE `{$table}` MODIFY `publisher_name` VARCHAR(1000) NULL");
            }
            if (Schema::hasColumn($table, 'chapter_pages')) {
                DB::statement("ALTER TABLE `{$table}` MODIFY `chapter_pages` VARCHAR(1000) NULL");
            }
        }
    }

    public function down(): void
    {
        $tables = ['outputs', 'back_office_task_outputs', 'back_office_entry_outputs'];

        foreach ($tables as $table) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            if (Schema::hasColumn($table, 'book_title')) {
                DB::statement("ALTER TABLE `{$table}` MODIFY `book_title` VARCHAR(255) NULL");
            }
            if (Schema::hasColumn($table, 'publisher_name')) {
                DB::statement("ALTER TABLE `{$table}` MODIFY `publisher_name` VARCHAR(255) NULL");
            }
            if (Schema::hasColumn($table, 'chapter_pages')) {
                DB::statement("ALTER TABLE `{$table}` MODIFY `chapter_pages` VARCHAR(255) NULL");
            }
        }
    }
};
