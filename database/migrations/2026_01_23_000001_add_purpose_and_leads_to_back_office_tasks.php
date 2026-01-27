<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('back_office_tasks', function (Blueprint $table) {
            if (!Schema::hasColumn('back_office_tasks', 'purpose')) {
                $table->text('purpose')->nullable()->after('description');
            }
            if (!Schema::hasColumn('back_office_tasks', 'leaders')) {
                $table->json('leaders')->nullable()->after('purpose');
            }
        });
    }

    public function down(): void
    {
        Schema::table('back_office_tasks', function (Blueprint $table) {
            if (Schema::hasColumn('back_office_tasks', 'leaders')) {
                $table->dropColumn('leaders');
            }
            if (Schema::hasColumn('back_office_tasks', 'purpose')) {
                $table->dropColumn('purpose');
            }
        });
    }
};
