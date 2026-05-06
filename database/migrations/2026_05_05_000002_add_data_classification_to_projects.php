<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('projects')) {
            return;
        }

        Schema::table('projects', function (Blueprint $table) {
            if (! Schema::hasColumn('projects', 'data_source')) {
                $table->string('data_source')->nullable()->after('theme');
            }

            if (! Schema::hasColumn('projects', 'data_method')) {
                $table->string('data_method')->nullable()->after('data_source');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('projects')) {
            return;
        }

        Schema::table('projects', function (Blueprint $table) {
            if (Schema::hasColumn('projects', 'data_method')) {
                $table->dropColumn('data_method');
            }

            if (Schema::hasColumn('projects', 'data_source')) {
                $table->dropColumn('data_source');
            }
        });
    }
};
