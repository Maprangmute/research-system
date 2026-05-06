<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('projects') && ! Schema::hasColumn('projects', 'pi_person_type')) {
            Schema::table('projects', function (Blueprint $table) {
                $table->string('pi_person_type')->nullable()->after('pi_affiliation');
            });
        }

        if (Schema::hasTable('copis') && ! Schema::hasColumn('copis', 'person_type')) {
            Schema::table('copis', function (Blueprint $table) {
                $table->string('person_type')->nullable()->after('affiliation');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('copis') && Schema::hasColumn('copis', 'person_type')) {
            Schema::table('copis', function (Blueprint $table) {
                $table->dropColumn('person_type');
            });
        }

        if (Schema::hasTable('projects') && Schema::hasColumn('projects', 'pi_person_type')) {
            Schema::table('projects', function (Blueprint $table) {
                $table->dropColumn('pi_person_type');
            });
        }
    }
};
