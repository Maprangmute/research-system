<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE `back_office_tasks` MODIFY `start_date` VARCHAR(255) NULL');
        DB::statement('ALTER TABLE `back_office_tasks` MODIFY `end_date` VARCHAR(255) NULL');
    }

    public function down(): void
    {
        $today = now('Asia/Bangkok')->format('d-m-Y');

        DB::table('back_office_tasks')->whereNull('start_date')->update(['start_date' => $today]);
        DB::table('back_office_tasks')->whereNull('end_date')->update(['end_date' => $today]);

        DB::statement('ALTER TABLE `back_office_tasks` MODIFY `start_date` VARCHAR(255) NOT NULL');
        DB::statement('ALTER TABLE `back_office_tasks` MODIFY `end_date` VARCHAR(255) NOT NULL');
    }
};
