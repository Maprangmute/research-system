<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // database/migrations/xxxx_add_fields_to_users_table.php
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('prefix')->nullable();
            $table->string('position')->nullable();
            $table->string('profile_picture')->nullable();
            $table->enum('role', ['user', 'admin'])->default('user');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['prefix', 'position', 'profile_picture', 'role']);
        });
    }
};
