<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('outputs', function (Blueprint $table) {
            // เพิ่มคอลัมน์ใหม่ (ถ้ายังไม่มี)
            if (!Schema::hasColumn('outputs', 'journal')) {
                $table->string('journal')->nullable()->after('type');
            }
            if (!Schema::hasColumn('outputs', 'quartile')) {
                // ใช้ enum Q1-Q4; ถ้า DB ไม่รองรับ enum ให้เปลี่ยนเป็น string + CHECK ได้
                $table->enum('quartile', ['Q1','Q2','Q3','Q4'])->nullable()->after('journal');
            }
            if (!Schema::hasColumn('outputs', 'impact_factor')) {
                // สูงสุด 999.999 พอสำหรับ IF
                $table->decimal('impact_factor', 5, 3)->nullable()->after('quartile');
            }
            if (!Schema::hasColumn('outputs', 'authors')) {
                // JSON: [{role: 'first|co|corresponding', name: '...'}, ...]
                // บน MariaDB JSON จะเป็น LONGTEXT อัตโนมัติ ใช้งานได้ปกติ
                $table->json('authors')->nullable()->after('impact_factor');
            }

            // ลบคอลัมน์เก่า (ถ้ามี)
            if (Schema::hasColumn('outputs', 'paper_title')) {
                $table->dropColumn('paper_title');
            }
        });
    }

    public function down(): void
    {
        Schema::table('outputs', function (Blueprint $table) {
            // rollback: ลบคอลัมน์ที่เพิ่ม
            if (Schema::hasColumn('outputs', 'authors')) {
                $table->dropColumn('authors');
            }
            if (Schema::hasColumn('outputs', 'impact_factor')) {
                $table->dropColumn('impact_factor');
            }
            if (Schema::hasColumn('outputs', 'quartile')) {
                $table->dropColumn('quartile');
            }
            if (Schema::hasColumn('outputs', 'journal')) {
                $table->dropColumn('journal');
            }

            // (ไม่จำเป็นต้องเพิ่ม paper_title กลับ; ถ้าต้องการค่อยเพิ่มเอง)
            // $table->string('paper_title')->nullable();
        });
    }
};
