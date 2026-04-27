<?php

use App\Models\BackOfficeTask;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('back_office_tasks')) {
            return;
        }

        DB::statement("ALTER TABLE `back_office_tasks` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

        if (!Schema::hasColumn('back_office_tasks', 'backoffice_id')) {
            Schema::table('back_office_tasks', function (Blueprint $table) {
                $table->string('backoffice_id', 20)->nullable()->after('id');
            });
        }

        DB::table('back_office_tasks')
            ->select('id', 'backoffice_id')
            ->orderBy('id')
            ->chunkById(200, function ($tasks) {
                foreach ($tasks as $task) {
                    if (filled($task->backoffice_id)) {
                        continue;
                    }

                    DB::table('back_office_tasks')
                        ->where('id', $task->id)
                        ->update([
                            'backoffice_id' => BackOfficeTask::formatBackOfficeId((int) $task->id),
                        ]);
                }
            });

        if (! $this->hasIndex('back_office_tasks', 'back_office_tasks_backoffice_id_unique')) {
            Schema::table('back_office_tasks', function (Blueprint $table) {
                $table->unique('backoffice_id', 'back_office_tasks_backoffice_id_unique');
            });
        }

        DB::statement("ALTER TABLE `back_office_tasks` MODIFY `backoffice_id` VARCHAR(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL");
    }

    public function down(): void
    {
        if (!Schema::hasTable('back_office_tasks') || !Schema::hasColumn('back_office_tasks', 'backoffice_id')) {
            return;
        }

        $hasBackofficeIdUnique = $this->hasIndex('back_office_tasks', 'back_office_tasks_backoffice_id_unique');

        Schema::table('back_office_tasks', function (Blueprint $table) use ($hasBackofficeIdUnique) {
            if ($hasBackofficeIdUnique) {
                $table->dropUnique('back_office_tasks_backoffice_id_unique');
            }

            $table->dropColumn('backoffice_id');
        });
    }

    private function hasIndex(string $table, string $index): bool
    {
        $database = DB::getDatabaseName();

        return DB::table('information_schema.statistics')
            ->where('table_schema', $database)
            ->where('table_name', $table)
            ->where('index_name', $index)
            ->exists();
    }
};
