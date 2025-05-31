<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // public function up(): void
    // {
    //     Schema::table('users', function (Blueprint $table) {
    //         if (!Schema::hasColumn('users', 'prefix')) {
    //             $table->string('prefix')->nullable()->after('id');
    //         }

    //         if (!Schema::hasColumn('users', 'position')) {
    //             $table->string('position')->nullable()->after('name');
    //         }

    //         if (!Schema::hasColumn('users', 'role')) {
    //             $table->enum('role', ['user', 'admin'])->default('user')->after('position');
    //         }

    //         if (!Schema::hasColumn('users', 'profile_picture')) {
    //             $table->string('profile_picture')->nullable()->after('profile_photo_path');
    //         }
    //     });
    // }

    // public function down(): void
    // {
    //     Schema::table('users', function (Blueprint $table) {
    //         $table->dropColumn(['prefix', 'position', 'role', 'profile_picture']);
    //     });
    }
};
