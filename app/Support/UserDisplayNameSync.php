<?php

namespace App\Support;

use App\Models\BackOfficeTask;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UserDisplayNameSync
{
    public static function sync(string $oldPrefix, string $oldName, string $newPrefix, string $newName): void
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

        if ($replacements === []) {
            return;
        }

        DB::transaction(function () use ($replacements): void {
            self::syncSimpleNameColumn('fte_roles', 'name', $replacements);
            self::syncSimpleNameColumn('copis', 'name', $replacements);
            self::syncSimpleNameColumn('co_research', 'name', $replacements);
            self::syncSimpleNameColumn('projects', 'pi_name', $replacements);

            self::syncOutputAuthors($replacements);
            self::syncOutputBookAuthors($replacements);
            self::syncSimpleNameColumn('output_conferences', 'note', $replacements);

            self::syncSimpleNameColumn('external_output_authors', 'name', $replacements);
            self::syncSimpleNameColumn('external_output_conferences', 'presenter_name', $replacements);

            self::syncSimpleNameColumn('back_office_entries', 'person_name', $replacements);
            self::syncSimpleNameColumn('progress_reports', 'submitter_name', $replacements);

            self::syncBackOfficeTaskLeaders($replacements);
            self::syncBookAuthorJsonColumn('back_office_task_outputs', 'book_authors', $replacements);
            self::syncBookAuthorJsonColumn('back_office_entry_outputs', 'book_authors', $replacements);
        });
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
