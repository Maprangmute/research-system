<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('external_output_conference_presenters')) {
            Schema::create('external_output_conference_presenters', function (Blueprint $table) {
                $table->charset = 'utf8mb4';
                $table->collation = 'utf8mb4_unicode_ci';

                $table->id();
                $table->unsignedBigInteger('external_output_conference_id');
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('name')->nullable();
                $table->timestamps();

                $table->foreign('external_output_conference_id', 'ext_ocp_conf_fk')
                    ->references('id')
                    ->on('external_output_conferences')
                    ->cascadeOnDelete();
                $table->foreign('user_id', 'ext_ocp_user_fk')
                    ->references('id')
                    ->on('users')
                    ->nullOnDelete();
            });
        }

        if (! Schema::hasTable('external_output_conferences')) {
            return;
        }

        $rows = DB::table('external_output_conferences')
            ->select(['id', 'user_id', 'presenter_name', 'created_at', 'updated_at'])
            ->orderBy('id')
            ->get();

        foreach ($rows as $row) {
            $name = trim((string) ($row->presenter_name ?? ''));
            $userId = $row->user_id ? (int) $row->user_id : null;

            if ($name === '' && ! $userId) {
                continue;
            }

            $exists = DB::table('external_output_conference_presenters')
                ->where('external_output_conference_id', $row->id)
                ->where(function ($query) use ($name, $userId) {
                    $query->where('name', $name);
                    if ($userId) {
                        $query->orWhere('user_id', $userId);
                    }
                })
                ->exists();

            if ($exists) {
                continue;
            }

            DB::table('external_output_conference_presenters')->insert([
                'external_output_conference_id' => $row->id,
                'user_id' => $userId,
                'name' => $name !== '' ? $name : null,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ]);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('external_output_conference_presenters') && Schema::hasTable('external_output_conferences')) {
            $presenters = DB::table('external_output_conference_presenters')
                ->select(['id', 'external_output_conference_id', 'user_id', 'name'])
                ->orderBy('id')
                ->get()
                ->groupBy('external_output_conference_id');

            foreach ($presenters as $conferenceId => $group) {
                $first = $group->first();
                DB::table('external_output_conferences')
                    ->where('id', $conferenceId)
                    ->update([
                        'user_id' => $first->user_id,
                        'presenter_name' => $first->name,
                    ]);
            }
        }

        if (Schema::hasTable('external_output_conference_presenters')) {
            Schema::table('external_output_conference_presenters', function (Blueprint $table) {
                try {
                    $table->dropForeign('ext_ocp_conf_fk');
                } catch (\Throwable $e) {
                }
                try {
                    $table->dropForeign('ext_ocp_user_fk');
                } catch (\Throwable $e) {
                }
            });
        }

        Schema::dropIfExists('external_output_conference_presenters');
    }
};
