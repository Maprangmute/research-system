<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('back_office_task_outputs')) {
            return;
        }

        Schema::dropIfExists('back_office_output_conference_presenters');
        Schema::dropIfExists('back_office_output_conferences');
        Schema::dropIfExists('back_office_output_book_authors');
        Schema::dropIfExists('back_office_output_award_recipients');
        Schema::dropIfExists('back_office_output_authors');

        DB::statement("ALTER TABLE `back_office_task_outputs` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

        if (Schema::hasColumn('back_office_task_outputs', 'title')) {
            DB::statement("ALTER TABLE `back_office_task_outputs` MODIFY `title` VARCHAR(500) NOT NULL");
        }

        if (Schema::hasColumn('back_office_task_outputs', 'project_output')) {
            DB::statement("ALTER TABLE `back_office_task_outputs` MODIFY `project_output` VARCHAR(500) NULL");
        }

        if (Schema::hasColumn('back_office_task_outputs', 'journal')) {
            DB::statement("ALTER TABLE `back_office_task_outputs` MODIFY `journal` VARCHAR(500) NULL");
        }

        Schema::create('back_office_output_authors', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->id();
            $table->unsignedBigInteger('back_office_task_output_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('role', 50);
            $table->string('person_type', 50)->nullable();
            $table->string('name', 500);
            $table->timestamps();

            $table->foreign('back_office_task_output_id', 'booa_output_fk')
                ->references('id')
                ->on('back_office_task_outputs')
                ->cascadeOnDelete();
            $table->foreign('user_id', 'booa_user_fk')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->unique(
                ['back_office_task_output_id', 'role', 'name'],
                'bo_output_authors_output_role_name_unique'
            );
        });

        Schema::create('back_office_output_award_recipients', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->id();
            $table->unsignedBigInteger('back_office_task_output_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('person_type', 50)->nullable();
            $table->string('name', 500);
            $table->timestamps();

            $table->foreign('back_office_task_output_id', 'booar_output_fk')
                ->references('id')
                ->on('back_office_task_outputs')
                ->cascadeOnDelete();
            $table->foreign('user_id', 'booar_user_fk')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });

        Schema::create('back_office_output_book_authors', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->id();
            $table->unsignedBigInteger('back_office_task_output_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('person_type', 50)->nullable();
            $table->string('name', 500);
            $table->timestamps();

            $table->foreign('back_office_task_output_id', 'booba_output_fk')
                ->references('id')
                ->on('back_office_task_outputs')
                ->cascadeOnDelete();
            $table->foreign('user_id', 'booba_user_fk')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->unique(
                ['back_office_task_output_id', 'name'],
                'bo_output_book_authors_output_name_unique'
            );
        });

        Schema::create('back_office_output_conferences', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->id();
            $table->unsignedBigInteger('back_office_task_output_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('name', 500)->nullable();
            $table->string('presentation_type', 255)->nullable();
            $table->string('location', 500)->nullable();
            $table->string('presenter_person_type', 50)->nullable();
            $table->string('note', 500)->nullable();
            $table->timestamps();

            $table->foreign('back_office_task_output_id', 'booc_output_fk')
                ->references('id')
                ->on('back_office_task_outputs')
                ->cascadeOnDelete();
            $table->foreign('user_id', 'booc_user_fk')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });

        Schema::create('back_office_output_conference_presenters', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->id();
            $table->unsignedBigInteger('back_office_output_conference_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('person_type', 50)->nullable();
            $table->string('name', 500)->nullable();
            $table->timestamps();

            $table->foreign('back_office_output_conference_id', 'boocp_conf_fk')
                ->references('id')
                ->on('back_office_output_conferences')
                ->cascadeOnDelete();
            $table->foreign('user_id', 'boocp_user_fk')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });

        $lookup = $this->buildUserLookup();
        $now = now();

        if (Schema::hasColumn('back_office_entries', 'user_id') && Schema::hasColumn('back_office_entries', 'person_name')) {
            DB::table('back_office_entries')
                ->select(['id', 'person_name'])
                ->whereNull('user_id')
                ->orderBy('id')
                ->chunkById(200, function ($rows) use ($lookup, $now): void {
                    foreach ($rows as $row) {
                        $matchedUserId = $this->resolveUserIdByStoredName($lookup, (string) ($row->person_name ?? ''));
                        if (! $matchedUserId) {
                            continue;
                        }

                        DB::table('back_office_entries')
                            ->where('id', $row->id)
                            ->whereNull('user_id')
                            ->update([
                                'user_id' => $matchedUserId,
                                'updated_at' => $now,
                            ]);
                    }
                });
        }

        DB::table('back_office_task_outputs')
            ->select(['id', 'type', 'authors', 'book_authors', 'conferences', 'created_at', 'updated_at'])
            ->orderBy('id')
            ->chunkById(100, function ($rows) use ($lookup, $now): void {
                foreach ($rows as $row) {
                    $outputId = (int) $row->id;
                    $createdAt = $row->created_at ?? $now;
                    $updatedAt = $row->updated_at ?? $now;

                    $authors = json_decode((string) ($row->authors ?? ''), true);
                    if (is_array($authors)) {
                        foreach ($authors as $author) {
                            if (! is_array($author)) {
                                continue;
                            }

                            $name = trim((string) ($author['name'] ?? ''));
                            if ($name === '') {
                                continue;
                            }

                            $personType = trim((string) ($author['person_type'] ?? ''));
                            $userId = $this->resolveUserIdByStoredName($lookup, $name);
                            $role = trim((string) ($author['role'] ?? ''));

                            if ($role === 'awardee') {
                                DB::table('back_office_output_award_recipients')->insert([
                                    'back_office_task_output_id' => $outputId,
                                    'user_id' => $userId,
                                    'person_type' => $personType !== '' ? $personType : null,
                                    'name' => $name,
                                    'created_at' => $createdAt,
                                    'updated_at' => $updatedAt,
                                ]);
                                continue;
                            }

                            DB::table('back_office_output_authors')->insert([
                                'back_office_task_output_id' => $outputId,
                                'user_id' => $userId,
                                'role' => $role !== '' ? $role : 'co',
                                'person_type' => $personType !== '' ? $personType : null,
                                'name' => $name,
                                'created_at' => $createdAt,
                                'updated_at' => $updatedAt,
                            ]);
                        }
                    }

                    $bookAuthors = json_decode((string) ($row->book_authors ?? ''), true);
                    if (is_array($bookAuthors)) {
                        foreach ($bookAuthors as $author) {
                            if (! is_array($author)) {
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

                    $conferences = json_decode((string) ($row->conferences ?? ''), true);
                    if (! is_array($conferences)) {
                        continue;
                    }

                    foreach ($conferences as $conference) {
                        if (! is_array($conference)) {
                            continue;
                        }

                        $conferenceName = trim((string) ($conference['name'] ?? ''));
                        $presentationType = trim((string) ($conference['presentation_type'] ?? ''));
                        $location = trim((string) ($conference['location'] ?? ''));
                        $note = trim((string) ($conference['note'] ?? ''));
                        $presenterPersonType = trim((string) ($conference['presenter_person_type'] ?? ''));

                        $conferenceId = DB::table('back_office_output_conferences')->insertGetId([
                            'back_office_task_output_id' => $outputId,
                            'user_id' => null,
                            'name' => $conferenceName !== '' ? $conferenceName : null,
                            'presentation_type' => $presentationType !== '' ? $presentationType : null,
                            'location' => $location !== '' ? $location : null,
                            'presenter_person_type' => $presenterPersonType !== '' ? $presenterPersonType : null,
                            'note' => $note !== '' ? $note : null,
                            'created_at' => $createdAt,
                            'updated_at' => $updatedAt,
                        ]);

                        $presenters = is_array($conference['presenters'] ?? null) ? $conference['presenters'] : [];
                        if ($presenters === [] && ($note !== '' || $presenterPersonType !== '')) {
                            $presenters[] = [
                                'name' => $note,
                                'person_type' => $presenterPersonType,
                            ];
                        }

                        foreach ($presenters as $presenter) {
                            if (! is_array($presenter)) {
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
    }

    public function down(): void
    {
        Schema::dropIfExists('back_office_output_conference_presenters');
        Schema::dropIfExists('back_office_output_conferences');
        Schema::dropIfExists('back_office_output_book_authors');
        Schema::dropIfExists('back_office_output_award_recipients');
        Schema::dropIfExists('back_office_output_authors');

        if (Schema::hasTable('back_office_task_outputs')) {
            if (Schema::hasColumn('back_office_task_outputs', 'title')) {
                DB::statement("ALTER TABLE `back_office_task_outputs` MODIFY `title` VARCHAR(255) NOT NULL");
            }

            if (Schema::hasColumn('back_office_task_outputs', 'project_output')) {
                DB::statement("ALTER TABLE `back_office_task_outputs` MODIFY `project_output` VARCHAR(255) NULL");
            }

            if (Schema::hasColumn('back_office_task_outputs', 'journal')) {
                DB::statement("ALTER TABLE `back_office_task_outputs` MODIFY `journal` VARCHAR(255) NULL");
            }
        }
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

    private function normalizeNameKey(string $value): string
    {
        $value = preg_replace('/\s+/u', ' ', trim($value)) ?? '';
        return $value !== '' ? mb_strtolower($value) : '';
    }
};
