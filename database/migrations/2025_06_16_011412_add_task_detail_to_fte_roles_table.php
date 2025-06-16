<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up(): void
{
    Schema::table('fte_roles', function (Blueprint $table) {
        $table->string('task_detail')->nullable()->after('fte_value');
    });
}

public function down(): void
{
    Schema::table('fte_roles', function (Blueprint $table) {
        $table->dropColumn('task_detail');
    });
}
};
