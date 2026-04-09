<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('external_outputs', function (Blueprint $table) {
            $table->string('book_title', 1000)->nullable()->after('impact_factor');
            $table->string('publisher_name', 1000)->nullable()->after('book_title');
            $table->string('chapter_pages', 1000)->nullable()->after('publisher_name');
        });
    }

    public function down(): void
    {
        Schema::table('external_outputs', function (Blueprint $table) {
            $table->dropColumn(['book_title', 'publisher_name', 'chapter_pages']);
        });
    }
};

