<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('back_office_entries', function (Blueprint $table) {
            if (!Schema::hasColumn('back_office_entries', 'start_date')) {
                $table->string('start_date')->nullable()->after('job_detail');
            }
            if (!Schema::hasColumn('back_office_entries', 'end_date')) {
                $table->string('end_date')->nullable()->after('start_date');
            }
        });
    }

    public function down(): void
    {
        Schema::table('back_office_entries', function (Blueprint $table) {
            if (Schema::hasColumn('back_office_entries', 'end_date')) {
                $table->dropColumn('end_date');
            }
            if (Schema::hasColumn('back_office_entries', 'start_date')) {
                $table->dropColumn('start_date');
            }
        });
    }
};
