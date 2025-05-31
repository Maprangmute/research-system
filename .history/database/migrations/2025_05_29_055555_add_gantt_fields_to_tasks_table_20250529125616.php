<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->unsignedTinyInteger('progress')->default(0)->after('end');
            $table->unsignedInteger('order')->default(0)->after('parent_id');
            $table->enum('status', ['ongoing', 'done'])->default('ongoing')->after('order');
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn(['progress', 'order', 'status']);
        });
    }
};
