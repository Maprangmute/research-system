<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('output_conference_presenters', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->id();
            $table->foreignId('output_conference_id')
                ->constrained('output_conferences')
                ->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name')->nullable();
            $table->timestamps();
        });

        $rows = DB::table('output_conferences')
            ->select(['id', 'output_id', 'name', 'presentation_type', 'location', 'note', 'user_id', 'created_at', 'updated_at'])
            ->orderBy('output_id')
            ->orderBy('id')
            ->get()
            ->groupBy(function ($row) {
                return implode('|', [
                    (int) $row->output_id,
                    trim((string) ($row->name ?? '')),
                    trim((string) ($row->presentation_type ?? '')),
                    trim((string) ($row->location ?? '')),
                ]);
            });

        foreach ($rows as $group) {
            $canonical = $group->sortBy('id')->first();
            if (! $canonical) {
                continue;
            }

            $presenterKeys = [];
            foreach ($group as $row) {
                $presenterName = trim((string) ($row->note ?? ''));
                $userId = $row->user_id ? (int) $row->user_id : null;
                if ($presenterName === '' && ! $userId) {
                    continue;
                }

                $presenterKey = implode('|', [$userId ?: 0, mb_strtolower($presenterName)]);
                if (isset($presenterKeys[$presenterKey])) {
                    continue;
                }
                $presenterKeys[$presenterKey] = true;

                DB::table('output_conference_presenters')->insert([
                    'output_conference_id' => $canonical->id,
                    'user_id' => $userId,
                    'name' => $presenterName !== '' ? $presenterName : null,
                    'created_at' => $row->created_at ?? now(),
                    'updated_at' => $row->updated_at ?? now(),
                ]);
            }

            $duplicateIds = $group->pluck('id')->filter(fn ($id) => (int) $id !== (int) $canonical->id)->values();
            if ($duplicateIds->isNotEmpty()) {
                DB::table('output_conferences')->whereIn('id', $duplicateIds->all())->delete();
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('output_conference_presenters')) {
            $presenters = DB::table('output_conference_presenters')
                ->select(['id', 'output_conference_id', 'user_id', 'name', 'created_at', 'updated_at'])
                ->orderBy('output_conference_id')
                ->orderBy('id')
                ->get()
                ->groupBy('output_conference_id');

            foreach ($presenters as $conferenceId => $rows) {
                $rows = collect($rows)->values();
                if ($rows->count() <= 1) {
                    $single = $rows->first();
                    if ($single) {
                        DB::table('output_conferences')->where('id', $conferenceId)->update([
                            'user_id' => $single->user_id,
                            'note' => $single->name,
                            'updated_at' => $single->updated_at ?? now(),
                        ]);
                    }
                    continue;
                }

                $baseConference = DB::table('output_conferences')->where('id', $conferenceId)->first();
                if (! $baseConference) {
                    continue;
                }

                foreach ($rows->slice(1) as $row) {
                    DB::table('output_conferences')->insert([
                        'output_id' => $baseConference->output_id,
                        'name' => $baseConference->name,
                        'presentation_type' => $baseConference->presentation_type,
                        'location' => $baseConference->location,
                        'note' => $row->name,
                        'user_id' => $row->user_id,
                        'created_at' => $row->created_at ?? now(),
                        'updated_at' => $row->updated_at ?? now(),
                    ]);
                }

                $first = $rows->first();
                DB::table('output_conferences')->where('id', $conferenceId)->update([
                    'user_id' => $first->user_id,
                    'note' => $first->name,
                    'updated_at' => $first->updated_at ?? now(),
                ]);
            }
        }

        Schema::dropIfExists('output_conference_presenters');
    }
};
