<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['outputs', 'external_outputs'] as $tableName) {
            if (! Schema::hasTable($tableName) || Schema::hasColumn($tableName, 'isbn')) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) {
                $table->string('isbn', 1000)->nullable()->after('chapter_pages');
            });
        }
    }

    public function down(): void
    {
        foreach (['outputs', 'external_outputs'] as $tableName) {
            if (! Schema::hasTable($tableName) || ! Schema::hasColumn($tableName, 'isbn')) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) {
                $table->dropColumn('isbn');
            });
        }
    }
};
