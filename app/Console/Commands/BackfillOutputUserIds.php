<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BackfillOutputUserIds extends Command
{
    protected $signature = 'outputs:backfill-user-ids
        {--dry-run : Preview matches without writing to the database}
        {--only= : Comma-separated table keys to process}';

    protected $description = 'Backfill nullable user_id fields in output-related tables from stored person names';

    /**
     * @return array<int, array<string, mixed>>
     */
    private function tableConfigs(): array
    {
        return [
            [
                'key' => 'output_authors',
                'table' => 'output_authors',
                'id_column' => 'id',
                'user_id_column' => 'user_id',
                'name_column' => 'name',
            ],
            [
                'key' => 'external_output_authors',
                'table' => 'external_output_authors',
                'id_column' => 'id',
                'user_id_column' => 'user_id',
                'name_column' => 'name',
            ],
            [
                'key' => 'output_award_recipients',
                'table' => 'output_award_recipients',
                'id_column' => 'id',
                'user_id_column' => 'user_id',
                'name_column' => 'name',
            ],
            [
                'key' => 'external_output_award_recipients',
                'table' => 'external_output_award_recipients',
                'id_column' => 'id',
                'user_id_column' => 'user_id',
                'name_column' => 'name',
            ],
            [
                'key' => 'output_book_authors',
                'table' => 'output_book_authors',
                'id_column' => 'id',
                'user_id_column' => 'user_id',
                'name_column' => 'name',
            ],
            [
                'key' => 'external_output_book_authors',
                'table' => 'external_output_book_authors',
                'id_column' => 'id',
                'user_id_column' => 'user_id',
                'name_column' => 'name',
            ],
            [
                'key' => 'output_conference_presenters',
                'table' => 'output_conference_presenters',
                'id_column' => 'id',
                'user_id_column' => 'user_id',
                'name_column' => 'name',
            ],
            [
                'key' => 'external_output_conference_presenters',
                'table' => 'external_output_conference_presenters',
                'id_column' => 'id',
                'user_id_column' => 'user_id',
                'name_column' => 'name',
            ],
        ];
    }

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $only = collect(explode(',', (string) $this->option('only')))
            ->map(fn ($item) => trim((string) $item))
            ->filter()
            ->values();

        $lookup = $this->buildUserLookup();
        if ($lookup->isEmpty()) {
            $this->warn('No user names available for matching.');
            return self::FAILURE;
        }

        $configs = collect($this->tableConfigs())
            ->filter(function (array $config) use ($only) {
                return $only->isEmpty() || $only->contains($config['key']);
            })
            ->values();

        if ($configs->isEmpty()) {
            $this->warn('No matching table keys selected.');
            return self::FAILURE;
        }

        $this->info($dryRun ? 'Dry run mode: no database updates will be written.' : 'Writing backfilled user_id values...');

        foreach ($configs as $config) {
            $this->processTable($config, $lookup, $dryRun);
            $this->newLine();
        }

        $this->info($dryRun ? 'Dry run complete.' : 'Backfill complete.');

        return self::SUCCESS;
    }

    /**
     * @return Collection<string, array<int, int>>
     */
    private function buildUserLookup(): Collection
    {
        $lookup = collect();

        User::query()
            ->select(['id', 'prefix', 'name', 'prefix_en', 'name_en'])
            ->orderBy('id')
            ->chunkById(500, function ($users) use (&$lookup): void {
                foreach ($users as $user) {
                    $candidates = array_filter([
                        $this->normalizeNameKey(trim(((string) $user->prefix) . ' ' . ((string) $user->name))),
                        $this->normalizeNameKey((string) $user->name),
                        $this->normalizeNameKey(trim(((string) $user->prefix_en) . ' ' . ((string) $user->name_en))),
                        $this->normalizeNameKey((string) $user->name_en),
                    ]);

                    foreach (array_unique($candidates) as $candidate) {
                        $existing = $lookup->get($candidate, []);
                        $existing[] = (int) $user->id;
                        $lookup->put($candidate, array_values(array_unique($existing)));
                    }
                }
            });

        return $lookup;
    }

    /**
     * @param  array<string, mixed>  $config
     * @param  Collection<string, array<int, int>>  $lookup
     */
    private function processTable(array $config, Collection $lookup, bool $dryRun): void
    {
        $table = (string) $config['table'];
        $idColumn = (string) $config['id_column'];
        $userIdColumn = (string) $config['user_id_column'];
        $nameColumn = (string) $config['name_column'];
        $key = (string) $config['key'];

        if (
            ! Schema::hasTable($table) ||
            ! Schema::hasColumn($table, $idColumn) ||
            ! Schema::hasColumn($table, $userIdColumn) ||
            ! Schema::hasColumn($table, $nameColumn)
        ) {
            $this->warn("[{$key}] skipped: required table/columns not found.");
            return;
        }

        $stats = [
            'scanned' => 0,
            'matched' => 0,
            'updated' => 0,
            'ambiguous' => 0,
            'unmatched' => 0,
            'blank' => 0,
        ];

        $ambiguousSamples = [];
        $unmatchedSamples = [];

        DB::table($table)
            ->select([$idColumn, $nameColumn])
            ->whereNull($userIdColumn)
            ->orderBy($idColumn)
            ->chunkById(500, function ($rows) use (
                $table,
                $idColumn,
                $nameColumn,
                $userIdColumn,
                $lookup,
                $dryRun,
                &$stats,
                &$ambiguousSamples,
                &$unmatchedSamples
            ): void {
                $hasUpdatedAt = Schema::hasColumn($table, 'updated_at');

                foreach ($rows as $row) {
                    $stats['scanned']++;

                    $rawName = trim((string) ($row->{$nameColumn} ?? ''));
                    $normalized = $this->normalizeNameKey($rawName);

                    if ($normalized === '') {
                        $stats['blank']++;
                        continue;
                    }

                    $matchedUserIds = $lookup->get($normalized, []);
                    if ($matchedUserIds === []) {
                        $stats['unmatched']++;
                        if (count($unmatchedSamples) < 10 && ! in_array($rawName, $unmatchedSamples, true)) {
                            $unmatchedSamples[] = $rawName;
                        }
                        continue;
                    }

                    if (count($matchedUserIds) > 1) {
                        $stats['ambiguous']++;
                        if (count($ambiguousSamples) < 10 && ! in_array($rawName, $ambiguousSamples, true)) {
                            $ambiguousSamples[] = $rawName;
                        }
                        continue;
                    }

                    $stats['matched']++;

                    if ($dryRun) {
                        continue;
                    }

                    $payload = [$userIdColumn => $matchedUserIds[0]];
                    if ($hasUpdatedAt) {
                        $payload['updated_at'] = now();
                    }

                    DB::table($table)
                        ->where($idColumn, $row->{$idColumn})
                        ->whereNull($userIdColumn)
                        ->update($payload);

                    $stats['updated']++;
                }
            }, $idColumn);

        $this->info("[{$key}] scanned={$stats['scanned']} matched={$stats['matched']} updated={$stats['updated']} ambiguous={$stats['ambiguous']} unmatched={$stats['unmatched']} blank={$stats['blank']}");

        if ($ambiguousSamples !== []) {
            $this->warn("[{$key}] ambiguous samples:");
            foreach ($ambiguousSamples as $sample) {
                $this->line("- {$sample}");
            }
        }

        if ($unmatchedSamples !== []) {
            $this->warn("[{$key}] unmatched samples:");
            foreach ($unmatchedSamples as $sample) {
                $this->line("- {$sample}");
            }
        }
    }

    private function normalizeNameKey(string $value): string
    {
        $value = preg_replace('/\s+/u', ' ', trim($value)) ?? '';

        return mb_strtolower($value, 'UTF-8');
    }
}
