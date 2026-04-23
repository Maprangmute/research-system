<?php

namespace App\Support;

use App\Models\BackOfficeTask;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UserDisplayNameSync
{
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
            self::syncSimpleNameColumn('progress_reports', 'submitter_name', $replacements);

            self::syncBackOfficeTaskLeaders($replacements);
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
}
