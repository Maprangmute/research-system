<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('back_office_entries')) {
            return;
        }

        DB::statement("ALTER TABLE `back_office_entries` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

        Schema::table('back_office_entries', function (Blueprint $table) {
            if (! Schema::hasColumn('back_office_entries', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('task_id')->constrained('users')->nullOnDelete();
            }
        });

        if (Schema::hasColumn('back_office_entries', 'person_name')) {
            DB::statement("ALTER TABLE `back_office_entries` MODIFY `person_name` VARCHAR(500) NOT NULL");
        }

        if (Schema::hasColumn('back_office_entries', 'job_name')) {
            DB::statement("ALTER TABLE `back_office_entries` MODIFY `job_name` VARCHAR(500) NOT NULL");
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('back_office_entries')) {
            return;
        }

        Schema::table('back_office_entries', function (Blueprint $table) {
            if (Schema::hasColumn('back_office_entries', 'user_id')) {
                $table->dropConstrainedForeignId('user_id');
            }
        });

        if (Schema::hasColumn('back_office_entries', 'person_name')) {
            DB::statement("ALTER TABLE `back_office_entries` MODIFY `person_name` VARCHAR(255) NOT NULL");
        }

        if (Schema::hasColumn('back_office_entries', 'job_name')) {
            DB::statement("ALTER TABLE `back_office_entries` MODIFY `job_name` VARCHAR(255) NOT NULL");
        }
    }
};
