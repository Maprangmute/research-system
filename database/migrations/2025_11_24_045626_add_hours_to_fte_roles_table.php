<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddHoursToFteRolesTable extends Migration
{
    public function up()
    {
        Schema::table('fte_roles', function (Blueprint $table) {
            $table->float('hours')->nullable()->after('fte_value_research');
        });
    }

    public function down()
    {
        Schema::table('fte_roles', function (Blueprint $table) {
            $table->dropColumn('hours');
        });
    }
}
