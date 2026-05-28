<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wfa_reports', function (Blueprint $table) {
            $table->unsignedTinyInteger('filter_month')->nullable()->after('filter_year');
        });
    }

    public function down(): void
    {
        Schema::table('wfa_reports', function (Blueprint $table) {
            $table->dropColumn('filter_month');
        });
    }
};
