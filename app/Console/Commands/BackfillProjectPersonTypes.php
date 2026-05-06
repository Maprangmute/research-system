<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BackfillProjectPersonTypes extends Command
{
    protected $signature = 'projects:backfill-person-types {--dry-run : Preview changes without updating database}';

    protected $description = 'Backfill PI and COPI person type fields from existing user links and affiliations';

    private const INTERNAL_FACULTY = 'ภายในคณะแพทยศาสตร์ศิริราชพยาบาล';
    private const EXTERNAL_FACULTY = 'ภายนอกคณะแพทยศาสตร์ศิริราชพยาบาล';
    private const INTERNAL_UNIT = 'ภายในหน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ';
    private const INTERNAL_AFFILIATION = 'หน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ คณะแพทยศาสตร์ศิริราชพยาบาล มหาวิทยาลัยมหิดล';

    public function handle(): int
    {
        if (! Schema::hasTable('projects') || ! Schema::hasTable('copis')) {
            $this->warn('Required tables are missing.');
            return self::FAILURE;
        }

        if (! Schema::hasColumn('projects', 'pi_person_type') || ! Schema::hasColumn('copis', 'person_type')) {
            $this->warn('Run migrations before backfilling person types.');
            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');
        $this->info($dryRun ? 'Dry run mode: no database updates will be written.' : 'Backfilling person type fields...');

        $projectStats = $this->backfillProjects($dryRun);
        $copiStats = $this->backfillCopis($dryRun);

        $this->newLine();
        $this->info("Projects scanned: {$projectStats['scanned']}");
        $this->info("Projects updated: {$projectStats['updated']}");
        $this->info("COPI scanned: {$copiStats['scanned']}");
        $this->info("COPI updated: {$copiStats['updated']}");

        if ($dryRun) {
            $this->comment('Re-run without --dry-run to write changes.');
        }

        return self::SUCCESS;
    }

    /**
     * @return array{scanned:int,updated:int}
     */
    private function backfillProjects(bool $dryRun): array
    {
        $stats = ['scanned' => 0, 'updated' => 0];
        $hasUpdatedAt = Schema::hasColumn('projects', 'updated_at');

        DB::table('projects')
            ->select(['id', 'pi_user_id', 'pi_name', 'pi_affiliation', 'pi_person_type'])
            ->orderBy('id')
            ->chunkById(500, function ($rows) use (&$stats, $dryRun, $hasUpdatedAt): void {
                foreach ($rows as $row) {
                    $stats['scanned']++;
                    $current = trim((string) ($row->pi_person_type ?? ''));
                    if ($current !== '') {
                        continue;
                    }

                    $type = $this->inferType($row->pi_user_id ?? null, $row->pi_affiliation ?? null, $row->pi_name ?? null);
                    if ($type === null) {
                        continue;
                    }

                    $stats['updated']++;
                    if ($dryRun) {
                        $this->line("projects #{$row->id}: {$type}");
                        continue;
                    }

                    $payload = ['pi_person_type' => $type];
                    if ($hasUpdatedAt) {
                        $payload['updated_at'] = now();
                    }

                    DB::table('projects')->where('id', $row->id)->update($payload);
                }
            });

        return $stats;
    }

    /**
     * @return array{scanned:int,updated:int}
     */
    private function backfillCopis(bool $dryRun): array
    {
        $stats = ['scanned' => 0, 'updated' => 0];
        $hasUpdatedAt = Schema::hasColumn('copis', 'updated_at');

        DB::table('copis')
            ->select(['id', 'user_id', 'name', 'affiliation', 'person_type'])
            ->orderBy('id')
            ->chunkById(500, function ($rows) use (&$stats, $dryRun, $hasUpdatedAt): void {
                foreach ($rows as $row) {
                    $stats['scanned']++;
                    $current = trim((string) ($row->person_type ?? ''));
                    if ($current !== '') {
                        continue;
                    }

                    $type = $this->inferType($row->user_id ?? null, $row->affiliation ?? null, $row->name ?? null);
                    if ($type === null) {
                        continue;
                    }

                    $stats['updated']++;
                    if ($dryRun) {
                        $this->line("copis #{$row->id}: {$type}");
                        continue;
                    }

                    $payload = ['person_type' => $type];
                    if ($hasUpdatedAt) {
                        $payload['updated_at'] = now();
                    }

                    DB::table('copis')->where('id', $row->id)->update($payload);
                }
            });

        return $stats;
    }

    private function inferType($userId, ?string $affiliation, ?string $name): ?string
    {
        $affiliation = trim((string) $affiliation);
        $name = trim((string) $name);

        if ((int) $userId > 0 || $affiliation === self::INTERNAL_AFFILIATION) {
            return self::INTERNAL_UNIT;
        }

        if ($affiliation === '' && $name === '') {
            return null;
        }

        if (str_contains($affiliation, 'คณะแพทยศาสตร์ศิริราชพยาบาล') || str_contains($affiliation, 'ศิริราช')) {
            return self::INTERNAL_FACULTY;
        }

        return self::EXTERNAL_FACULTY;
    }
}
