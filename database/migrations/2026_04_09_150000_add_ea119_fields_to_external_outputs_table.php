<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('external_outputs')) {
            return;
        }

        Schema::table('external_outputs', function (Blueprint $table) {
            if (!Schema::hasColumn('external_outputs', 'book_title')) {
                $table->string('book_title', 1000)->nullable()->after('impact_factor');
            }
            if (!Schema::hasColumn('external_outputs', 'publisher_name')) {
                $table->string('publisher_name', 1000)->nullable()->after('book_title');
            }
            if (!Schema::hasColumn('external_outputs', 'chapter_pages')) {
                $table->string('chapter_pages', 1000)->nullable()->after('publisher_name');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('external_outputs')) {
            return;
        }

        $columns = array_values(array_filter([
            Schema::hasColumn('external_outputs', 'book_title') ? 'book_title' : null,
            Schema::hasColumn('external_outputs', 'publisher_name') ? 'publisher_name' : null,
            Schema::hasColumn('external_outputs', 'chapter_pages') ? 'chapter_pages' : null,
        ]));

        if ($columns === []) {
            return;
        }

        Schema::table('external_outputs', function (Blueprint $table) use ($columns) {
            $table->dropColumn($columns);
        });
    }
};
