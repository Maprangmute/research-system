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

        <!-- A: ข้อมูลโครงการ -->
        <hr class="my-6 border-t-2 border-gray-300">
        <h3 class="font-semibold text-lg text-blue-700 mb-2">A: ข้อมูลโครงการ</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <x-input label="ชื่อโครงการ (ภาษาไทย)*" name="project_name_th" required />
            <x-input label="ชื่อโครงการ (English)" name="project_name_en" />
            <x-input label="ประเภทของโครงการ*" name="project_type" required />
            <x-input label="กลุ่มเนื้อหา (Theme)*" name="theme" required />
            <x-input label="ลิงก์ Google Drive / Shortcut" name="shortcut_link" />
            <x-input label="ขนาดโครงการ (S/M/L)" name="size" />
        </div>

        <!-- B: ระยะเวลาดำเนินโครงการ -->
        <hr class="my-6 border-t-2 border-gray-300">
        <h3 class="font-semibold text-lg text-blue-700 mb-2">B: ระยะเวลาดำเนินโครงการ</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <x-input label="BA000: Project Initiation & Planning (Status)" name="planning_status" />
            <x-input label="BA001: วันที่เริ่มเขียน Proposal (เดือน/ปี)" name="proposal_date" placeholder="มกราคม 2568" />
            <x-input label="BC000: Project Execution Status" name="execution_status" />
            <x-input label="BC101: วันที่สัญญาอนุมัติ (เดือน/ปี)" name="contract_date" placeholder="กุมภาพันธ์ 2568" />
            <x-input label="BC102: วันที่เริ่มโครงการจริง (เดือน/ปี)" name="start_date" placeholder="มีนาคม 2568" />
            <x-input label="Start Month" name="start_month" placeholder="1-12" />
            <x-input label="Start Year (พ.ศ.)" name="start_year" placeholder="2568" />
            <x-input label="End Month" name="end_month" placeholder="1-12" />
            <x-input label="End Year (พ.ศ.)" name="end_year" placeholder="2569" />
            <x-input label="BD000: Review Status" name="review_status" />
            <x-input label="BE000: Closure Status" name="closure_status" />
        </div>

        <!-- C: ข้อมูลทุน -->
        <hr class="my-6 border-t-2 border-gray-300">
        <h3 class="font-semibold text-lg text-blue-700 mb-2">C: ข้อมูลทุน</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <x-input label="ประเภทแหล่งทุน" name="funding_type" />
            <x-input label="ชื่อแหล่งทุน" name="funding_source" />
            <x-input label="ประเภทองค์กร (รัฐ/เอกชน/มูลนิธิ)" name="funding_org_type" />
            <x-input label="ชื่อทุน" name="funding_name" />
            <x-input label="งบประมาณ (บาท)" name="budget" type="number" step="0.01" />
        </div>

        <!-- D: รหัสโครงการ R / IRB -->
        <hr class="my-6 border-t-2 border-gray-300">
        <h3 class="font-semibold text-lg text-blue-700 mb-2">D: รหัสโครงการ R</h3>
        <div id="r-code-container" class="space-y-2 mb-4">
            <div id="r-0" class="flex gap-2">
                <x-input name="r_codes[]" placeholder="R:001" class="w-full" />
            </div>
        </div>
        <button type="button" onclick="addRCode()" class="mb-6 px-3 py-1 bg-purple-600 text-white rounded">+ เพิ่มรหัส R</button>

        <h3 class="font-semibold text-lg text-blue-700 mb-2">รหัส IRB</h3>
        <div id="irb-code-container" class="space-y-2 mb-4">
            <div id="irb-0" class="grid grid-cols-2 gap-2 items-center">
                <x-input name="irb_codes[]" placeholder="EC2025" />
                <x-input name="irb_sources[]" placeholder="ชื่อหน่วยงาน" />
            </div>
        </div>
        <button type="button" onclick="addIRBCode()" class="mb-6 px-3 py-1 bg-red-600 text-white rounded">+ เพิ่มรหัส IRB</button>

        <!-- E: FTE -->
        <hr class="my-6 border-t-2 border-gray-300">
        <h3 class="font-semibold text-lg text-blue-700 mb-2">E: รายชื่อผู้มีส่วนร่วม (FTE)</h3>
        <div id="fte-container" class="space-y-2 mb-4">
            <div class="grid grid-cols-3 gap-4">
                <x-input name="fte_roles[0][role_group]" placeholder="กลุ่ม" />
                <x-input name="fte_roles[0][name]" placeholder="ชื่อ" />
                <x-input name="fte_roles[0][fte_value]" placeholder="FTE" type="number" step="0.01" />
            </div>
        </div>
        <button type="button" onclick="addFTERow()" class="mb-6 px-3 py-1 bg-blue-600 text-white rounded">+ เพิ่มผู้มีส่วนร่วม</button>

        <!-- F: Output -->
        <hr class="my-6 border-t-2 border-gray-300">
        <h3 class="font-semibold text-lg text-blue-700 mb-2">F: ผลงานวิจัย (Output)</h3>
        <div id="output-container" class="space-y-2 mb-4">
            <div class="grid grid-cols-3 gap-4">
                <x-input name="outputs[0][type]" placeholder="ประเภท" />
                <x-input name="outputs[0][title]" placeholder="ชื่อผลงาน" />
                <x-input name="outputs[0][year]" placeholder="ปี" />
            </div>
        </div>
        <button type="button" onclick="addOutputRow()" class="mb-6 px-3 py-1 bg-green-600 text-white rounded">+ เพิ่ม Output</button>

        <!-- G: ยุทธศาสตร์ -->
        <hr class="my-6 border-t-2 border-gray-300">
        <h3 class="font-semibold text-lg text-blue-700 mb-2">G: ยุทธศาสตร์ Siriraj Health Policy</h3>
        @include('projects.partials.strategy-checkboxes')

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
            <input name="irb_codes[]" type="text" placeholder="ECxxxx" class="w-full border p-2 rounded">
            <div class="flex gap-2">
                <input name="irb_sources[]" type="text" placeholder="จาก (ชื่อหน่วยงาน)" class="w-full border p-2 rounded">
                <button type="button" onclick="removeRow('${id}')" class="text-red-600">✕</button>
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
@endsection
