<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (
            !Schema::hasTable('copis') ||
            !Schema::hasTable('users') ||
            !Schema::hasColumn('copis', 'user_id') ||
            !Schema::hasColumn('copis', 'name') ||
            !Schema::hasColumn('users', 'id') ||
            !Schema::hasColumn('users', 'name')
        ) {
            return;
        }

        $hasUserPrefix = Schema::hasColumn('users', 'prefix');
        $hasUpdatedAt = Schema::hasColumn('copis', 'updated_at');

        $nameToUserId = [];

        DB::table('users')
            ->select(array_filter([
                'id',
                'name',
                $hasUserPrefix ? 'prefix' : null,
            ]))
            ->orderBy('id')
            ->get()
            ->each(function ($user) use (&$nameToUserId, $hasUserPrefix): void {
                $keys = array_filter([
                    trim(($hasUserPrefix ? ($user->prefix ?? '') : '').' '.($user->name ?? '')),
                    trim((string) ($user->name ?? '')),
                ]);

                foreach ($keys as $key) {
                    if (!array_key_exists($key, $nameToUserId)) {
                        $nameToUserId[$key] = (int) $user->id;
                        continue;
                    }

                    if ($nameToUserId[$key] !== (int) $user->id) {
                        $nameToUserId[$key] = null;
                    }
                }
            });

        DB::table('copis')
            ->whereNull('user_id')
            ->whereNotNull('name')
            ->orderBy('id')
            ->chunkById(200, function ($rows) use ($nameToUserId, $hasUpdatedAt): void {
                foreach ($rows as $copi) {
                    $name = trim((string) ($copi->name ?? ''));
                    $userId = $nameToUserId[$name] ?? null;

                    if (!$userId) {
                        continue;
                    }

                    $payload = ['user_id' => $userId];
                    if ($hasUpdatedAt) {
                        $payload['updated_at'] = now();
                    }

                    DB::table('copis')
                        ->where('id', $copi->id)
                        ->whereNull('user_id')
                        ->update($payload);
                }
            });
    }

    public function down(): void
    {
        // Intentionally left blank to avoid clearing valid linked user IDs.
    }
};
