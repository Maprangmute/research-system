<?php

namespace App\Support;

use App\Models\BackOfficeTask;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UserDisplayNameSync
{
    public static function linkExistingRecordsForUserAfterResponse(User $user): void
    {
        if (app()->runningInConsole()) {
            self::linkExistingRecordsForUser($user);
            return;
        }

        app()->terminating(static function () use ($user): void {
            self::linkExistingRecordsForUser($user->fresh() ?? $user);
        });
    }

    public static function linkExistingRecordsForUser(User $user): void
    {
        $userId = (int) $user->id;
        if ($userId <= 0) {
            return;
        }

        $fullName = trim((string) $user->full_name);
        $researchDisplayName = trim((string) $user->research_display_name);
        $candidates = self::userNameCandidates($user);

        if ($candidates === []) {
            return;
        }

        DB::transaction(function () use ($userId, $fullName, $researchDisplayName, $candidates): void {
            self::linkProjectPiByNames($userId, $fullName, $candidates);
            self::linkNameTableByNames('copis', 'user_id', 'name', $userId, $fullName, $candidates);
            self::linkNameTableByNames('co_research', 'user_id', 'name', $userId, $fullName, $candidates);
            self::linkNameTableByNames('fte_roles', 'user_id', 'name', $userId, $fullName, $candidates);

            self::linkNameTableByNames('output_authors', 'user_id', 'name', $userId, $researchDisplayName, $candidates);
            self::linkNameTableByNames('external_output_authors', 'user_id', 'name', $userId, $researchDisplayName, $candidates);
            self::linkNameTableByNames('output_award_recipients', 'user_id', 'name', $userId, $fullName, $candidates);
            self::linkNameTableByNames('external_output_award_recipients', 'user_id', 'name', $userId, $fullName, $candidates);
            self::linkNameTableByNames('output_book_authors', 'user_id', 'name', $userId, $fullName, $candidates);
            self::linkNameTableByNames('external_output_book_authors', 'user_id', 'name', $userId, $fullName, $candidates);
            self::linkNameTableByNames('output_conference_presenters', 'user_id', 'name', $userId, $fullName, $candidates);
            self::linkNameTableByNames('external_output_conference_presenters', 'user_id', 'name', $userId, $fullName, $candidates);

            self::linkNameTableByNames('back_office_entries', 'user_id', 'person_name', $userId, $fullName, $candidates);
            self::linkNameTableByNames('wfa_reports', 'user_id', 'person_name', $userId, $fullName, $candidates);
            self::linkNameTableByNames('back_office_task_outputs', 'owner_user_id', 'owner_name', $userId, $fullName, $candidates);
            self::linkNameTableByNames('back_office_output_contributors', 'user_id', 'name', $userId, $fullName, $candidates);
            self::linkBackOfficeOutputContributorsByNames($userId, $fullName, $candidates);
            self::linkNameTableByNames('back_office_output_authors', 'user_id', 'name', $userId, $researchDisplayName, $candidates);
            self::linkNameTableByNames('back_office_output_award_recipients', 'user_id', 'name', $userId, $fullName, $candidates);
            self::linkNameTableByNames('back_office_output_book_authors', 'user_id', 'name', $userId, $fullName, $candidates);
            self::linkNameTableByNames('back_office_output_conference_presenters', 'user_id', 'name', $userId, $fullName, $candidates);
        });
    }

    public static function syncAfterResponse(string $oldPrefix, string $oldName, string $newPrefix, string $newName, ?int $userId = null): void
    {
        if (app()->runningInConsole()) {
            self::sync($oldPrefix, $oldName, $newPrefix, $newName, $userId);
            return;
        }

        app()->terminating(static function () use ($oldPrefix, $oldName, $newPrefix, $newName, $userId): void {
            self::sync($oldPrefix, $oldName, $newPrefix, $newName, $userId);
        });
    }

    public static function sync(string $oldPrefix, string $oldName, string $newPrefix, string $newName, ?int $userId = null): void
    {
        $oldPrefix = trim($oldPrefix);
        $oldName = trim($oldName);
        $newPrefix = trim($newPrefix);
        $newName = trim($newName);

        $oldFull = trim($oldPrefix.' '.$oldName);
        $newFull = trim($newPrefix.' '.$newName);

        $replacements = [];

        if ($oldFull !== '' && $newFull !== '' && $oldFull !== $newFull) {
            $replacements[$oldFull] = $newFull;
        }

        if ($oldName !== '' && $newName !== '' && $oldName !== $newName) {
            $replacements[$oldName] = $newName;
        }

        if ($userId && $newFull !== '') {
            self::syncCopiLinkedUsers($userId, $newFull);
            self::syncCoResearchLinkedUsers($userId, $newFull);
            self::syncProjectLinkedUsers($userId, $newFull);
            self::syncOutputAuthorLinkedUsers($userId);
            self::syncOutputAwardRecipientLinkedUsers($userId, $newFull);
            self::syncOutputBookAuthorLinkedUsers($userId, $newFull);
            self::syncOutputConferenceLinkedUsers($userId, $newFull);
            self::syncBackOfficeEntryLinkedUsers($userId, $newFull);
            self::syncWfaReportLinkedUsers($userId, $newFull);
            self::syncBackOfficeOutputContributorLinkedUsers($userId, $newFull);
            self::syncBackOfficeOutputAuthorLinkedUsers($userId);
            self::syncBackOfficeOutputAwardRecipientLinkedUsers($userId, $newFull);
            self::syncBackOfficeOutputBookAuthorLinkedUsers($userId, $newFull);
            self::syncBackOfficeOutputConferenceLinkedUsers($userId, $newFull);
            if ($user = User::query()->find($userId)) {
                self::linkExistingRecordsForUser($user);
            }
        }

        if ($replacements === []) {
            return;
        }

        DB::transaction(function () use ($replacements): void {
            self::syncSimpleNameColumn('fte_roles', 'name', $replacements);
            self::syncSimpleNameColumn('copis', 'name', $replacements);
            self::syncSimpleNameColumn('co_research', 'name', $replacements);
            self::syncSimpleNameColumn('projects', 'pi_name', $replacements);

            self::syncOutputAuthors($replacements);
            self::syncSimpleNameColumn('output_award_recipients', 'name', $replacements);
            self::syncOutputBookAuthors($replacements);
            self::syncSimpleNameColumn('output_conference_presenters', 'name', $replacements);

            self::syncSimpleNameColumn('external_output_authors', 'name', $replacements);
            self::syncSimpleNameColumn('external_output_award_recipients', 'name', $replacements);
            self::syncSimpleNameColumn('external_output_book_authors', 'name', $replacements);
            self::syncSimpleNameColumn('external_output_conferences', 'presenter_name', $replacements);
            self::syncSimpleNameColumn('external_output_conference_presenters', 'name', $replacements);

            self::syncSimpleNameColumn('back_office_entries', 'person_name', $replacements);
            self::syncSimpleNameColumn('wfa_reports', 'person_name', $replacements);
            self::syncTextColumnOccurrences('wfa_reports', 'title', $replacements);
            self::syncSimpleNameColumn('back_office_output_contributors', 'name', $replacements);
            self::syncOutputAuthorsForTable('back_office_output_authors', $replacements);
            self::syncSimpleNameColumn('back_office_output_award_recipients', 'name', $replacements);
            self::syncSimpleNameColumn('back_office_output_book_authors', 'name', $replacements);
            self::syncSimpleNameColumn('back_office_output_conference_presenters', 'name', $replacements);
            self::syncSimpleNameColumn('progress_reports', 'submitter_name', $replacements);

            self::syncBackOfficeTaskLeaders($replacements);
            self::syncBackOfficeContributorsJsonColumn('back_office_task_outputs', 'contributors', $replacements);
            self::syncBackOfficeAuthorJsonColumn('back_office_task_outputs', 'authors', $replacements);
            self::syncBackOfficeConferenceJsonColumn('back_office_task_outputs', 'conferences', $replacements);
            self::syncBookAuthorJsonColumn('back_office_task_outputs', 'book_authors', $replacements);
            self::syncBookAuthorJsonColumn('back_office_entry_outputs', 'book_authors', $replacements);
        });
    }

    private static function syncCoResearchLinkedUsers(int $userId, string $fullName): void
    {
        if (
            !Schema::hasTable('co_research') ||
            !Schema::hasColumn('co_research', 'user_id') ||
            !Schema::hasColumn('co_research', 'name')
        ) {
            return;
        }

        $payload = ['name' => $fullName];

        if (Schema::hasColumn('co_research', 'updated_at')) {
            $payload['updated_at'] = now();
        }

        DB::table('co_research')
            ->where('user_id', $userId)
            ->update($payload);
    }

    private static function syncCopiLinkedUsers(int $userId, string $fullName): void
    {
        if (
            !Schema::hasTable('copis') ||
            !Schema::hasColumn('copis', 'user_id') ||
            !Schema::hasColumn('copis', 'name')
        ) {
            return;
        }

        $payload = ['name' => $fullName];

        if (Schema::hasColumn('copis', 'updated_at')) {
            $payload['updated_at'] = now();
        }

        DB::table('copis')
            ->where('user_id', $userId)
            ->update($payload);
    }

    private static function syncProjectLinkedUsers(int $userId, string $fullName): void
    {
        if (
            !Schema::hasTable('projects') ||
            !Schema::hasColumn('projects', 'pi_user_id') ||
            !Schema::hasColumn('projects', 'pi_name')
        ) {
            return;
        }

        $payload = ['pi_name' => $fullName];

        if (Schema::hasColumn('projects', 'updated_at')) {
            $payload['updated_at'] = now();
        }

        DB::table('projects')
            ->where('pi_user_id', $userId)
            ->update($payload);
    }

    private static function syncOutputAuthorLinkedUsers(int $userId): void
    {
        if (
            !Schema::hasTable('output_authors') ||
            !Schema::hasColumn('output_authors', 'user_id') ||
            !Schema::hasColumn('output_authors', 'name')
        ) {
            return;
        }

        $user = User::query()->find($userId);
        if (! $user) {
            return;
        }

        $researchDisplayName = trim((string) $user->research_display_name);
        $fullName = trim((string) $user->full_name);
        $hasUpdatedAt = Schema::hasColumn('output_authors', 'updated_at');

        DB::table('output_authors')
            ->select(['id', 'role'])
            ->where('user_id', $userId)
            ->orderBy('id')
            ->chunkById(200, function ($rows) use ($researchDisplayName, $fullName, $hasUpdatedAt): void {
                foreach ($rows as $row) {
                    $name = $row->role === 'awardee' ? $fullName : $researchDisplayName;
                    if ($name === '') {
                        continue;
                    }

                    $payload = ['name' => $name];

                    if ($hasUpdatedAt) {
                        $payload['updated_at'] = now();
                    }

                    DB::table('output_authors')->where('id', $row->id)->update($payload);
                }
            });
    }

    private static function syncOutputConferenceLinkedUsers(int $userId, string $fullName): void
    {
        if (
            !Schema::hasTable('output_conference_presenters') ||
            !Schema::hasColumn('output_conference_presenters', 'user_id') ||
            !Schema::hasColumn('output_conference_presenters', 'name')
        ) {
            return;
        }

        $payload = ['name' => $fullName];

        if (Schema::hasColumn('output_conference_presenters', 'updated_at')) {
            $payload['updated_at'] = now();
        }

        DB::table('output_conference_presenters')
            ->where('user_id', $userId)
            ->update($payload);
    }

    private static function syncOutputAwardRecipientLinkedUsers(int $userId, string $fullName): void
    {
        if (
            !Schema::hasTable('output_award_recipients') ||
            !Schema::hasColumn('output_award_recipients', 'user_id') ||
            !Schema::hasColumn('output_award_recipients', 'name')
        ) {
            return;
        }

        $payload = ['name' => $fullName];

        if (Schema::hasColumn('output_award_recipients', 'updated_at')) {
            $payload['updated_at'] = now();
        }

        DB::table('output_award_recipients')
            ->where('user_id', $userId)
            ->update($payload);
    }

    private static function syncOutputBookAuthorLinkedUsers(int $userId, string $fullName): void
    {
        if (
            !Schema::hasTable('output_book_authors') ||
            !Schema::hasColumn('output_book_authors', 'user_id') ||
            !Schema::hasColumn('output_book_authors', 'name')
        ) {
            return;
        }

        $payload = ['name' => $fullName];

        if (Schema::hasColumn('output_book_authors', 'updated_at')) {
            $payload['updated_at'] = now();
        }

        DB::table('output_book_authors')
            ->where('user_id', $userId)
            ->update($payload);
    }

    private static function syncBackOfficeEntryLinkedUsers(int $userId, string $fullName): void
    {
        if (
            !Schema::hasTable('back_office_entries') ||
            !Schema::hasColumn('back_office_entries', 'user_id') ||
            !Schema::hasColumn('back_office_entries', 'person_name')
        ) {
            return;
        }

        $payload = ['person_name' => $fullName];

        if (Schema::hasColumn('back_office_entries', 'updated_at')) {
            $payload['updated_at'] = now();
        }

        DB::table('back_office_entries')
            ->where('user_id', $userId)
            ->update($payload);
    }

    private static function syncWfaReportLinkedUsers(int $userId, string $fullName): void
    {
        if (
            !Schema::hasTable('wfa_reports') ||
            !Schema::hasColumn('wfa_reports', 'user_id') ||
            !Schema::hasColumn('wfa_reports', 'person_name')
        ) {
            return;
        }

        $payload = ['person_name' => $fullName];

        if (Schema::hasColumn('wfa_reports', 'updated_at')) {
            $payload['updated_at'] = now();
        }

        DB::table('wfa_reports')
            ->where('user_id', $userId)
            ->update($payload);
    }

    private static function syncBackOfficeOutputContributorLinkedUsers(int $userId, string $fullName): void
    {
        if (
            !Schema::hasTable('back_office_output_contributors') ||
            !Schema::hasColumn('back_office_output_contributors', 'user_id') ||
            !Schema::hasColumn('back_office_output_contributors', 'name')
        ) {
            return;
        }

        $payload = ['name' => $fullName];

        if (Schema::hasColumn('back_office_output_contributors', 'updated_at')) {
            $payload['updated_at'] = now();
        }

        DB::table('back_office_output_contributors')
            ->where('user_id', $userId)
            ->update($payload);
    }

    private static function syncBackOfficeOutputAuthorLinkedUsers(int $userId): void
    {
        if (
            !Schema::hasTable('back_office_output_authors') ||
            !Schema::hasColumn('back_office_output_authors', 'user_id') ||
            !Schema::hasColumn('back_office_output_authors', 'name')
        ) {
            return;
        }

        $user = User::query()->find($userId);
        if (! $user) {
            return;
        }

        $researchDisplayName = trim((string) $user->research_display_name);
        $fullName = trim((string) $user->full_name);
        $hasUpdatedAt = Schema::hasColumn('back_office_output_authors', 'updated_at');

        DB::table('back_office_output_authors')
            ->select(['id', 'role'])
            ->where('user_id', $userId)
            ->orderBy('id')
            ->chunkById(200, function ($rows) use ($researchDisplayName, $fullName, $hasUpdatedAt): void {
                foreach ($rows as $row) {
                    $name = $row->role === 'awardee' ? $fullName : $researchDisplayName;
                    if ($name === '') {
                        continue;
                    }

                    $payload = ['name' => $name];

                    if ($hasUpdatedAt) {
                        $payload['updated_at'] = now();
                    }

                    DB::table('back_office_output_authors')
                        ->where('id', $row->id)
                        ->update($payload);
                }
            });
    }

    private static function syncBackOfficeOutputAwardRecipientLinkedUsers(int $userId, string $fullName): void
    {
        if (
            !Schema::hasTable('back_office_output_award_recipients') ||
            !Schema::hasColumn('back_office_output_award_recipients', 'user_id') ||
            !Schema::hasColumn('back_office_output_award_recipients', 'name')
        ) {
            return;
        }

        $payload = ['name' => $fullName];

        if (Schema::hasColumn('back_office_output_award_recipients', 'updated_at')) {
            $payload['updated_at'] = now();
        }

        DB::table('back_office_output_award_recipients')
            ->where('user_id', $userId)
            ->update($payload);
    }

    private static function syncBackOfficeOutputBookAuthorLinkedUsers(int $userId, string $fullName): void
    {
        if (
            !Schema::hasTable('back_office_output_book_authors') ||
            !Schema::hasColumn('back_office_output_book_authors', 'user_id') ||
            !Schema::hasColumn('back_office_output_book_authors', 'name')
        ) {
            return;
        }

        $payload = ['name' => $fullName];

        if (Schema::hasColumn('back_office_output_book_authors', 'updated_at')) {
            $payload['updated_at'] = now();
        }

        DB::table('back_office_output_book_authors')
            ->where('user_id', $userId)
            ->update($payload);
    }

    private static function syncBackOfficeOutputConferenceLinkedUsers(int $userId, string $fullName): void
    {
        if (
            !Schema::hasTable('back_office_output_conference_presenters') ||
            !Schema::hasColumn('back_office_output_conference_presenters', 'user_id') ||
            !Schema::hasColumn('back_office_output_conference_presenters', 'name')
        ) {
            return;
        }

        $payload = ['name' => $fullName];

        if (Schema::hasColumn('back_office_output_conference_presenters', 'updated_at')) {
            $payload['updated_at'] = now();
        }

        DB::table('back_office_output_conference_presenters')
            ->where('user_id', $userId)
            ->update($payload);
    }

    private static function syncOutputAuthors(array $replacements): void
    {
        if (
            !Schema::hasTable('output_authors') ||
            !Schema::hasColumn('output_authors', 'id') ||
            !Schema::hasColumn('output_authors', 'output_id') ||
            !Schema::hasColumn('output_authors', 'role') ||
            !Schema::hasColumn('output_authors', 'name')
        ) {
            return;
        }

        $hasUpdatedAt = Schema::hasColumn('output_authors', 'updated_at');

        foreach ($replacements as $old => $new) {
            DB::table('output_authors')
                ->whereRaw('TRIM(name) = ?', [$old])
                ->orderBy('id')
                ->chunkById(200, function ($rows) use ($new, $hasUpdatedAt): void {
                    foreach ($rows as $row) {
                        $duplicateExists = DB::table('output_authors')
                            ->where('output_id', $row->output_id)
                            ->where('role', $row->role)
                            ->whereRaw('TRIM(name) = ?', [$new])
                            ->where('id', '!=', $row->id)
                            ->exists();

                        if ($duplicateExists) {
                            DB::table('output_authors')->where('id', $row->id)->delete();
                            continue;
                        }

                        $payload = ['name' => $new];
                        if ($hasUpdatedAt) {
                            $payload['updated_at'] = now();
                        }

                        DB::table('output_authors')->where('id', $row->id)->update($payload);
                    }
                });
        }
    }

    private static function syncOutputBookAuthors(array $replacements): void
    {
        if (
            !Schema::hasTable('output_book_authors') ||
            !Schema::hasColumn('output_book_authors', 'id') ||
            !Schema::hasColumn('output_book_authors', 'output_id') ||
            !Schema::hasColumn('output_book_authors', 'name')
        ) {
            return;
        }

        $hasUpdatedAt = Schema::hasColumn('output_book_authors', 'updated_at');

        foreach ($replacements as $old => $new) {
            DB::table('output_book_authors')
                ->whereRaw('TRIM(name) = ?', [$old])
                ->orderBy('id')
                ->chunkById(200, function ($rows) use ($new, $hasUpdatedAt): void {
                    foreach ($rows as $row) {
                        $duplicateExists = DB::table('output_book_authors')
                            ->where('output_id', $row->output_id)
                            ->whereRaw('TRIM(name) = ?', [$new])
                            ->where('id', '!=', $row->id)
                            ->exists();

                        if ($duplicateExists) {
                            DB::table('output_book_authors')->where('id', $row->id)->delete();
                            continue;
                        }

                        $payload = ['name' => $new];
                        if ($hasUpdatedAt) {
                            $payload['updated_at'] = now();
                        }

                        DB::table('output_book_authors')->where('id', $row->id)->update($payload);
                    }
                });
        }
    }

    private static function syncOutputAuthorsForTable(string $table, array $replacements): void
    {
        $hasOutputIdColumn = Schema::hasColumn($table, 'output_id');
        $hasBackOfficeOutputIdColumn = Schema::hasColumn($table, 'back_office_task_output_id');

        if (
            !Schema::hasTable($table) ||
            !Schema::hasColumn($table, 'id') ||
            (!$hasBackOfficeOutputIdColumn && !$hasOutputIdColumn) ||
            !Schema::hasColumn($table, 'role') ||
            !Schema::hasColumn($table, 'name')
        ) {
            return;
        }

        $outputIdColumn = $hasOutputIdColumn ? 'output_id' : 'back_office_task_output_id';
        $hasUpdatedAt = Schema::hasColumn($table, 'updated_at');

        foreach ($replacements as $old => $new) {
            DB::table($table)
                ->whereRaw('TRIM(name) = ?', [$old])
                ->orderBy('id')
                ->chunkById(200, function ($rows) use ($table, $outputIdColumn, $new, $hasUpdatedAt): void {
                    foreach ($rows as $row) {
                        $duplicateExists = DB::table($table)
                            ->where($outputIdColumn, $row->{$outputIdColumn})
                            ->where('role', $row->role)
                            ->whereRaw('TRIM(name) = ?', [$new])
                            ->where('id', '!=', $row->id)
                            ->exists();

                        if ($duplicateExists) {
                            DB::table($table)->where('id', $row->id)->delete();
                            continue;
                        }

                        $payload = ['name' => $new];
                        if ($hasUpdatedAt) {
                            $payload['updated_at'] = now();
                        }

                        DB::table($table)->where('id', $row->id)->update($payload);
                    }
                });
        }
    }

    private static function syncSimpleNameColumn(string $table, string $column, array $replacements): void
    {
        if (!Schema::hasTable($table) || !Schema::hasColumn($table, $column)) {
            return;
        }

        $hasUpdatedAt = Schema::hasColumn($table, 'updated_at');

        foreach ($replacements as $old => $new) {
            $query = DB::table($table)->whereRaw("TRIM({$column}) = ?", [$old]);
            $payload = [$column => $new];
            if ($hasUpdatedAt) {
                $payload['updated_at'] = now();
            }
            $query->update($payload);
        }
    }

    private static function syncTextColumnOccurrences(string $table, string $column, array $replacements): void
    {
        if (!Schema::hasTable($table) || !Schema::hasColumn($table, $column)) {
            return;
        }

        $hasUpdatedAt = Schema::hasColumn($table, 'updated_at');

        foreach ($replacements as $old => $new) {
            if ($old === '' || $old === $new) {
                continue;
            }

            $query = DB::table($table)->where($column, 'like', '%' . $old . '%');
            $payload = [$column => DB::raw('REPLACE(' . $column . ', ' . DB::getPdo()->quote($old) . ', ' . DB::getPdo()->quote($new) . ')')];
            if ($hasUpdatedAt) {
                $payload['updated_at'] = now();
            }
            $query->update($payload);
        }
    }

    /**
     * @return array<int, string>
     */
    private static function userNameCandidates(User $user): array
    {
        $values = [
            trim((string) $user->full_name),
            trim((string) $user->name),
            trim(((string) ($user->prefix_en ?? '')) . ' ' . ((string) ($user->name_en ?? ''))),
            trim((string) ($user->name_en ?? '')),
            trim((string) $user->research_display_name),
        ];

        return collect($values)
            ->map(fn ($value) => preg_replace('/\s+/u', ' ', trim((string) $value)) ?? '')
            ->filter()
            ->unique(fn ($value) => mb_strtolower($value, 'UTF-8'))
            ->values()
            ->all();
    }

    /**
     * @param  array<int, string>  $candidateNames
     */
    private static function linkProjectPiByNames(int $userId, string $fullName, array $candidateNames): void
    {
        if (
            !Schema::hasTable('projects') ||
            !Schema::hasColumn('projects', 'pi_user_id') ||
            !Schema::hasColumn('projects', 'pi_name')
        ) {
            return;
        }

        self::linkNameTableByNames('projects', 'pi_user_id', 'pi_name', $userId, $fullName, $candidateNames);
    }

    /**
     * @param  array<int, string>  $candidateNames
     */
    private static function linkNameTableByNames(string $table, string $userIdColumn, string $nameColumn, int $userId, string $storedName, array $candidateNames): void
    {
        if (
            $storedName === '' ||
            !Schema::hasTable($table) ||
            !Schema::hasColumn($table, $userIdColumn) ||
            !Schema::hasColumn($table, $nameColumn)
        ) {
            return;
        }

        $payload = [
            $userIdColumn => $userId,
            $nameColumn => $storedName,
        ];

        if (Schema::hasColumn($table, 'updated_at')) {
            $payload['updated_at'] = now();
        }

        DB::table($table)
            ->whereNull($userIdColumn)
            ->where(function ($query) use ($nameColumn, $candidateNames): void {
                foreach ($candidateNames as $candidate) {
                    $query->orWhereRaw("TRIM({$nameColumn}) = ?", [$candidate]);
                }
            })
            ->update($payload);
    }

    private static function syncBackOfficeTaskLeaders(array $replacements): void
    {
        if (!Schema::hasTable('back_office_tasks') || !Schema::hasColumn('back_office_tasks', 'leaders')) {
            return;
        }

        BackOfficeTask::query()
            ->whereNotNull('leaders')
            ->chunkById(200, function ($tasks) use ($replacements): void {
                foreach ($tasks as $task) {
                    $leaders = is_array($task->leaders) ? $task->leaders : [];
                    if ($leaders === []) {
                        continue;
                    }

                    $changed = false;
                    $updated = [];
                    foreach ($leaders as $leader) {
                        $trimmed = trim((string) $leader);
                        if ($trimmed !== '' && isset($replacements[$trimmed])) {
                            $updated[] = $replacements[$trimmed];
                            $changed = true;
                        } else {
                            $updated[] = $leader;
                        }
                    }

                    if ($changed) {
                        $task->leaders = $updated;
                        $task->save();
                    }
                }
            });
    }

    private static function syncBookAuthorJsonColumn(string $table, string $column, array $replacements): void
    {
        if (!Schema::hasTable($table) || !Schema::hasColumn($table, $column) || !Schema::hasColumn($table, 'id')) {
            return;
        }

        $hasUpdatedAt = Schema::hasColumn($table, 'updated_at');

        DB::table($table)
            ->whereNotNull($column)
            ->orderBy('id')
            ->chunkById(200, function ($rows) use ($table, $column, $replacements, $hasUpdatedAt): void {
                foreach ($rows as $row) {
                    $decoded = json_decode((string) $row->{$column}, true);
                    if (!is_array($decoded) || $decoded === []) {
                        continue;
                    }

                    $changed = false;
                    foreach ($decoded as &$author) {
                        if (!is_array($author)) {
                            continue;
                        }

                        $name = trim((string) ($author['name'] ?? ''));
                        if ($name !== '' && isset($replacements[$name])) {
                            $author['name'] = $replacements[$name];
                            $changed = true;
                        }
                    }
                    unset($author);

                    if (!$changed) {
                        continue;
                    }

                    $payload = [$column => json_encode($decoded, JSON_UNESCAPED_UNICODE)];
                    if ($hasUpdatedAt) {
                        $payload['updated_at'] = now();
                    }

                    DB::table($table)->where('id', $row->id)->update($payload);
                }
            });
    }

    /**
     * @param  array<int, string>  $candidateNames
     */
    private static function linkBackOfficeOutputContributorsByNames(int $userId, string $fullName, array $candidateNames): void
    {
        if (
            !Schema::hasTable('back_office_task_outputs') ||
            !Schema::hasColumn('back_office_task_outputs', 'id') ||
            !Schema::hasColumn('back_office_task_outputs', 'contributors')
        ) {
            return;
        }

        $hasUpdatedAt = Schema::hasColumn('back_office_task_outputs', 'updated_at');

        DB::table('back_office_task_outputs')
            ->whereNotNull('contributors')
            ->orderBy('id')
            ->chunkById(200, function ($rows) use ($userId, $fullName, $candidateNames, $hasUpdatedAt): void {
                foreach ($rows as $row) {
                    $decoded = json_decode((string) $row->contributors, true);
                    if (!is_array($decoded) || $decoded === []) {
                        continue;
                    }

                    $changed = false;
                    foreach ($decoded as &$contributor) {
                        if (!is_array($contributor)) {
                            continue;
                        }

                        if (!empty($contributor['user_id'])) {
                            continue;
                        }

                        $name = preg_replace('/\s+/u', ' ', trim((string) ($contributor['name'] ?? ''))) ?? '';
                        if ($name === '' || !in_array($name, $candidateNames, true)) {
                            continue;
                        }

                        $contributor['user_id'] = $userId;
                        $contributor['name'] = $fullName;
                        $changed = true;
                    }
                    unset($contributor);

                    if (!$changed) {
                        continue;
                    }

                    $payload = ['contributors' => json_encode($decoded, JSON_UNESCAPED_UNICODE)];
                    if ($hasUpdatedAt) {
                        $payload['updated_at'] = now();
                    }

                    DB::table('back_office_task_outputs')->where('id', $row->id)->update($payload);
                }
            });
    }

    private static function syncBackOfficeContributorsJsonColumn(string $table, string $column, array $replacements): void
    {
        if (!Schema::hasTable($table) || !Schema::hasColumn($table, $column) || !Schema::hasColumn($table, 'id')) {
            return;
        }

        $hasUpdatedAt = Schema::hasColumn($table, 'updated_at');

        DB::table($table)
            ->whereNotNull($column)
            ->orderBy('id')
            ->chunkById(200, function ($rows) use ($table, $column, $replacements, $hasUpdatedAt): void {
                foreach ($rows as $row) {
                    $decoded = json_decode((string) $row->{$column}, true);
                    if (!is_array($decoded) || $decoded === []) {
                        continue;
                    }

                    $changed = false;
                    foreach ($decoded as &$contributor) {
                        if (!is_array($contributor)) {
                            continue;
                        }

                        if (!empty($contributor['user_id'])) {
                            continue;
                        }

                        $name = trim((string) ($contributor['name'] ?? ''));
                        if ($name !== '' && isset($replacements[$name])) {
                            $contributor['name'] = $replacements[$name];
                            $changed = true;
                        }
                    }
                    unset($contributor);

                    if (!$changed) {
                        continue;
                    }

                    $payload = [$column => json_encode($decoded, JSON_UNESCAPED_UNICODE)];
                    if ($hasUpdatedAt) {
                        $payload['updated_at'] = now();
                    }

                    DB::table($table)->where('id', $row->id)->update($payload);
                }
            });
    }

    private static function syncBackOfficeAuthorJsonColumn(string $table, string $column, array $replacements): void
    {
        if (!Schema::hasTable($table) || !Schema::hasColumn($table, $column) || !Schema::hasColumn($table, 'id')) {
            return;
        }

        $hasUpdatedAt = Schema::hasColumn($table, 'updated_at');

        DB::table($table)
            ->whereNotNull($column)
            ->orderBy('id')
            ->chunkById(200, function ($rows) use ($table, $column, $replacements, $hasUpdatedAt): void {
                foreach ($rows as $row) {
                    $decoded = json_decode((string) $row->{$column}, true);
                    if (!is_array($decoded) || $decoded === []) {
                        continue;
                    }

                    $changed = false;
                    foreach ($decoded as &$author) {
                        if (!is_array($author)) {
                            continue;
                        }

                        $name = trim((string) ($author['name'] ?? ''));
                        if ($name !== '' && isset($replacements[$name])) {
                            $author['name'] = $replacements[$name];
                            $changed = true;
                        }
                    }
                    unset($author);

                    if (!$changed) {
                        continue;
                    }

                    $payload = [$column => json_encode($decoded, JSON_UNESCAPED_UNICODE)];
                    if ($hasUpdatedAt) {
                        $payload['updated_at'] = now();
                    }

                    DB::table($table)->where('id', $row->id)->update($payload);
                }
            });
    }

    private static function syncBackOfficeConferenceJsonColumn(string $table, string $column, array $replacements): void
    {
        if (!Schema::hasTable($table) || !Schema::hasColumn($table, $column) || !Schema::hasColumn($table, 'id')) {
            return;
        }

        $hasUpdatedAt = Schema::hasColumn($table, 'updated_at');

        DB::table($table)
            ->whereNotNull($column)
            ->orderBy('id')
            ->chunkById(200, function ($rows) use ($table, $column, $replacements, $hasUpdatedAt): void {
                foreach ($rows as $row) {
                    $decoded = json_decode((string) $row->{$column}, true);
                    if (!is_array($decoded) || $decoded === []) {
                        continue;
                    }

                    $changed = false;
                    foreach ($decoded as &$conference) {
                        if (!is_array($conference)) {
                            continue;
                        }

                        $note = trim((string) ($conference['note'] ?? ''));
                        if ($note !== '' && isset($replacements[$note])) {
                            $conference['note'] = $replacements[$note];
                            $changed = true;
                        }

                        if (!empty($conference['presenters']) && is_array($conference['presenters'])) {
                            foreach ($conference['presenters'] as &$presenter) {
                                if (!is_array($presenter)) {
                                    continue;
                                }

                                $name = trim((string) ($presenter['name'] ?? ''));
                                if ($name !== '' && isset($replacements[$name])) {
                                    $presenter['name'] = $replacements[$name];
                                    $changed = true;
                                }
                            }
                            unset($presenter);
                        }
                    }
                    unset($conference);

                    if (!$changed) {
                        continue;
                    }

                    $payload = [$column => json_encode($decoded, JSON_UNESCAPED_UNICODE)];
                    if ($hasUpdatedAt) {
                        $payload['updated_at'] = now();
                    }

                    DB::table($table)->where('id', $row->id)->update($payload);
                }
            });
    }
}
