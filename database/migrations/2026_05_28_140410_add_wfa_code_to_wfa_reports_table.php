<?php

use App\Models\WfaReport;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wfa_reports', function (Blueprint $table) {
            $table->string('wfa_code')->nullable()->after('id');
        });

        DB::table('wfa_reports')
            ->orderBy('id')
            ->select(['id'])
            ->chunk(100, function ($reports): void {
                foreach ($reports as $report) {
                    DB::table('wfa_reports')
                        ->where('id', $report->id)
                        ->update(['wfa_code' => WfaReport::formatCode((int) $report->id)]);
                }
            });

        Schema::table('wfa_reports', function (Blueprint $table) {
            $table->unique('wfa_code');
        });
    }

    public function down(): void
    {
        Schema::table('wfa_reports', function (Blueprint $table) {
            $table->dropUnique(['wfa_code']);
            $table->dropColumn('wfa_code');
        });
    }
};
