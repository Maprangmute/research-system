@extends('layouts.app')

@if($errors->any())
    <div class="bg-red-100 text-red-800 p-3 rounded mb-4">
        <ul class="list-disc list-inside text-sm">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif


@section('content')
<!-- Flatpickr -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/th.js"></script>

<div class="max-w-5xl mx-auto mt-6 bg-white shadow-md rounded p-6">
    <h2 class="text-xl font-bold mb-6 text-gray-800">📋 เพิ่มข้อมูลโครงการวิจัย</h2>

    @if(session('success'))
        <div class="mb-4 p-3 bg-green-100 text-green-700 rounded">
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('projects.store') }}" method="POST">
        @csrf

        <hr class="my-6 border-t-2 border-gray-300">
    <!-- A: ข้อมูลโครงการ -->
    <h3 class="font-semibold text-lg text-blue-700 mb-2">A: ข้อมูลโครงการ</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
        <label>ชื่อโครงการ (ภาษาไทย)*
            <input name="project_name_th" value="{{ old('project_name_th', $project->project_name_th) }}" required class="w-full border p-2 rounded">
        </label>
        <label>ชื่อโครงการ (English)
            <input name="project_name_en" value="{{ old('project_name_en', $project->project_name_en) }}" class="w-full border p-2 rounded">
        </label>
        <label>ประเภทของโครงการ*
            <input name="project_type" value="{{ old('project_type', $project->project_type) }}" required class="w-full border p-2 rounded">
        </label>
        <label>กลุ่มเนื้อหา (Theme)*
            <input name="theme" value="{{ old('theme', $project->theme) }}" required class="w-full border p-2 rounded">
        </label>
        <label>Shortcut / Google Drive
            <input name="shortcut_link" value="{{ old('shortcut_link', $project->shortcut_link) }}" class="w-full border p-2 rounded">
        </label>
        <label>ขนาดโครงการ
            <input name="size" value="{{ old('size', $project->size) }}" class="w-full border p-2 rounded">
        </label>
    </div>

    <!-- B: ระยะเวลาดำเนินโครงการ -->
    <h3 class="font-semibold text-lg text-blue-700 mt-6 mb-2">B: ระยะเวลาดำเนินโครงการ</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
        <label>BA000 - สถานะการวางแผน
            <input name="planning_status" value="{{ old('planning_status', $project->planning_status) }}" class="w-full border p-2 rounded">
        </label>
        <label>BA001 - วันที่เริ่มเขียน Proposal
            <input name="proposal_date" value="{{ old('proposal_date', $project->proposal_date) }}" class="thai-datepicker w-full border p-2 rounded">
        </label>
        <label>BC101 - วันที่สัญญาอนุมัติ
            <input name="contract_date" value="{{ old('contract_date', $project->contract_date) }}" class="thai-datepicker w-full border p-2 rounded">
        </label>
        <label>BC102 - วันที่เริ่มโครงการ
            <input name="start_date" value="{{ old('start_date', $project->start_date) }}" class="thai-datepicker w-full border p-2 rounded">
        </label>
        <label>เดือนเริ่มต้น
            <input name="start_month" value="{{ old('start_month', $project->start_month) }}" class="w-full border p-2 rounded">
        </label>
        <label>ปีเริ่มต้น
            <input name="start_year" value="{{ old('start_year', $project->start_year) }}" class="w-full border p-2 rounded">
        </label>
        <label>เดือนสิ้นสุด
            <input name="end_month" value="{{ old('end_month', $project->end_month) }}" class="w-full border p-2 rounded">
        </label>
        <label>ปีสิ้นสุด
            <input name="end_year" value="{{ old('end_year', $project->end_year) }}" class="w-full border p-2 rounded">
        </label>
        <label>BC000 - สถานะการดำเนินโครงการ
            <input name="execution_status" value="{{ old('execution_status', $project->execution_status) }}" class="w-full border p-2 rounded">
        </label>
        <label>BD000 - สถานะการประเมิน
            <input name="review_status" value="{{ old('review_status', $project->review_status) }}" class="w-full border p-2 rounded">
        </label>
        <label>BE000 - สถานะปิดโครงการ
            <input name="closure_status" value="{{ old('closure_status', $project->closure_status) }}" class="w-full border p-2 rounded">
        </label>
    </div>



</div>


        <hr class="my-6 border-t-2 border-gray-300">
<!-- รหัสโครงการ R -->
<h3 class="font-semibold text-lg text-blue-700 mb-2">รหัสโครงการ R</h3>
<div id="r-code-container" class="space-y-2 mb-4">
    <div id="r-0" class="flex gap-2">
    <label class="w-full">
        <span class="block text-sm text-gray-700 mb-1">รหัสโครงการ R</span>
        <input name="r_codes[]" type="text" placeholder="R:001" class="w-full border p-2 rounded">
    </label>
</div>
</div>
        <button type="button" onclick="addRCode()" class="mb-6 px-3 py-1 bg-purple-600 text-white rounded">+ เพิ่มรหัส R</button>

        <hr class="my-6 border-t-2 border-gray-300">


<!-- รหัส IRB -->
<h3 class="font-semibold text-lg text-blue-700 mb-2">รหัส IRB</h3>
<div id="irb-code-container" class="space-y-2 mb-4">
    <div id="irb-0" class="grid grid-cols-2 gap-2 items-center">
        <label class="w-full">
            <span class="block text-sm text-gray-700 mb-1">รหัส IRB</span>
            <input name="irbs[0][code]" type="text" placeholder="EC2025" class="w-full border p-2 rounded">
        </label>
        <label class="w-full">
            <span class="block text-sm text-gray-700 mb-1">จาก (ชื่อหน่วยงาน)</span>
            <input name="irbs[0][source]" type="text" placeholder="ชื่อหน่วยงาน" class="w-full border p-2 rounded">
        </label>
    </div>
</div>

<button type="button" onclick="addIRBCode()" class="mb-6 px-3 py-1 bg-red-600 text-white rounded">
    + เพิ่มรหัส IRB
</button>
        <hr class="my-6 border-t-2 border-gray-300">
 <!-- C: Roles and Responsibilities -->
     <h3 class="font-semibold text-lg text-blue-700 mb-2">C: Roles and Responsibilities</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <label class="w-full">
                <span class="block text-sm text-gray-700 mb-1">CA101 - ชื่อ PI (ผู้วิจัยหลัก)</span>
                <input name="pi_name" type="text" class="w-full border p-2 rounded" value="">
                @error('pi_name') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
            </label>
            <label class="w-full">
                <span class="block text-sm text-gray-700 mb-1">CA103 - สังกัดผู้วิจัยหลัก</span>
                <input name="pi_affiliation" type="text" class="w-full border p-2 rounded" value="">
                @error('pi_affiliation') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
            </label>
        </div>
 <h3 class="text-balance text-blue-700 mb-2"> รายชื่อผู้ร่วมวิจัย (COPI)</h3>
<div id="copi-container" class="space-y-2 mb-4">
    <div id="copi-0" class="grid grid-cols-2 gap-4 items-center">
        <label class="w-full">
            <span class="block text-sm text-gray-700 mb-1">ชื่อ COPI</span>
            <input name="copis[0][name]" type="text" class="w-full border p-2 rounded">
        </label>
        <div class="flex gap-2">
            <label class="w-full">
                <span class="block text-sm text-gray-700 mb-1">สังกัด COPI</span>
                <input name="copis[0][affiliation]" type="text" class="w-full border p-2 rounded">
            </label>
            <button type="button" onclick="removeRow('copi-0')" class="text-red-600 mt-6">✕</button>
        </div>
    </div>
</div>
<button type="button" onclick="addCopiRow()" class="mb-6 px-3 py-1 bg-yellow-600 text-white rounded">+ เพิ่ม COPI</button>
        
        <hr class="my-6 border-t-2 border-gray-300">
<!-- D: รายชื่อผู้มีส่วนร่วม (FTE) -->
<h3 class="font-semibold text-lg text-blue-700 mb-2">D: รายชื่อผู้มีส่วนร่วม (FTE)</h3>
<div id="fte-container" class="space-y-2 mb-4">
    <div class="grid grid-cols-3 gap-4">
    <label class="w-full">
        <span class="block text-sm text-gray-700 mb-1">กลุ่ม (เช่น Scientific)</span>
        <input name="fte_roles[0][role_group]" type="text" class="w-full border p-2 rounded">
    </label>
    <label class="w-full">
        <span class="block text-sm text-gray-700 mb-1">ชื่อ-นามสกุล</span>
        <input name="fte_roles[0][name]" type="text" class="w-full border p-2 rounded">
    </label>
    <label class="w-full">
        <span class="block text-sm text-gray-700 mb-1">FTE</span>
        <input name="fte_roles[0][fte_value]" type="number" step="0.01" class="w-full border p-2 rounded">
    </label>
</div>
</div>
<button type="button" onclick="addFTERow()" class="mb-6 px-3 py-1 bg-blue-600 text-white rounded">+ เพิ่มผู้มีส่วนร่วม</button>

<hr class="my-6 border-t-2 border-gray-300">
<!-- E: ผลงานวิจัย (Output) -->
<h3 class="font-semibold text-lg text-blue-700 mb-2">E: ผลงานวิจัย (Output)</h3>
<div id="output-container" class="space-y-2 mb-4">
    <div class="grid grid-cols-3 gap-4">
    <label class="w-full">
        <span class="block text-sm text-gray-700 mb-1">ประเภท</span>
        <input name="outputs[0][type]" type="text" class="w-full border p-2 rounded">
    </label>
    <label class="w-full">
        <span class="block text-sm text-gray-700 mb-1">ชื่อผลงาน</span>
        <input name="outputs[0][title]" type="text" class="w-full border p-2 rounded">
    </label>
    <label class="w-full">
        <span class="block text-sm text-gray-700 mb-1">ปี</span>
        <input name="outputs[0][year]" type="text" class="w-full border p-2 rounded">
    </label>
</div>
</div>
<button type="button" onclick="addOutputRow()" class="mb-6 px-3 py-1 bg-green-600 text-white rounded">+ เพิ่ม Output</button>

<hr class="my-6 border-t-2 border-gray-300">
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
    const id = `r-${rCodeIndex}`;
    container.insertAdjacentHTML('beforeend', `
        <div id="${id}" class="flex gap-2">
            <input name="r_codes[]" type="text" placeholder="R:xxx" class="w-full border p-2 rounded">
            <button type="button" onclick="removeRow('${id}')" class="text-red-600">✕</button>
        </div>
    `);
    rCodeIndex++;
}

let irbCodeIndex = 1;
function addIRBCode() {
    const container = document.getElementById('irb-code-container');
    const id = `irb-${irbCodeIndex}`;
    container.insertAdjacentHTML('beforeend', `
        <div id="${id}" class="grid grid-cols-2 gap-2 items-center">
            <label class="w-full">
                <span class="block text-sm text-gray-700 mb-1">รหัส IRB</span>
                <input name="irbs[${irbCodeIndex}][code]" type="text" placeholder="ECxxxx" class="w-full border p-2 rounded">
            </label>
            <div class="flex gap-2">
                <label class="w-full">
                    <span class="block text-sm text-gray-700 mb-1">จาก (ชื่อหน่วยงาน)</span>
                    <input name="irbs[${irbCodeIndex}][source]" type="text" placeholder="ชื่อหน่วยงาน" class="w-full border p-2 rounded">
                </label>
                <button type="button" onclick="removeRow('${id}')" class="text-red-600 mt-6">✕</button>
            </div>
        </div>
    `);
    irbCodeIndex++;
}


let fteIndex = 1;
function addFTERow() {
    const container = document.getElementById('fte-container');
    const id = `fte-${fteIndex}`;
    container.insertAdjacentHTML('beforeend', `
        <div id="${id}" class="grid grid-cols-3 gap-4 items-center">
            <input name="fte_roles[${fteIndex}][role_group]" type="text" class="w-full border p-2 rounded" placeholder="กลุ่ม">
            <input name="fte_roles[${fteIndex}][name]" type="text" class="w-full border p-2 rounded" placeholder="ชื่อ">
            <div class="flex gap-2">
                <input name="fte_roles[${fteIndex}][fte_value]" type="number" step="0.01" class="w-full border p-2 rounded" placeholder="FTE">
                <button type="button" onclick="removeRow('${id}')" class="text-red-600">✕</button>
            </div>
        </div>
    `);
    fteIndex++;
}

let outputIndex = 1;
function addOutputRow() {
    const container = document.getElementById('output-container');
    const id = `output-${outputIndex}`;
    container.insertAdjacentHTML('beforeend', `
        <div id="${id}" class="grid grid-cols-3 gap-4 items-center">
            <input name="outputs[${outputIndex}][type]" type="text" class="w-full border p-2 rounded" placeholder="ประเภท">
            <input name="outputs[${outputIndex}][title]" type="text" class="w-full border p-2 rounded" placeholder="ชื่อผลงาน">
            <div class="flex gap-2">
                <input name="outputs[${outputIndex}][year]" type="text" class="w-full border p-2 rounded" placeholder="ปี">
                <button type="button" onclick="removeRow('${id}')" class="text-red-600">✕</button>
            </div>
        </div>
    `);
    outputIndex++;
}
function removeRow(id) {
    const el = document.getElementById(id);
    if (el) el.remove();
}
</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        flatpickr(".thai-datepicker", {
            locale: "th",
            dateFormat: "d-m-Y",
            altInput: true,
            altFormat: "j F Y",
            onReady: toBuddhist,
            onChange: toBuddhist,
            onMonthChange: toBuddhist,
            onYearChange: toBuddhist,
        });

        function toBuddhist(selectedDates, dateStr, instance) {
            const years = instance.calendarContainer.querySelectorAll('.flatpickr-current-month input.cur-year');
            years.forEach(el => {
                const year = parseInt(el.value);
                if (!isNaN(year)) el.value = year + 543;
                el.addEventListener('blur', () => {
                    let y = parseInt(el.value);
                    if (y > 2400) {
                        el.value = y - 543;
                        instance.setDate(new Date(instance.selectedDates[0]));
                    }
                });
            });
        }
    });


    let copiIndex = 1;
function addCopiRow() {
    const container = document.getElementById('copi-container');
    const id = `copi-${copiIndex}`;
    container.insertAdjacentHTML('beforeend', `
        <div id="${id}" class="grid grid-cols-2 gap-4 items-center">
            <label class="w-full">
                <span class="block text-sm text-gray-700 mb-1">ชื่อ COPI</span>
                <input name="copis[${copiIndex}][name]" type="text" class="w-full border p-2 rounded">
            </label>
            <div class="flex gap-2">
                <label class="w-full">
                    <span class="block text-sm text-gray-700 mb-1">สังกัด COPI</span>
                    <input name="copis[${copiIndex}][affiliation]" type="text" class="w-full border p-2 rounded">
                </label>
                <button type="button" onclick="removeRow('${id}')" class="text-red-600 mt-6">✕</button>
            </div>
        </div>
    `);
    copiIndex++;
}

</script>



@endsection
