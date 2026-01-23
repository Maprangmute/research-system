<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('back_office_entries', function (Blueprint $table) {
            if (!Schema::hasColumn('back_office_entries', 'document_link')) {
                $table->string('document_link')->nullable()->after('end_date');
            }
        });
    }

    public function down(): void
    {
        Schema::table('back_office_entries', function (Blueprint $table) {
            if (Schema::hasColumn('back_office_entries', 'document_link')) {
                $table->dropColumn('document_link');
            }
        });
    }
};
