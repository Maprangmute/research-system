<?php

namespace App\Console\Commands;

use App\Models\BackOfficeTask;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BackfillBackOfficeIds extends Command
{
    protected $signature = 'backoffice:backfill-ids {--dry-run : Preview updates without writing}';

    protected $description = 'Backfill BO_#### identifiers for Back Office tasks';

    public function handle(): int
    {
        if (!Schema::hasTable('back_office_tasks') || !Schema::hasColumn('back_office_tasks', 'backoffice_id')) {
            $this->error('back_office_tasks.backoffice_id does not exist. Run migrations first.');
            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');
        $scanned = 0;
        $updated = 0;
        $skipped = 0;

        DB::table('back_office_tasks')
            ->select(['id', 'backoffice_id'])
            ->orderBy('id')
            ->chunkById(200, function ($tasks) use ($dryRun, &$scanned, &$updated, &$skipped): void {
                foreach ($tasks as $task) {
                    $scanned++;

                    if (filled($task->backoffice_id)) {
                        $skipped++;
                        continue;
                    }

                    $updated++;
                    $backofficeId = BackOfficeTask::formatBackOfficeId((int) $task->id);

                    if (! $dryRun) {
                        DB::table('back_office_tasks')
                            ->where('id', $task->id)
                            ->update(['backoffice_id' => $backofficeId]);
                    }
                }
            });

        $this->line(($dryRun ? 'Dry run: ' : '') . "scanned={$scanned} updated={$updated} skipped={$skipped}");

        return self::SUCCESS;
    }
}
