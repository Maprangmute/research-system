<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->foreignId('pi_user_id')->nullable()->after('pi_name')->constrained('users')->nullOnDelete();
        });

        $users = DB::table('users')
            ->select(['id', 'prefix', 'name'])
            ->get()
            ->mapWithKeys(function ($user) {
                $fullName = trim(((string) ($user->prefix ?? '')) . ' ' . ((string) ($user->name ?? '')));

                return $fullName !== '' ? [$fullName => (int) $user->id] : [];
            });

        if ($users->isEmpty()) {
            return;
        }

        DB::table('projects')
            ->select(['id', 'pi_name'])
            ->whereNotNull('pi_name')
            ->whereRaw("TRIM(pi_name) <> ''")
            ->orderBy('id')
            ->chunkById(200, function ($rows) use ($users): void {
                foreach ($rows as $row) {
                    $name = trim((string) ($row->pi_name ?? ''));
                    $matchedUserId = $users->get($name);

                    if (! $matchedUserId) {
                        continue;
                    }

                    DB::table('projects')
                        ->where('id', $row->id)
                        ->update(['pi_user_id' => $matchedUserId]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropConstrainedForeignId('pi_user_id');
        });
    }
};
