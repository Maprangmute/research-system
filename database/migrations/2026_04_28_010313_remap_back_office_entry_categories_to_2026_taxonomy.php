<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('back_office_entries') || !Schema::hasColumn('back_office_entries', 'category')) {
            return;
        }

        foreach ($this->categoryMap() as $from => $to) {
            DB::table('back_office_entries')
                ->where('category', $from)
                ->update(['category' => $to]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('back_office_entries') || !Schema::hasColumn('back_office_entries', 'category')) {
            return;
        }

        foreach ($this->categoryMap() as $from => $to) {
            DB::table('back_office_entries')
                ->where('category', $to)
                ->update(['category' => $from]);
        }
    }

    private function categoryMap(): array
    {
        return [
            'งานบริการหน่วยงาน' => 'งานบริการ สนับสนุน และประสานงานภายในหน่วย',
            'งานบริการด้านเอกสารงานวิจัย' => 'งานบริการด้านเอกสาร',
            'งานบริการด้านการขอคำปรึกษางานวิจัย' => 'งานบริการด้านการให้คำปรึกษางานวิจัย',
            'งานบริการขนส่งสินค้าเเละเอกสาร' => 'งานบริการส่งสินค้าและเอกสาร',
            'งานด้านออกแบบ เเละผลิตสื่อ' => 'งานด้านออกแบบ และผลิตสื่อ',
            'งานด้านการออกแบบ เเละ สื่อสิ่งพิมพ์' => 'งานด้านออกแบบ และผลิตสื่อ',
            'งานด้านการออกแบบเเละผลิตสื่อวิดิทัศน์' => 'งานด้านออกแบบ และผลิตสื่อ',
            'งานด้านการสื่อสาร' => 'งานด้านการสื่อสาร และประชาสัมพันธ์',
            'งานด้านการพัฒนาเทคโนโลยีเเละสารสนเทศ' => 'งานด้านการพัฒนาเทคโนโลยีและสารสนเทศ',
            'การจัดงานหรือเข้าร่วมงานนิทรรศการ' => 'งานจัดงานหรือเข้าร่วมงานนิทรรศการ',
            'การออกแบบหลักสูตร เเละ การเรียนการสอน' => 'งานออกแบบหลักสูตรและการเรียนการสอน',
            'event' => 'งานจัดงานหรือเข้าร่วมงานนิทรรศการ',
            'work' => 'งานบริการ สนับสนุน และประสานงานภายในหน่วย',
            'consult' => 'งานบริการด้านการให้คำปรึกษางานวิจัย',
            'other' => 'งานบริการ สนับสนุน และประสานงานภายในหน่วย',
        ];
    }
};
