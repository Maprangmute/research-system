<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('projects') || ! Schema::hasColumn('projects', 'data_method')) {
            return;
        }

        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('data_method');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('projects') || Schema::hasColumn('projects', 'data_method')) {
            return;
        }

        Schema::table('projects', function (Blueprint $table) {
            $table->string('data_method')->nullable()->after('data_source');
        });
    }
};
