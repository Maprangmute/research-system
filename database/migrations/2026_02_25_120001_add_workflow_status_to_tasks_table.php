<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('tasks', 'workflow_status')) {
            Schema::table('tasks', function (Blueprint $table) {
                $table->string('workflow_status', 32)->nullable()->after('status');
            });
        }

        if (Schema::hasColumn('tasks', 'workflow_status')) {
            DB::table('tasks')->whereNull('workflow_status')->update([
                'workflow_status' => DB::raw("CASE WHEN status = 'done' THEN 'completed' ELSE 'in_progress' END"),
            ]);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('tasks', 'workflow_status')) {
            Schema::table('tasks', function (Blueprint $table) {
                $table->dropColumn('workflow_status');
            });
        }
    }
};
