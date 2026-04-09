<?php

namespace App\Console\Commands;

use App\Models\Project;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RemapProjectTypes extends Command
{
    protected $signature = 'projects:remap-types {--dry-run : Preview changes without updating database}';

    protected $description = 'Remap old project_type values to the new 6-category taxonomy';

    /**
     * @return array<string, string>
     */
    private function targetTypes(): array
    {
        return [
            '01' => '01 Aging and Frailty',
            '02' => '02 Health Systems and HTA',
            '03' => '03 Rational Drug Use (RDU)',
            '04' => '04 Real-World Evidence (RWE) and Clinical–Technical Cohorts',
            '05' => '05 Global Health',
            '06' => '06 Other Areas',
        ];
    }

    /**
     * @return array<string, string>
     */
    private function oldToNewCodeMap(): array
    {
        return [
            '01' => '01',
            '07' => '01',
            '03' => '02',
            '04' => '03',
            '06' => '04',
            '05' => '05',
            '02' => '06',
            '08' => '06',
            '09' => '06',
            '10' => '06',
            '11' => '06',
            '12' => '06',
        ];
    }

    /**
     * @param  array<string, string>  $targets
     * @param  array<string, string>  $oldToNew
     */
    private function resolveMappedType(string $raw, array $targets, array $oldToNew): ?string
    {
        $trimmed = trim($raw);
        if ($trimmed === '') {
            return null;
        }

        if (in_array($trimmed, $targets, true)) {
            return $trimmed;
        }

        if (!preg_match('/^(\d{2})/u', $trimmed, $match)) {
            return null;
        }

        $oldCode = $match[1];
        $newCode = $oldToNew[$oldCode] ?? null;

        if (!$newCode) {
            return null;
        }

        return $targets[$newCode] ?? null;
    }

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $targets = $this->targetTypes();
        $oldToNew = $this->oldToNewCodeMap();

        $total = 0;
        $updated = 0;
        $unchanged = 0;
        $unmapped = 0;
        $unmappedSamples = [];

        $this->info($dryRun ? 'Dry run mode: no database updates will be written.' : 'Applying project type remap...');

        Project::query()
            ->select(['id', 'project_type'])
            ->whereNotNull('project_type')
            ->whereRaw("TRIM(project_type) <> ''")
            ->orderBy('id')
            ->chunkById(500, function ($rows) use (
                $dryRun,
                $targets,
                $oldToNew,
                &$total,
                &$updated,
                &$unchanged,
                &$unmapped,
                &$unmappedSamples
            ) {
                foreach ($rows as $row) {
                    $total++;
                    $current = trim((string) $row->project_type);
                    $mapped = $this->resolveMappedType($current, $targets, $oldToNew);

                    if ($mapped === null) {
                        $unmapped++;
                        if (count($unmappedSamples) < 20 && !in_array($current, $unmappedSamples, true)) {
                            $unmappedSamples[] = $current;
                        }
                        continue;
                    }

                    if ($mapped === $current) {
                        $unchanged++;
                        continue;
                    }

                    $updated++;

                    if ($dryRun) {
                        $this->line("ID {$row->id}: {$current} => {$mapped}");
                        continue;
                    }

                    DB::table('projects')
                        ->where('id', $row->id)
                        ->update([
                            'project_type' => $mapped,
                            'updated_at' => now(),
                        ]);
                }
            });

        $this->newLine();
        $this->info("Scanned: {$total}");
        $this->info("Updated: {$updated}");
        $this->info("Unchanged: {$unchanged}");
        $this->info("Unmapped: {$unmapped}");

        if ($unmapped > 0 && $unmappedSamples !== []) {
            $this->warn('Unmapped samples:');
            foreach ($unmappedSamples as $sample) {
                $this->line("- {$sample}");
            }
        }

        if ($dryRun) {
            $this->comment('Re-run without --dry-run to write changes.');
        } else {
            $this->info('Done.');
        }

        return self::SUCCESS;
    }
}
