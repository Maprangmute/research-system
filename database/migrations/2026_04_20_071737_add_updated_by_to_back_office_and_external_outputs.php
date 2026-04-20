<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('back_office_tasks', function (Blueprint $table) {
            if (!Schema::hasColumn('back_office_tasks', 'updated_by')) {
                $table->foreignId('updated_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
            }
        });

        Schema::table('external_outputs', function (Blueprint $table) {
            if (!Schema::hasColumn('external_outputs', 'updated_by')) {
                $table->foreignId('updated_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('external_outputs', function (Blueprint $table) {
            if (Schema::hasColumn('external_outputs', 'updated_by')) {
                $table->dropConstrainedForeignId('updated_by');
            }
        });

        Schema::table('back_office_tasks', function (Blueprint $table) {
            if (Schema::hasColumn('back_office_tasks', 'updated_by')) {
                $table->dropConstrainedForeignId('updated_by');
            }
        });
    }
};
