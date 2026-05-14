<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'is_borrowed')) {
                $table->boolean('is_borrowed')->default(false)->after('skill_role');
            }
            if (! Schema::hasColumn('users', 'borrowed_department')) {
                $table->string('borrowed_department')->nullable()->after('is_borrowed');
            }
        });

        $borrowedUsers = [
            'ปทุมพร' => 'ภาควิชาอายุรศาสตร์ สาขาวิชาอายุรศาสตร์ปัจฉิมวัย',
            'ศตนันท์' => 'ฝ่ายเภสัชกรรม หน่วยพัฒนาคุณภาพ',
            'ศตนันทน์' => 'ฝ่ายเภสัชกรรม หน่วยพัฒนาคุณภาพ',
            'นริสา' => 'ฝ่ายเภสัชกรรม หน่วยพัฒนาคุณภาพ',
            'เอลียา' => 'ฝ่ายเภสัชกรรม งานบริหารเภสัชภัณฑ์',
            'ธนวรรณ' => 'ภาควิชาอายุรศาสตร์ สาขาวิชาต่อมไร้ท่อและเมตะบอลิสม',
        ];

        foreach ($borrowedUsers as $name => $department) {
            DB::table('users')
                ->where('name', 'like', '%' . $name . '%')
                ->update([
                    'is_borrowed' => true,
                    'borrowed_department' => $department,
                    'updated_at' => now(),
                ]);
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'borrowed_department')) {
                $table->dropColumn('borrowed_department');
            }
            if (Schema::hasColumn('users', 'is_borrowed')) {
                $table->dropColumn('is_borrowed');
            }
        });
    }
};
