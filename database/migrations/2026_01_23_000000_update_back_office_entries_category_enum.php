<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
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
            )
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE back_office_entries
            MODIFY category ENUM('event', 'work', 'consult', 'other')
        ");
    }
};
