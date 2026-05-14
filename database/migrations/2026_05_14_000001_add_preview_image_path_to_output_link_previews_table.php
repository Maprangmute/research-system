<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('output_link_previews', function (Blueprint $table) {
            $table->string('preview_image_path')->nullable()->after('preview_image_url');
        });
    }

    public function down(): void
    {
        Schema::table('output_link_previews', function (Blueprint $table) {
            $table->dropColumn('preview_image_path');
        });
    }
};
