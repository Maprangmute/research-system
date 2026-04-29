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

        if (! Schema::hasTable('back_office_output_contributors')) {
            Schema::create('back_office_output_contributors', function (Blueprint $table) {
                $table->charset = 'utf8mb4';
                $table->collation = 'utf8mb4_unicode_ci';
                $table->id();
                $table->unsignedBigInteger('back_office_task_output_id');
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('name', 500)->nullable();
                $table->timestamps();

                $table->foreign('back_office_task_output_id', 'bo_output_contrib_output_fk')
                    ->references('id')
                    ->on('back_office_task_outputs')
                    ->cascadeOnDelete();
                $table->foreign('user_id', 'bo_output_contrib_user_fk')
                    ->references('id')
                    ->on('users')
                    ->nullOnDelete();

                $table->unique(
                    ['back_office_task_output_id', 'user_id'],
                    'bo_output_contributors_output_user_unique'
                );
            });
        }

        $this->backfillContributors();
    }

    public function down(): void
    {
        Schema::dropIfExists('back_office_output_contributors');
    }

    private function backfillContributors(): void
    {
        if (
            ! Schema::hasTable('back_office_output_contributors') ||
            ! Schema::hasColumn('back_office_task_outputs', 'id')
        ) {
            return;
        }

        $userNames = User::query()
            ->select(['id', 'prefix', 'name'])
            ->get()
            ->mapWithKeys(function (User $user): array {
                return [(int) $user->id => trim((string) $user->full_name)];
            });

        $hasContributors = Schema::hasColumn('back_office_task_outputs', 'contributors');
        $hasOwnerUserId = Schema::hasColumn('back_office_task_outputs', 'owner_user_id');
        $hasOwnerName = Schema::hasColumn('back_office_task_outputs', 'owner_name');

        DB::table('back_office_task_outputs')
            ->select([
                'id',
                ...($hasContributors ? ['contributors'] : []),
                ...($hasOwnerUserId ? ['owner_user_id'] : []),
                ...($hasOwnerName ? ['owner_name'] : []),
            ])
            ->orderBy('id')
            ->chunkById(200, function ($outputs) use ($userNames, $hasContributors, $hasOwnerUserId, $hasOwnerName): void {
                foreach ($outputs as $output) {
                    $rows = [];
                    $seenUserIds = [];

                    if ($hasContributors && ! empty($output->contributors)) {
                        $decoded = json_decode((string) $output->contributors, true);
                        if (is_array($decoded)) {
                            foreach ($decoded as $item) {
                                if (! is_array($item)) {
                                    continue;
                                }

                                $userId = ! empty($item['user_id']) ? (int) $item['user_id'] : null;
                                $name = trim((string) ($item['name'] ?? ''));

                                if ($userId && $userNames->has($userId)) {
                                    if (isset($seenUserIds[$userId])) {
                                        continue;
                                    }
                                    $seenUserIds[$userId] = true;
                                    $name = trim((string) $userNames->get($userId)) ?: $name;
                                }

                                if (! $userId && $name === '') {
                                    continue;
                                }

                                $rows[] = [
                                    'back_office_task_output_id' => $output->id,
                                    'user_id' => $userId,
                                    'name' => $name !== '' ? $name : null,
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ];
                            }
                        }
                    }

                    if ($rows === [] && ($hasOwnerUserId || $hasOwnerName)) {
                        $ownerUserId = $hasOwnerUserId && ! empty($output->owner_user_id) ? (int) $output->owner_user_id : null;
                        $ownerName = $hasOwnerName ? trim((string) ($output->owner_name ?? '')) : '';

                        if ($ownerUserId && $userNames->has($ownerUserId)) {
                            $ownerName = trim((string) $userNames->get($ownerUserId)) ?: $ownerName;
                        }

                        if ($ownerUserId || $ownerName !== '') {
                            $rows[] = [
                                'back_office_task_output_id' => $output->id,
                                'user_id' => $ownerUserId,
                                'name' => $ownerName !== '' ? $ownerName : null,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];
                        }
                    }

                    if ($rows !== []) {
                        DB::table('back_office_output_contributors')->insertOrIgnore($rows);
                    }
                }
            });
    }
};
