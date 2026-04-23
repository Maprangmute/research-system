<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('external_output_award_recipients')) {
            Schema::create('external_output_award_recipients', function (Blueprint $table) {
                $table->charset = 'utf8mb4';
                $table->collation = 'utf8mb4_unicode_ci';
                $table->id();
                $table->foreignId('external_output_id')->constrained('external_outputs')->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('name');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('external_output_authors') || ! Schema::hasTable('external_outputs')) {
            return;
        }

        $typeByOutputId = DB::table('external_outputs')->pluck('type', 'id');

        DB::table('external_output_authors')
            ->select(['id', 'external_output_id', 'user_id', 'name'])
            ->orderBy('id')
            ->chunkById(200, function ($rows) use ($typeByOutputId): void {
                foreach ($rows as $row) {
                    if (($typeByOutputId[$row->external_output_id] ?? null) !== 'EA118') {
                        continue;
                    }

                    $name = trim((string) $row->name);
                    $exists = DB::table('external_output_award_recipients')
                        ->where('external_output_id', $row->external_output_id)
                        ->whereRaw('TRIM(name) = ?', [$name])
                        ->exists();

                    if (! $exists && $name !== '') {
                        DB::table('external_output_award_recipients')->insert([
                            'external_output_id' => $row->external_output_id,
                            'user_id' => $row->user_id,
                            'name' => $name,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }

                    DB::table('external_output_authors')->where('id', $row->id)->delete();
                }
            });
    }

    public function down(): void
    {
        if (Schema::hasTable('external_output_award_recipients') && Schema::hasTable('external_output_authors')) {
            DB::table('external_output_award_recipients')
                ->select(['id', 'external_output_id', 'user_id', 'name'])
                ->orderBy('id')
                ->chunkById(200, function ($rows): void {
                    foreach ($rows as $row) {
                        $exists = DB::table('external_output_authors')
                            ->where('external_output_id', $row->external_output_id)
                            ->whereRaw('TRIM(name) = ?', [trim((string) $row->name)])
                            ->exists();

                        if (! $exists) {
                            DB::table('external_output_authors')->insert([
                                'external_output_id' => $row->external_output_id,
                                'user_id' => $row->user_id,
                                'role' => 'awardee',
                                'name' => trim((string) $row->name),
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }
                    }
                });
        }

        Schema::dropIfExists('external_output_award_recipients');
    }
};
