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
            if (! Schema::hasColumn('back_office_task_outputs', 'owner_user_id')) {
                $table->foreignId('owner_user_id')
                    ->nullable()
                    ->after('task_id')
                    ->constrained('users')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('back_office_task_outputs', 'owner_name')) {
                $table->string('owner_name')->nullable()->after('owner_user_id');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('back_office_task_outputs')) {
            return;
        }

        Schema::table('back_office_task_outputs', function (Blueprint $table) {
            if (Schema::hasColumn('back_office_task_outputs', 'owner_user_id')) {
                $table->dropConstrainedForeignId('owner_user_id');
            }

            if (Schema::hasColumn('back_office_task_outputs', 'owner_name')) {
                $table->dropColumn('owner_name');
            }
        });
    }
};
