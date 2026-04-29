<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('outputs') || ! Schema::hasColumn('outputs', 'quartile')) {
            return;
        }

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE `outputs` MODIFY `quartile` VARCHAR(32) NULL');
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('outputs') || ! Schema::hasColumn('outputs', 'quartile')) {
            return;
        }

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `outputs` MODIFY `quartile` ENUM('Q1','Q2','Q3','Q4') NULL");
        }
    }
};
