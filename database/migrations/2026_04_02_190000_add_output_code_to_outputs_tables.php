<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('outputs', function (Blueprint $table) {
            $table->string('output_code', 32)->nullable()->unique()->after('project_id');
        });

        Schema::table('external_outputs', function (Blueprint $table) {
            $table->string('output_code', 32)->nullable()->unique()->after('id');
        });

        DB::table('outputs')
            ->select('id')
            ->orderBy('id')
            ->chunkById(200, function ($rows) {
                foreach ($rows as $row) {
                    DB::table('outputs')
                        ->where('id', $row->id)
                        ->update([
                            'output_code' => 'pv_' . str_pad((string) $row->id, 4, '0', STR_PAD_LEFT),
                        ]);
                }
            });

        DB::table('external_outputs')
            ->select('id')
            ->orderBy('id')
            ->chunkById(200, function ($rows) {
                foreach ($rows as $row) {
                    DB::table('external_outputs')
                        ->where('id', $row->id)
                        ->update([
                            'output_code' => 'px_' . str_pad((string) $row->id, 4, '0', STR_PAD_LEFT),
                        ]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('external_outputs', function (Blueprint $table) {
            $table->dropUnique(['output_code']);
            $table->dropColumn('output_code');
        });

        Schema::table('outputs', function (Blueprint $table) {
            $table->dropUnique(['output_code']);
            $table->dropColumn('output_code');
        });
    }
};
