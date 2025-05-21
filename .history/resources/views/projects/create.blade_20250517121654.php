@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto mt-6 bg-white shadow-md rounded p-6">
    <h2 class="text-xl font-bold mb-6 text-gray-800">📋 เพิ่มข้อมูลโครงการวิจัย</h2>

    @if(session('success'))
        <div class="mb-4 p-3 bg-green-100 text-green-700 rounded">
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('projects.store') }}" method="POST">
        @csrf

        <!-- A: Project Overview -->
        <h3 class="font-semibold text-lg text-blue-700 mb-2">A: ข้อมูลโครงการ</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <input name="project_name_th" type="text" placeholder="ชื่อโครงการ (ไทย)*" required class="w-full border p-2 rounded">
            <input name="project_name_en" type="text" placeholder="ชื่อโครงการ (อังกฤษ)" class="w-full border p-2 rounded">
            <input name="project_type" type="text" placeholder="ประเภทของโครงการ*" required class="w-full border p-2 rounded">
            <input name="theme" type="text" placeholder="กลุ่มเนื้อหา (Theme)*" required class="w-full border p-2 rounded">
            <input name="shortcut_link" type="text" placeholder="ลิงก์ Google Drive / Shortcut" class="w-full border p-2 rounded">
            <input name="io_code" type="text" placeholder="รหัสโครงการ IO (AA603.1)" class="w-full border p-2 rounded">
            <input name="course_code" type="text" placeholder="รหัสรายวิชา (AA605.1)" class="w-full border p-2 rounded">
        </div>

        <!-- B: Timeline -->
        <h3 class="font-semibold text-lg text-blue-700 mb-2">B: ระยะเวลาดำเนินโครงการ</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
            <input name="planning_status" type="text" placeholder="สถานะเริ่มต้น (BA000)" class="w-full border p-2 rounded">
            <input name="proposal_date" type="text" placeholder="เริ่มเขียน Proposal (BA001)" class="w-full border p-2 rounded">
            <input name="execution_status" type="text" placeholder="สถานะดำเนินโครงการ (BC000)" class="w-full border p-2 rounded">
            <input name="contract_date" type="text" placeholder="วันที่สัญญาอนุมัติ (BC101)" class="w-full border p-2 rounded">
            <input name="start_date" type="text" placeholder="วันที่เริ่มโครงการ (BC102)" class="w-full border p-2 rounded">
            <input name="start_month" type="text" placeholder="เดือนเริ่มต้น*" required class="w-full border p-2 rounded">
            <input name="start_year" type="text" placeholder="ปีเริ่มต้น (พ.ศ.)*" required class="w-full border p-2 rounded">
            <input name="end_month" type="text" placeholder="เดือนสิ้นสุด" class="w-full border p-2 rounded">
            <input name="end_year" type="text" placeholder="ปีสิ้นสุด (พ.ศ.)" class="w-full border p-2 rounded">
            <input name="review_status" type="text" placeholder="สถานะ Review (BD000)" class="w-full border p-2 rounded">
            <input name="closure_status" type="text" placeholder="สถานะปิดโครงการ (BE000)" class="w-full border p-2 rounded">
        </div>

        <!-- C: Funding -->
        <h3 class="font-semibold text-lg text-blue-700 mb-2">C: ข้อมูลทุน</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <input name="funding_type" type="text" placeholder="ประเภทแหล่งทุน (DA102)" class="w-full border p-2 rounded">
            <input name="funding_source" type="text" placeholder="ชื่อแหล่งทุน (DA103)" class="w-full border p-2 rounded">
            <input name="funding_org_type" type="text" placeholder="ประเภทองค์กร (DA104)" class="w-full border p-2 rounded">
            <input name="funding_name" type="text" placeholder="ชื่อทุน (DA105)" class="w-full border p-2 rounded">
            <input name="budget" type="number" step="0.01" placeholder="งบประมาณ (บาท) (DA106)" class="w-full border p-2 rounded">
            <input name="size" type="text" placeholder="ขนาดโครงการ (S/M/L)" class="w-full border p-2 rounded">
        </div>

        <!-- รหัสโครงการ R -->
        <h3 class="font-semibold text-lg text-blue-700 mb-2">รหัสโครงการ R</h3>
        <div id="r-code-container" class="space-y-2 mb-4">
            <input name="r_codes[]" type="text" placeholder="R:001" class="w-full border p-2 rounded">
        </div>
        <button type="button" onclick="addRCode()" class="mb-6 px-3 py-1 bg-purple-600 text-white rounded">+ เพิ่มรหัส R</button>

        <!-- รหัส IRB -->
        <h3 class="font-semibold text-lg text-blue-700 mb-2">รหัส IRB</h3>
        <div id="irb-code-container" class="space-y-2 mb-4">
            <input name="irb_codes[]" type="text" placeholder="EC2025" class="w-full border p-2 rounded">
        </div>
        <button type="button" onclick="addIRBCode()" class="mb-6 px-3 py-1 bg-red-600 text-white rounded">+ เพิ่มรหัส IRB</button>

        <!-- ยุทธศาสตร์ -->
<h3 class="font-semibold text-lg text-blue-700 mb-2">ยุทธศาสตร์ Siriraj Health Policy</h3>
<table class="w-full table-auto text-sm mb-6">
    <thead>
        <tr class="bg-gray-100">
            <th class="px-3 py-2 text-left">หมวด</th>
            <th class="px-3 py-2 text-left">รายการ</th>
            <th class="px-3 py-2">มี</th>
        </tr>
    </thead>
    <tbody>
        @php
            $strategies = [
                '1' => [
                    'label' => 'AA501: Capacity Building',
                    'items' => [
                        '1' => '1.1 การสรรหานักวิจัยด้านนโยบายสุขภาพที่มีความสามารถ',
                        '2' => '1.2 การพัฒนาศักยภาพฯ ผ่านการเรียนรู้จากการปฏิบัติงานจริง',
                        '3' => '1.3 การพัฒนาศักยภาพฯ ผ่านการศึกษาต่อเนื่อง',
                        '4' => '1.4 การบูรณาการกับการเรียนการสอนนักศึกษาแพทย์',
                        '5' => '1.5 การพัฒนาศักยภาพฯ ให้แก่บุคลากรศิริราช',
                    ]
                ],
                '2' => [
                    'label' => 'AA502: Excellence Research',
                    'items' => [
                        '1' => '2.1 การเพิ่มบทบาทของหน่วยวิจัยฯ',
                        '2' => '2.2 การสร้างความสัมพันธ์กับผู้กำหนดนโยบาย',
                        '3' => '2.3 การสรรหาและกำหนดหัวข้อวิจัย',
                    ]
                ],
                '3' => [
                    'label' => 'AA503: Health Policy Linkage',
                    'items' => [
                        '1' => '3.1 การสังเคราะห์องค์ความรู้ฯ จากงานวิจัยต้นแบบ',
                        '2' => '3.2 การดำเนินงานวิจัยที่มีคุณภาพ',
                        '3' => '3.3 ผลักดันให้งานวิจัยใช้ตัดสินใจนโยบาย',
                        '4' => '3.4 เชื่อมโยงนวัตกรรมกับการพัฒนานโยบาย',
                        '5' => '3.5 การสื่อสารงานวิจัยต่อสาธารณะ',
                    ]
                ],
                '4' => [
                    'label' => 'AA504: Partnership Engagement',
                    'items' => [
                        '1' => '4.1 การสร้างเครือข่ายงานวิจัย',
                        '2' => '4.2 การสร้างความร่วมมือฯ',
                    ]
                ],
                '5' => [
                    'label' => 'AA505: System Support',
                    'items' => [
                        '1' => '5.1 การพัฒนาระบบสนับสนุนการดำเนินงานวิจัย',
                        '2' => '5.2 การพัฒนาระบบสนับสนุนเส้นทางอาชีพในหน่วยวิจัย',
                    ]
                ],
            ];
        @endphp

        @foreach($strategies as $sKey => $strategy)
            <tr class="border-t">
                <td class="px-3 py-2 font-bold" rowspan="{{ count($strategy['items']) + 1 }}">{{ $strategy['label'] }}</td>
            </tr>
            @foreach($strategy['items'] as $iKey => $desc)
                <tr class="border-t">
                    <td class="px-3 py-2">{{ $desc }}</td>
                    <td class="px-3 py-2 text-center">
                        <input type="checkbox" name="strategy_{{ $sKey }}_{{ $iKey }}" class="mr-1">
                    </td>
                </tr>
            @endforeach
        @endforeach
    </tbody>
</table>

<!-- Submit -->
        <div class="mt-6">
            <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded hover:bg-indigo-700">💾 บันทึกข้อมูล</button>
        </div>
    </form>
</div>

<script>
let rCodeIndex = 1;
function addRCode() {
    const container = document.getElementById('r-code-container');
    container.insertAdjacentHTML('beforeend', `
        <input name="r_codes[]" type="text" placeholder="R:xxx" class="w-full border p-2 rounded">
    `);
    rCodeIndex++;
}

let irbCodeIndex = 1;
function addIRBCode() {
    const container = document.getElementById('irb-code-container');
    container.insertAdjacentHTML('beforeend', `
        <input name="irb_codes[]" type="text" placeholder="ECxxxx" class="w-full border p-2 rounded">
    `);
    irbCodeIndex++;
}
</script>
@endsection
