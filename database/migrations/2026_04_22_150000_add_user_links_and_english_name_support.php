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

        Schema::table('output_authors', function (Blueprint $table) {
            if (! Schema::hasColumn('output_authors', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('output_id')->constrained('users')->nullOnDelete();
            }
        });

        Schema::table('output_conferences', function (Blueprint $table) {
            if (! Schema::hasColumn('output_conferences', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('output_id')->constrained('users')->nullOnDelete();
            }
        });

        Schema::table('external_output_authors', function (Blueprint $table) {
            if (! Schema::hasColumn('external_output_authors', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('external_output_id')->constrained('users')->nullOnDelete();
            }
        });

        Schema::table('external_output_conferences', function (Blueprint $table) {
            if (! Schema::hasColumn('external_output_conferences', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('external_output_id')->constrained('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('external_output_conferences', function (Blueprint $table) {
            if (Schema::hasColumn('external_output_conferences', 'user_id')) {
                $table->dropConstrainedForeignId('user_id');
            }
        });

        Schema::table('external_output_authors', function (Blueprint $table) {
            if (Schema::hasColumn('external_output_authors', 'user_id')) {
                $table->dropConstrainedForeignId('user_id');
            }
        });

        Schema::table('output_conferences', function (Blueprint $table) {
            if (Schema::hasColumn('output_conferences', 'user_id')) {
                $table->dropConstrainedForeignId('user_id');
            }
        });

        Schema::table('output_authors', function (Blueprint $table) {
            if (Schema::hasColumn('output_authors', 'user_id')) {
                $table->dropConstrainedForeignId('user_id');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'prefix_en')) {
                $table->dropColumn('prefix_en');
            }

            if (Schema::hasColumn('users', 'name_en')) {
                $table->dropColumn('name_en');
            }
        });
    }
};
