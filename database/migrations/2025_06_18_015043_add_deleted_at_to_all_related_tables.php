<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('co_research', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('fte_roles', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('outputs', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('irbs', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('copis', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('tasks', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('task_links', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::table('co_research', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('fte_roles', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('outputs', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('irbs', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('copis', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('task_links', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
