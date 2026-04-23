<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('output_award_recipients', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->id();
            $table->foreignId('output_id')->constrained('outputs')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->timestamps();
        });

        if (! Schema::hasTable('output_authors')) {
            return;
        }

        $hasUpdatedAt = Schema::hasColumn('output_authors', 'updated_at');
        $now = now();

        DB::table('output_authors')
            ->select(['id', 'output_id', 'user_id', 'name'])
            ->where('role', 'awardee')
            ->orderBy('id')
            ->chunkById(200, function ($rows) use ($hasUpdatedAt, $now): void {
                foreach ($rows as $row) {
                    $exists = DB::table('output_award_recipients')
                        ->where('output_id', $row->output_id)
                        ->whereRaw('TRIM(name) = ?', [trim((string) $row->name)])
                        ->exists();

                    if (! $exists) {
                        $payload = [
                            'output_id' => $row->output_id,
                            'user_id' => $row->user_id,
                            'name' => trim((string) $row->name),
                            'created_at' => $now,
                        ];

                        if ($hasUpdatedAt) {
                            $payload['updated_at'] = $now;
                        }

                        DB::table('output_award_recipients')->insert($payload);
                    }

                    DB::table('output_authors')->where('id', $row->id)->delete();
                }
            });
    }

    public function down(): void
    {
        if (Schema::hasTable('output_award_recipients') && Schema::hasTable('output_authors')) {
            $hasUpdatedAt = Schema::hasColumn('output_authors', 'updated_at');
            $now = now();

            DB::table('output_award_recipients')
                ->select(['id', 'output_id', 'user_id', 'name'])
                ->orderBy('id')
                ->chunkById(200, function ($rows) use ($hasUpdatedAt, $now): void {
                    foreach ($rows as $row) {
                        $exists = DB::table('output_authors')
                            ->where('output_id', $row->output_id)
                            ->where('role', 'awardee')
                            ->whereRaw('TRIM(name) = ?', [trim((string) $row->name)])
                            ->exists();

                        if ($exists) {
                            continue;
                        }

                        $payload = [
                            'output_id' => $row->output_id,
                            'user_id' => $row->user_id,
                            'role' => 'awardee',
                            'name' => trim((string) $row->name),
                            'created_at' => $now,
                        ];

                        if ($hasUpdatedAt) {
                            $payload['updated_at'] = $now;
                        }

                        DB::table('output_authors')->insert($payload);
                    }
                });
        }

        Schema::dropIfExists('output_award_recipients');
    }
};
