<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const INTERNAL_UNIT_STATUS = 'ภายในหน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ';
    private const INTERNAL_AFFILIATION = 'หน่วยวิจัยเพื่อขับเคลื่อนนโยบายสุขภาพ คณะแพทยศาสตร์ศิริราชพยาบาล มหาวิทยาลัยมหิดล';

    public function up(): void
    {
        Schema::table('co_research', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('project_id')->constrained('users')->nullOnDelete();
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

        DB::table('co_research')
            ->select(['id', 'name', 'unit_status'])
            ->orderBy('id')
            ->chunkById(200, function ($rows) use ($users): void {
                foreach ($rows as $row) {
                    $name = trim((string) ($row->name ?? ''));
                    $unitStatus = trim((string) ($row->unit_status ?? ''));
                    $matchedUserId = $users->get($name);

                    if (! $matchedUserId) {
                        continue;
                    }

                    $payload = ['user_id' => $matchedUserId];

                    if ($unitStatus === self::INTERNAL_UNIT_STATUS) {
                        $payload['affiliation'] = self::INTERNAL_AFFILIATION;
                    }

                    DB::table('co_research')->where('id', $row->id)->update($payload);
                }
            });
    }

    public function down(): void
    {
        Schema::table('co_research', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
        });
    }
};
