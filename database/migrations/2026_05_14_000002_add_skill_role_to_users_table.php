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
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'skill_role')) {
                $table->string('skill_role')->nullable()->after('position');
            }
        });

        DB::table('users')
            ->select(['id', 'name', 'position', 'skill_role'])
            ->orderBy('id')
            ->chunkById(200, function ($users): void {
                foreach ($users as $user) {
                    if (trim((string) ($user->skill_role ?? '')) !== '') {
                        continue;
                    }

                    DB::table('users')
                        ->where('id', $user->id)
                        ->update([
                            'skill_role' => User::inferSkillRoleFromPosition($user->position ?? null, $user->name ?? null),
                            'updated_at' => now(),
                        ]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'skill_role')) {
                $table->dropColumn('skill_role');
            }
        });
    }
};
