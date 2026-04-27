<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BackfillBackOfficeUserLinks extends Command
{
    protected $signature = 'backoffice:backfill-user-links
        {--dry-run : Preview backfill results without writing}
        {--force : Rebuild relation rows from legacy JSON even when relation rows already exist}';

    protected $description = 'Backfill Back Office user_id fields and relation tables from legacy text/JSON data';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $force = (bool) $this->option('force');

        $lookup = $this->buildUserLookup();
        if ($lookup->isEmpty()) {
            $this->warn('No users available for matching.');
            return self::FAILURE;
        }

        $this->info($dryRun ? 'Dry run mode: no updates will be written.' : 'Backfilling Back Office user links...');

        $entryStats = $this->backfillEntries($lookup, $dryRun);
        $this->line("entries: scanned={$entryStats['scanned']} matched={$entryStats['matched']} updated={$entryStats['updated']} ambiguous={$entryStats['ambiguous']} unmatched={$entryStats['unmatched']} blank={$entryStats['blank']}");

        $outputStats = $this->backfillOutputs($lookup, $dryRun, $force);
        $this->line("outputs: scanned={$outputStats['scanned']} synced={$outputStats['synced']} skipped={$outputStats['skipped']}");

        $this->info($dryRun ? 'Dry run complete.' : 'Back Office backfill complete.');

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

    private function normalizeNameKey(string $value): string
    {
        $value = preg_replace('/\s+/u', ' ', trim($value)) ?? '';

        return $value !== '' ? mb_strtolower($value) : '';
    }

    /**
     * @param  Collection<string, array<int, int>>  $lookup
     */
    private function resolveUserIdByStoredName(Collection $lookup, string $name): ?int
    {
        $normalized = $this->normalizeNameKey($name);
        if ($normalized === '') {
            return null;
        }

        $matches = $lookup->get($normalized, []);

        return count($matches) === 1 ? (int) $matches[0] : null;
    }

    /**
     * @param  Collection<string, array<int, int>>  $lookup
     * @return array<string, int>
     */
    private function backfillEntries(Collection $lookup, bool $dryRun): array
    {
        $stats = [
            'scanned' => 0,
            'matched' => 0,
            'updated' => 0,
            'ambiguous' => 0,
            'unmatched' => 0,
            'blank' => 0,
        ];

        if (
            !Schema::hasTable('back_office_entries') ||
            !Schema::hasColumn('back_office_entries', 'id') ||
            !Schema::hasColumn('back_office_entries', 'user_id') ||
            !Schema::hasColumn('back_office_entries', 'person_name')
        ) {
            return $stats;
        }

        DB::table('back_office_entries')
            ->select(['id', 'person_name'])
            ->whereNull('user_id')
            ->orderBy('id')
            ->chunkById(200, function ($rows) use ($lookup, $dryRun, &$stats): void {
                $hasUpdatedAt = Schema::hasColumn('back_office_entries', 'updated_at');

                foreach ($rows as $row) {
                    $stats['scanned']++;
                    $name = trim((string) ($row->person_name ?? ''));
                    $normalized = $this->normalizeNameKey($name);

                    if ($normalized === '') {
                        $stats['blank']++;
                        continue;
                    }

                    $matchedUserIds = $lookup->get($normalized, []);
                    if ($matchedUserIds === []) {
                        $stats['unmatched']++;
                        continue;
                    }

                    if (count($matchedUserIds) > 1) {
                        $stats['ambiguous']++;
                        continue;
                    }

                    $stats['matched']++;

                    if ($dryRun) {
                        continue;
                    }

                    $payload = ['user_id' => $matchedUserIds[0]];
                    if ($hasUpdatedAt) {
                        $payload['updated_at'] = now();
                    }

                    DB::table('back_office_entries')
                        ->where('id', $row->id)
                        ->whereNull('user_id')
                        ->update($payload);

                    $stats['updated']++;
                }
            });

        return $stats;
    }

    /**
     * @param  Collection<string, array<int, int>>  $lookup
     * @return array<string, int>
     */
    private function backfillOutputs(Collection $lookup, bool $dryRun, bool $force): array
    {
        $stats = [
            'scanned' => 0,
            'synced' => 0,
            'skipped' => 0,
        ];

        if (
            !Schema::hasTable('back_office_task_outputs') ||
            !Schema::hasColumn('back_office_task_outputs', 'id')
        ) {
            return $stats;
        }

        DB::table('back_office_task_outputs')
            ->select(['id', 'type', 'authors', 'book_authors', 'conferences', 'created_at', 'updated_at'])
            ->orderBy('id')
            ->chunkById(100, function ($rows) use ($lookup, $dryRun, $force, &$stats): void {
                foreach ($rows as $row) {
                    $stats['scanned']++;

                    $outputId = (int) $row->id;
                    $type = trim((string) ($row->type ?? ''));
                    $createdAt = $row->created_at ?? now();
                    $updatedAt = $row->updated_at ?? now();

                    if (!$force && $this->outputHasLinkedRows($outputId)) {
                        $stats['skipped']++;
                        continue;
                    }

                    $authors = $this->decodeJsonArray($row->authors ?? null);
                    $bookAuthors = $this->decodeJsonArray($row->book_authors ?? null);
                    $conferences = $this->decodeJsonArray($row->conferences ?? null);

                    if ($dryRun) {
                        $stats['synced']++;
                        continue;
                    }

                    DB::transaction(function () use ($outputId, $type, $authors, $bookAuthors, $conferences, $lookup, $createdAt, $updatedAt): void {
                        DB::table('back_office_output_authors')
                            ->where('back_office_task_output_id', $outputId)
                            ->delete();
                        DB::table('back_office_output_award_recipients')
                            ->where('back_office_task_output_id', $outputId)
                            ->delete();
                        DB::table('back_office_output_book_authors')
                            ->where('back_office_task_output_id', $outputId)
                            ->delete();

                        $conferenceIds = DB::table('back_office_output_conferences')
                            ->where('back_office_task_output_id', $outputId)
                            ->pluck('id');
                        if ($conferenceIds->isNotEmpty()) {
                            DB::table('back_office_output_conference_presenters')
                                ->whereIn('back_office_output_conference_id', $conferenceIds)
                                ->delete();
                        }
                        DB::table('back_office_output_conferences')
                            ->where('back_office_task_output_id', $outputId)
                            ->delete();

                        if (str_starts_with($type, 'EA118')) {
                            foreach ($authors as $author) {
                                if (!is_array($author) || trim((string) ($author['role'] ?? '')) !== 'awardee') {
                                    continue;
                                }

                                $name = trim((string) ($author['name'] ?? ''));
                                $personType = trim((string) ($author['person_type'] ?? ''));
                                if ($name === '' && $personType === '') {
                                    continue;
                                }

                                DB::table('back_office_output_award_recipients')->insert([
                                    'back_office_task_output_id' => $outputId,
                                    'user_id' => $name !== '' ? $this->resolveUserIdByStoredName($lookup, $name) : null,
                                    'person_type' => $personType !== '' ? $personType : null,
                                    'name' => $name !== '' ? $name : null,
                                    'created_at' => $createdAt,
                                    'updated_at' => $updatedAt,
                                ]);
                            }
                        } else {
                            foreach ($authors as $author) {
                                if (!is_array($author)) {
                                    continue;
                                }

                                $name = trim((string) ($author['name'] ?? ''));
                                if ($name === '') {
                                    continue;
                                }

                                DB::table('back_office_output_authors')->insert([
                                    'back_office_task_output_id' => $outputId,
                                    'user_id' => $this->resolveUserIdByStoredName($lookup, $name),
                                    'role' => trim((string) ($author['role'] ?? '')) ?: 'co',
                                    'person_type' => trim((string) ($author['person_type'] ?? '')) ?: null,
                                    'name' => $name,
                                    'created_at' => $createdAt,
                                    'updated_at' => $updatedAt,
                                ]);
                            }
                        }

                        if (str_starts_with($type, 'EA119')) {
                            foreach ($bookAuthors as $author) {
                                if (!is_array($author)) {
                                    continue;
                                }

                                $name = trim((string) ($author['name'] ?? ''));
                                if ($name === '') {
                                    continue;
                                }

                                DB::table('back_office_output_book_authors')->insert([
                                    'back_office_task_output_id' => $outputId,
                                    'user_id' => $this->resolveUserIdByStoredName($lookup, $name),
                                    'person_type' => trim((string) ($author['person_type'] ?? '')) ?: null,
                                    'name' => $name,
                                    'created_at' => $createdAt,
                                    'updated_at' => $updatedAt,
                                ]);
                            }
                        }

                        if (str_starts_with($type, 'EA103')) {
                            foreach ($conferences as $conference) {
                                if (!is_array($conference)) {
                                    continue;
                                }

                                $conferenceId = DB::table('back_office_output_conferences')->insertGetId([
                                    'back_office_task_output_id' => $outputId,
                                    'user_id' => null,
                                    'name' => trim((string) ($conference['name'] ?? '')) ?: null,
                                    'presentation_type' => trim((string) ($conference['presentation_type'] ?? '')) ?: null,
                                    'location' => trim((string) ($conference['location'] ?? '')) ?: null,
                                    'presenter_person_type' => trim((string) ($conference['presenter_person_type'] ?? '')) ?: null,
                                    'note' => trim((string) ($conference['note'] ?? '')) ?: null,
                                    'created_at' => $createdAt,
                                    'updated_at' => $updatedAt,
                                ]);

                                $presenters = is_array($conference['presenters'] ?? null) ? $conference['presenters'] : [];
                                if ($presenters === [] && (trim((string) ($conference['note'] ?? '')) !== '' || trim((string) ($conference['presenter_person_type'] ?? '')) !== '')) {
                                    $presenters[] = [
                                        'name' => trim((string) ($conference['note'] ?? '')),
                                        'person_type' => trim((string) ($conference['presenter_person_type'] ?? '')),
                                    ];
                                }

                                foreach ($presenters as $presenter) {
                                    if (!is_array($presenter)) {
                                        continue;
                                    }

                                    $name = trim((string) ($presenter['name'] ?? ''));
                                    $personType = trim((string) ($presenter['person_type'] ?? ''));
                                    if ($name === '' && $personType === '') {
                                        continue;
                                    }

                                    DB::table('back_office_output_conference_presenters')->insert([
                                        'back_office_output_conference_id' => $conferenceId,
                                        'user_id' => $name !== '' ? $this->resolveUserIdByStoredName($lookup, $name) : null,
                                        'person_type' => $personType !== '' ? $personType : null,
                                        'name' => $name !== '' ? $name : null,
                                        'created_at' => $createdAt,
                                        'updated_at' => $updatedAt,
                                    ]);
                                }
                            }
                        }
                    });

                    $stats['synced']++;
                }
            });

        return $stats;
    }

    private function outputHasLinkedRows(int $outputId): bool
    {
        return DB::table('back_office_output_authors')
            ->where('back_office_task_output_id', $outputId)
            ->exists()
            || DB::table('back_office_output_award_recipients')
                ->where('back_office_task_output_id', $outputId)
                ->exists()
            || DB::table('back_office_output_book_authors')
                ->where('back_office_task_output_id', $outputId)
                ->exists()
            || DB::table('back_office_output_conferences')
                ->where('back_office_task_output_id', $outputId)
                ->exists();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function decodeJsonArray(mixed $value): array
    {
        $decoded = json_decode((string) ($value ?? ''), true);

        return is_array($decoded) ? $decoded : [];
    }
}
