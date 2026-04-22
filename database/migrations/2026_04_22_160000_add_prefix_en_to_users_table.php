<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'prefix_en')) {
                $table->string('prefix_en')->nullable()->after('prefix');
            }

            if (! Schema::hasColumn('users', 'name_en')) {
                $table->string('name_en')->nullable()->after('prefix_en');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'name_en')) {
                $table->dropColumn('name_en');
            }

            if (Schema::hasColumn('users', 'prefix_en')) {
                $table->dropColumn('prefix_en');
            }
        });
    }
};
