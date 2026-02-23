<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('progress_reports')) {
            return;
        }

        Schema::table('progress_reports', function (Blueprint $table) {
            if (!Schema::hasColumn('progress_reports', 'progress_title')) {
                $table->string('progress_title')->nullable()->after('submit_date');
            }
            if (!Schema::hasColumn('progress_reports', 'progress_subtitle')) {
                $table->string('progress_subtitle')->nullable()->after('progress_title');
            }
            if (!Schema::hasColumn('progress_reports', 'progress_rows')) {
                $table->json('progress_rows')->nullable()->after('file_info');
            }
            if (!Schema::hasColumn('progress_reports', 'issue_rows')) {
                $table->json('issue_rows')->nullable()->after('progress_rows');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('progress_reports')) {
            return;
        }

        $columns = ['progress_title', 'progress_subtitle', 'progress_rows', 'issue_rows'];
        $existing = array_values(array_filter($columns, fn($column) => Schema::hasColumn('progress_reports', $column)));
        if (!$existing) {
            return;
        }

        Schema::table('progress_reports', function (Blueprint $table) use ($existing) {
            $table->dropColumn($existing);
        });
    }
};
