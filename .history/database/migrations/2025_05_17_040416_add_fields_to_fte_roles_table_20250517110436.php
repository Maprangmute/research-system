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
        Schema::table('fte_roles', function (Blueprint $table) {
            $table->string('name')->after('project_id');
            $table->string('role_group')->after('name');
            $table->decimal('fte_value', 5, 2)->after('role_group');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fte_roles', function (Blueprint $table) {
            //
        });
    }
};
