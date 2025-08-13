<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up()
{
    Schema::table('fte_roles', function (Blueprint $table) {
        $table->decimal('fte_value_research', 5, 2)->nullable()->after('fte_value');
        $table->decimal('fte_value_management', 5, 2)->nullable()->after('fte_value_research');
        $table->boolean('management_approved')->default(false)->after('fte_value_management');
        $table->unsignedBigInteger('approved_by')->nullable()->after('management_approved');
        $table->timestamp('approved_at')->nullable()->after('approved_by');
    });
}

public function down()
{
    Schema::table('fte_roles', function (Blueprint $table) {
        $table->dropColumn([
            'fte_value_research',
            'fte_value_management',
            'management_approved',
            'approved_by',
            'approved_at',
        ]);
    });
}
};
