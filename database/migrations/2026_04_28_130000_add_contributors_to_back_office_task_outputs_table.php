<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('back_office_task_outputs')) {
            return;
        }

        Schema::table('back_office_task_outputs', function (Blueprint $table) {
            if (! Schema::hasColumn('back_office_task_outputs', 'contributors')) {
                $table->json('contributors')->nullable()->after('owner_name');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('back_office_task_outputs')) {
            return;
        }

        Schema::table('back_office_task_outputs', function (Blueprint $table) {
            if (Schema::hasColumn('back_office_task_outputs', 'contributors')) {
                $table->dropColumn('contributors');
            }
        });
    }
};
