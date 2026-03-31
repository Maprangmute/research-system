<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('back_office_entries')) {
            return;
        }

        Schema::table('back_office_entries', function (Blueprint $table) {
            if (!Schema::hasColumn('back_office_entries', 'job_name')) {
                $table->string('job_name')->nullable()->after('category');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('back_office_entries') || !Schema::hasColumn('back_office_entries', 'job_name')) {
            return;
        }

        Schema::table('back_office_entries', function (Blueprint $table) {
            $table->dropColumn('job_name');
        });
    }
};
