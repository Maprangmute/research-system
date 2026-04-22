<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('output_book_authors', function (Blueprint $table) {
            if (! Schema::hasColumn('output_book_authors', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('output_id')->constrained('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('output_book_authors', function (Blueprint $table) {
            if (Schema::hasColumn('output_book_authors', 'user_id')) {
                $table->dropConstrainedForeignId('user_id');
            }
        });
    }
};
