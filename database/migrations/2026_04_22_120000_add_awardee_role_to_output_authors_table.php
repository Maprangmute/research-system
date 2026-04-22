<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::statement("ALTER TABLE output_authors MODIFY COLUMN role ENUM('first','co','corresponding','awardee') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE output_authors MODIFY COLUMN role ENUM('first','co','corresponding') NOT NULL");
    }
};
