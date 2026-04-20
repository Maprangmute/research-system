<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('back_office_entries', function (Blueprint $table) {
            $table->text('job_detail')->change();
        });
    }

    public function down(): void
    {
        Schema::table('back_office_entries', function (Blueprint $table) {
            $table->string('job_detail')->change();
        });
    }
};