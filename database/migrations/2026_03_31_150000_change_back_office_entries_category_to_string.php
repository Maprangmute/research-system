<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('back_office_entries') || !Schema::hasColumn('back_office_entries', 'category')) {
            return;
        }

        DB::statement("ALTER TABLE back_office_entries MODIFY category VARCHAR(255) NOT NULL");
    }

    public function down(): void
    {
        if (!Schema::hasTable('back_office_entries') || !Schema::hasColumn('back_office_entries', 'category')) {
            return;
        }

        DB::statement(<<<'SQL'
            ALTER TABLE back_office_entries
            MODIFY category ENUM(
                'งานบริการด้านเอกสารงานวิจัย',
                'งานบริการด้านการขอคำปรึกษางานวิจัย',
                'งานบริการหน่วยงาน',
                'งานบริการด้านทรัพยากรบุคคล',
                'งานจัดประชุมที่ไม่เกี่ยวข้องกับงานวิจัย',
                'งานบริการขนส่งสินค้าเเละเอกสาร',
                'งานด้านการออกแบบ เเละ สื่อสิ่งพิมพ์',
                'งานด้านการสื่อสาร',
                'งานด้านการพัฒนาเทคโนโลยีเเละสารสนเทศ',
                'งานด้านการออกแบบเเละผลิตสื่อวิดิทัศน์',
                'การจัดงานหรือเข้าร่วมงานนิทรรศการ',
                'การออกแบบหลักสูตร เเละ การเรียนการสอน',
                'event',
                'work',
                'consult',
                'other'
            ) NOT NULL
        SQL);
    }
};
