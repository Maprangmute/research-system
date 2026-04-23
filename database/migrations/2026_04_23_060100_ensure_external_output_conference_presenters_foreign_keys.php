<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('external_output_conference_presenters')) {
            return;
        }

        $foreignKeys = collect(Schema::getForeignKeys('external_output_conference_presenters'));
        $existingNames = $foreignKeys->pluck('name')->filter()->values()->all();

        Schema::table('external_output_conference_presenters', function (Blueprint $table) use ($existingNames) {
            if (! in_array('ext_ocp_conf_fk', $existingNames, true)) {
                $table->foreign('external_output_conference_id', 'ext_ocp_conf_fk')
                    ->references('id')
                    ->on('external_output_conferences')
                    ->cascadeOnDelete();
            }

            if (! in_array('ext_ocp_user_fk', $existingNames, true)) {
                $table->foreign('user_id', 'ext_ocp_user_fk')
                    ->references('id')
                    ->on('users')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('external_output_conference_presenters')) {
            return;
        }

        $foreignKeys = collect(Schema::getForeignKeys('external_output_conference_presenters'));
        $existingNames = $foreignKeys->pluck('name')->filter()->values()->all();

        Schema::table('external_output_conference_presenters', function (Blueprint $table) use ($existingNames) {
            if (in_array('ext_ocp_conf_fk', $existingNames, true)) {
                $table->dropForeign('ext_ocp_conf_fk');
            }

            if (in_array('ext_ocp_user_fk', $existingNames, true)) {
                $table->dropForeign('ext_ocp_user_fk');
            }
        });
    }
};
