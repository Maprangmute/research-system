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

        <!-- Section: A -->
        <hr class="my-6 border-t-2 border-gray-300">
        <h3 class="font-semibold text-lg text-blue-700 mb-2">A: ข้อมูลโครงการ</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <x-input name="project_name_th" label="ชื่อโครงการ (ภาษาไทย)*" required />
            <x-input name="project_name_en" label="ชื่อโครงการ (English)" />
            <x-input name="project_type" label="ประเภทของโครงการ/กิจกรรม (TYPE)*" required />
            <x-input name="theme" label="กลุ่มเนื้อหาหลักของงาน (Content/Theme)*" required />
            <x-input name="shortcut_link" label="Shortcut / Google Drive" />
            <x-input name="size" label="ขนาดโครงการ (S/M/L)" />
            <x-input name="io_code" label="รหัสโครงการ IO (AA)" />
            <x-input name="course_code" label="รหัสรายวิชา" />
        </div>

        <!-- Section: B -->
        <hr class="my-6 border-t-2 border-gray-300">
        <h3 class="font-semibold text-lg text-blue-700 mb-2">B: ระยะเวลาดำเนินโครงการ</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <x-input name="planning_status" label="Project Planning Status (BA000)" />
            <x-input name="proposal_date" label="วันที่เริ่มเขียน Proposal (BA001)" />
            <x-input name="execution_status" label="Execution Status (BC000)" />
            <x-input name="contract_date" label="วันที่อนุมัติสัญญา (BC101)" />
            <x-input name="start_date" label="วันที่โครงการเริ่ม (BC102)" />
            <x-input name="start_month" label="เดือนเริ่มต้น" />
            <x-input name="start_year" label="ปีเริ่มต้น" />
            <x-input name="end_month" label="เดือนสิ้นสุด" />
            <x-input name="end_year" label="ปีสิ้นสุด" />
            <x-input name="review_status" label="Submission/Review Status (BD000)" />
            <x-input name="closure_status" label="Project Closure Status (BE000)" />
        </div>

        <!-- Section: C -->
        <hr class="my-6 border-t-2 border-gray-300">
        <h3 class="font-semibold text-lg text-blue-700 mb-2">C: ข้อมูลทุน</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <x-input name="funding_type" label="ประเภทแหล่งทุน (DA102)" />
            <x-input name="funding_source" label="ชื่อแหล่งทุน (DA103)" />
            <x-input name="funding_org_type" label="ประเภทองค์กร (DA104)" />
            <x-input name="funding_name" label="ชื่อทุน (DA105)" />
            <x-input name="budget" type="number" label="งบประมาณ (DA106) (บาท)" />
        </div>

        <!-- Section: รหัส R -->
        <hr class="my-6 border-t-2 border-gray-300">
        <h3 class="font-semibold text-lg text-blue-700 mb-2">D: รหัสโครงการ R</h3>
        <div id="r-code-container" class="space-y-2 mb-4">
            <div id="r-0" class="flex gap-2">
                <input name="r_codes[]" type="text" placeholder="R:001" class="w-full border p-2 rounded">
            </div>
        </div>
        <button type="button" onclick="addRCode()" class="mb-6 px-3 py-1 bg-purple-600 text-white rounded">+ เพิ่มรหัส R</button>

        <!-- Section: IRB -->
        <h3 class="font-semibold text-lg text-blue-700 mb-2">E: รหัส IRB</h3>
        <div id="irb-code-container" class="space-y-2 mb-4">
            <div id="irb-0" class="grid grid-cols-2 gap-2">
                <input name="irb_codes[]" type="text" placeholder="EC2025" class="w-full border p-2 rounded">
                <input name="irb_sources[]" type="text" placeholder="ชื่อหน่วยงาน" class="w-full border p-2 rounded">
            </div>
        </div>
        <button type="button" onclick="addIRBCode()" class="mb-6 px-3 py-1 bg-red-600 text-white rounded">+ เพิ่มรหัส IRB</button>

        <!-- Section: FTE -->
        <hr class="my-6 border-t-2 border-gray-300">
        <h3 class="font-semibold text-lg text-blue-700 mb-2">F: รายชื่อผู้มีส่วนร่วม (FTE)</h3>
        <div id="fte-container" class="space-y-2 mb-4">
            <div class="grid grid-cols-3 gap-4">
                <input name="fte_roles[0][role_group]" type="text" placeholder="กลุ่ม" class="w-full border p-2 rounded">
                <input name="fte_roles[0][name]" type="text" placeholder="ชื่อ" class="w-full border p-2 rounded">
                <input name="fte_roles[0][fte_value]" type="number" step="0.01" placeholder="FTE" class="w-full border p-2 rounded">
            </div>
        </div>
        <button type="button" onclick="addFTERow()" class="mb-6 px-3 py-1 bg-blue-600 text-white rounded">+ เพิ่มผู้มีส่วนร่วม</button>

        <!-- Section: Output -->
        <hr class="my-6 border-t-2 border-gray-300">
        <h3 class="font-semibold text-lg text-blue-700 mb-2">G: ผลงานวิจัย (Output)</h3>
        <div id="output-container" class="space-y-2 mb-4">
            <div class="grid grid-cols-3 gap-4">
                <input name="outputs[0][type]" type="text" placeholder="ประเภท" class="w-full border p-2 rounded">
                <input name="outputs[0][title]" type="text" placeholder="ชื่อผลงาน" class="w-full border p-2 rounded">
                <input name="outputs[0][year]" type="text" placeholder="ปี" class="w-full border p-2 rounded">
            </div>
        </div>
        <button type="button" onclick="addOutputRow()" class="mb-6 px-3 py-1 bg-green-600 text-white rounded">+ เพิ่ม Output</button>

        <!-- Section: ยุทธศาสตร์ -->
        <hr class="my-6 border-t-2 border-gray-300">
        @include('projects.partials._form_strategies')

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
        <div id="${id}" class="grid grid-cols-2 gap-2">
            <input name="irb_codes[]" type="text" class="w-full border p-2 rounded" placeholder="IRB Code">
            <div class="flex gap-2">
                <input name="irb_sources[]" type="text" class="w-full border p-2 rounded" placeholder="จาก (ชื่อหน่วยงาน)">
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
