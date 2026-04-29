<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('back_office_task_outputs')) {
            return;
        }

        $lookup = $this->buildUserLookup();
        $now = now();

        DB::table('back_office_task_outputs')
            ->select($this->outputSelectColumns())
            ->orderBy('id')
            ->chunkById(100, function ($outputs) use ($lookup, $now): void {
                foreach ($outputs as $output) {
                    $this->backfillContributors($output, $lookup, $now);
                    $this->backfillAuthors($output, $lookup, $now);
                    $this->backfillBookAuthors($output, $lookup, $now);
                    $this->backfillConferences($output, $lookup, $now);
                }
            });

        Schema::table('back_office_task_outputs', function (Blueprint $table) {
            foreach (['contributors', 'authors', 'book_authors', 'conferences'] as $column) {
                if (Schema::hasColumn('back_office_task_outputs', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('back_office_task_outputs')) {
            return;
        }

        Schema::table('back_office_task_outputs', function (Blueprint $table) {
            if (! Schema::hasColumn('back_office_task_outputs', 'contributors')) {
                $table->json('contributors')->nullable()->after('owner_name');
            }

            if (! Schema::hasColumn('back_office_task_outputs', 'authors')) {
                $table->json('authors')->nullable();
            }

            if (! Schema::hasColumn('back_office_task_outputs', 'conferences')) {
                $table->json('conferences')->nullable();
            }

            if (! Schema::hasColumn('back_office_task_outputs', 'book_authors')) {
                $table->json('book_authors')->nullable();
            }
        });
    }

    private function outputSelectColumns(): array
    {
        return collect([
            'id',
            'owner_user_id',
            'owner_name',
            'contributors',
            'authors',
            'book_authors',
            'conferences',
            'created_at',
            'updated_at',
        ])
            ->filter(fn (string $column): bool => Schema::hasColumn('back_office_task_outputs', $column))
            ->values()
            ->all();
    }

    private function buildUserLookup(): array
    {
        return User::query()
            ->select(['id', 'prefix', 'name'])
            ->get()
            ->flatMap(function (User $user): array {
                $fullName = $this->normalizeName($user->full_name);
                $plainName = $this->normalizeName($user->name);
                $names = array_values(array_unique(array_filter([$fullName, $plainName])));

                return collect($names)
                    ->mapWithKeys(fn (string $name): array => [$name => (int) $user->id])
                    ->all();
            })
            ->all();
    }

    private function backfillContributors(object $output, array $lookup, mixed $now): void
    {
        if (! Schema::hasTable('back_office_output_contributors')) {
            return;
        }

        if ($this->hasRows('back_office_output_contributors', 'back_office_task_output_id', (int) $output->id)) {
            return;
        }

        $contributors = $this->decodeJsonArray($output->contributors ?? null);

        if ($contributors === [] && (! empty($output->owner_user_id) || ! empty($output->owner_name))) {
            $contributors[] = [
                'user_id' => ! empty($output->owner_user_id) ? (int) $output->owner_user_id : null,
                'name' => $this->normalizeName((string) ($output->owner_name ?? '')),
            ];
        }

        $rows = collect($contributors)
            ->map(function ($contributor) use ($output, $lookup, $now): ?array {
                $contributor = is_array($contributor) ? $contributor : [];
                $name = $this->normalizeName((string) ($contributor['name'] ?? ''));
                $userId = ! empty($contributor['user_id']) ? (int) $contributor['user_id'] : ($lookup[$name] ?? null);

                if (! $userId && $name === '') {
                    return null;
                }

                return [
                    'back_office_task_output_id' => (int) $output->id,
                    'user_id' => $userId,
                    'name' => $name !== '' ? $name : null,
                    'created_at' => $output->created_at ?? $now,
                    'updated_at' => $output->updated_at ?? $now,
                ];
            })
            ->filter()
            ->unique(fn (array $row): string => $row['user_id'] ? 'user:'.$row['user_id'] : 'name:'.($row['name'] ?? ''))
            ->values()
            ->all();

        if ($rows !== []) {
            DB::table('back_office_output_contributors')->insert($rows);
        }
    }

    private function backfillAuthors(object $output, array $lookup, mixed $now): void
    {
        if (! Schema::hasTable('back_office_output_authors')) {
            return;
        }

        $authors = $this->decodeJsonArray($output->authors ?? null);
        if ($authors === []) {
            return;
        }

        $authorRows = [];
        $awardRows = [];

        foreach ($authors as $author) {
            if (! is_array($author)) {
                continue;
            }

            $name = $this->normalizeName((string) ($author['name'] ?? ''));
            if ($name === '') {
                continue;
            }

            $role = trim((string) ($author['role'] ?? ''));
            $row = [
                'back_office_task_output_id' => (int) $output->id,
                'user_id' => ! empty($author['user_id']) ? (int) $author['user_id'] : ($lookup[$name] ?? null),
                'person_type' => trim((string) ($author['person_type'] ?? '')) ?: null,
                'name' => $name,
                'created_at' => $output->created_at ?? $now,
                'updated_at' => $output->updated_at ?? $now,
            ];

            if ($role === 'awardee') {
                $awardRows[] = $row;
                continue;
            }

            $authorRows[] = array_merge($row, [
                'role' => $role !== '' ? $role : 'co',
            ]);
        }

        if ($authorRows !== [] && ! $this->hasRows('back_office_output_authors', 'back_office_task_output_id', (int) $output->id)) {
            DB::table('back_office_output_authors')->insertOrIgnore($authorRows);
        }

        if (
            $awardRows !== [] &&
            Schema::hasTable('back_office_output_award_recipients') &&
            ! $this->hasRows('back_office_output_award_recipients', 'back_office_task_output_id', (int) $output->id)
        ) {
            DB::table('back_office_output_award_recipients')->insert($awardRows);
        }
    }

    private function backfillBookAuthors(object $output, array $lookup, mixed $now): void
    {
        if (! Schema::hasTable('back_office_output_book_authors')) {
            return;
        }

        if ($this->hasRows('back_office_output_book_authors', 'back_office_task_output_id', (int) $output->id)) {
            return;
        }

        $rows = collect($this->decodeJsonArray($output->book_authors ?? null))
            ->map(function ($author) use ($output, $lookup, $now): ?array {
                $author = is_array($author) ? $author : [];
                $name = $this->normalizeName((string) ($author['name'] ?? ''));
                if ($name === '') {
                    return null;
                }

                return [
                    'back_office_task_output_id' => (int) $output->id,
                    'user_id' => ! empty($author['user_id']) ? (int) $author['user_id'] : ($lookup[$name] ?? null),
                    'person_type' => trim((string) ($author['person_type'] ?? '')) ?: null,
                    'name' => $name,
                    'created_at' => $output->created_at ?? $now,
                    'updated_at' => $output->updated_at ?? $now,
                ];
            })
            ->filter()
            ->values()
            ->all();

        if ($rows !== []) {
            DB::table('back_office_output_book_authors')->insertOrIgnore($rows);
        }
    }

    private function backfillConferences(object $output, array $lookup, mixed $now): void
    {
        if (! Schema::hasTable('back_office_output_conferences')) {
            return;
        }

        if ($this->hasRows('back_office_output_conferences', 'back_office_task_output_id', (int) $output->id)) {
            return;
        }

        foreach ($this->decodeJsonArray($output->conferences ?? null) as $conference) {
            if (! is_array($conference)) {
                continue;
            }

            $conferenceId = DB::table('back_office_output_conferences')->insertGetId([
                'back_office_task_output_id' => (int) $output->id,
                'user_id' => null,
                'name' => $this->nullableName($conference['name'] ?? ''),
                'presentation_type' => $this->nullableName($conference['presentation_type'] ?? ''),
                'location' => $this->nullableName($conference['location'] ?? ''),
                'presenter_person_type' => $this->nullableName($conference['presenter_person_type'] ?? ''),
                'note' => $this->nullableName($conference['note'] ?? ''),
                'created_at' => $output->created_at ?? $now,
                'updated_at' => $output->updated_at ?? $now,
            ]);

            $presenters = is_array($conference['presenters'] ?? null) ? $conference['presenters'] : [];
            if ($presenters === [] && $this->nullableName($conference['presenter_name'] ?? '') !== null) {
                $presenters[] = [
                    'name' => $conference['presenter_name'],
                    'person_type' => $conference['presenter_person_type'] ?? null,
                ];
            }

            foreach ($presenters as $presenter) {
                if (! is_array($presenter)) {
                    continue;
                }

                $name = $this->normalizeName((string) ($presenter['name'] ?? ''));
                $personType = trim((string) ($presenter['person_type'] ?? ''));

                if ($name === '' && $personType === '') {
                    continue;
                }

                DB::table('back_office_output_conference_presenters')->insert([
                    'back_office_output_conference_id' => $conferenceId,
                    'user_id' => ! empty($presenter['user_id']) ? (int) $presenter['user_id'] : ($lookup[$name] ?? null),
                    'person_type' => $personType !== '' ? $personType : null,
                    'name' => $name !== '' ? $name : null,
                    'created_at' => $output->created_at ?? $now,
                    'updated_at' => $output->updated_at ?? $now,
                ]);
            }
        }
    }

    private function hasRows(string $table, string $column, int $id): bool
    {
        return Schema::hasTable($table) && DB::table($table)->where($column, $id)->exists();
    }

    private function decodeJsonArray(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (! is_string($value) || trim($value) === '') {
            return [];
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? $decoded : [];
    }

    private function nullableName(mixed $value): ?string
    {
        $name = $this->normalizeName((string) $value);

        return $name !== '' ? $name : null;
    }

    private function normalizeName(string $name): string
    {
        return preg_replace('/\s+/u', ' ', trim($name)) ?? '';
    }
};
