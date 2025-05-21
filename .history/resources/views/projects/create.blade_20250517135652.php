@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/th.js"></script>

<div class="max-w-5xl mx-auto mt-6 bg-white shadow-md rounded p-6">
    <h2 class="text-xl font-bold mb-6 text-gray-800">📋 เพิ่มข้อมูลโครงการวิจัย</h2>

    @if(session('success'))
        <div class="mb-4 p-3 bg-green-100 text-green-700 rounded">{{ session('success') }}</div>
    @endif

    <form action="{{ route('projects.store') }}" method="POST">
        @csrf

        <!-- A: ข้อมูลโครงการ -->
        <hr class="my-6 border-t-2 border-gray-300">
        <h3 class="text-lg font-semibold text-blue-700 mb-2">A: ข้อมูลโครงการ</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach([
                ['project_name_th', 'ชื่อโครงการ (ภาษาไทย)*', true],
                ['project_name_en', 'ชื่อโครงการ (English)', false],
                ['project_type', 'ประเภทของโครงการ*', true],
                ['theme', 'กลุ่มเนื้อหา (Theme)*', true],
                ['shortcut_link', 'ลิงก์ Google Drive / Shortcut', false],
                ['size', 'ขนาดโครงการ (S/M/L)', false],
            ] as [$name, $label, $required])
            <label class="w-full">
                <span class="block text-sm text-gray-700 mb-1">{{ $label }}</span>
                <input name="{{ $name }}" type="text" class="w-full border p-2 rounded" {{ $required ? 'required' : '' }} value="{{ old($name) }}">
                @error($name) <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </label>
            @endforeach
        </div>

        <!-- B: ระยะเวลาดำเนินโครงการ -->
        <hr class="my-6 border-t-2 border-gray-300">
        <h3 class="text-lg font-semibold text-blue-700 mb-2">B: ระยะเวลาดำเนินโครงการ</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach([
                ['planning_status', 'BA000: สถานะการวางแผนโครงการ'],
                ['proposal_date', 'BA001: วันที่เริ่มเขียน Proposal'],
                ['execution_status', 'BC000: สถานะดำเนินโครงการ'],
                ['contract_date', 'BC101: วันที่สัญญาอนุมัติ'],
                ['start_date', 'BC102: วันที่เริ่มโครงการ'],
                ['start_month', 'เดือนเริ่มต้น'],
                ['start_year', 'ปีเริ่มต้น'],
                ['end_month', 'เดือนสิ้นสุด'],
                ['end_year', 'ปีสิ้นสุด'],
                ['review_status', 'BD000: สถานะการทบทวนโครงการ'],
                ['closure_status', 'BE000: สถานะการปิดโครงการ'],
            ] as [$name, $label])
            <label class="w-full">
                <span class="block text-sm text-gray-700 mb-1">{{ $label }}</span>
                <input name="{{ $name }}" type="text" class="w-full border p-2 rounded date-th" value="{{ old($name) }}">
            </label>
            @endforeach
        </div>

        <!-- C: ข้อมูลทุน -->
        <hr class="my-6 border-t-2 border-gray-300">
        <h3 class="text-lg font-semibold text-blue-700 mb-2">C: ข้อมูลทุน</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach([
                ['funding_type', 'ประเภทแหล่งทุน'],
                ['funding_source', 'ชื่อแหล่งทุน'],
                ['funding_org_type', 'ประเภทองค์กร'],
                ['funding_name', 'ชื่อทุน'],
                ['budget', 'งบประมาณ (บาท)'],
            ] as [$name, $label])
            <label class="w-full">
                <span class="block text-sm text-gray-700 mb-1">{{ $label }}</span>
                <input name="{{ $name }}" type="{{ $name === 'budget' ? 'number' : 'text' }}" class="w-full border p-2 rounded" value="{{ old($name) }}">
            </label>
            @endforeach
        </div>

        <!-- D: รหัสโครงการ R -->
        <hr class="my-6 border-t-2 border-gray-300">
        <h3 class="text-lg font-semibold text-blue-700 mb-2">D: รหัสโครงการ R</h3>
        <div id="r-code-container" class="space-y-2 mb-4">
            <div id="r-0" class="flex gap-2">
                <input name="r_codes[]" type="text" placeholder="R:001" class="w-full border p-2 rounded">
            </div>
        </div>
        <button type="button" onclick="addRCode()" class="mb-6 px-3 py-1 bg-purple-600 text-white rounded">+ เพิ่มรหัส R</button>

        <!-- D: รหัส IRB -->
        <h3 class="text-lg font-semibold text-blue-700 mb-2">D: รหัส IRB</h3>
        <div id="irb-code-container" class="space-y-2 mb-4">
            <div id="irb-0" class="grid grid-cols-2 gap-2">
                <input name="irb_codes[]" type="text" placeholder="ECxxxx" class="w-full border p-2 rounded">
                <input name="irb_sources[]" type="text" placeholder="ชื่อหน่วยงาน" class="w-full border p-2 rounded">
            </div>
        </div>
        <button type="button" onclick="addIRBCode()" class="mb-6 px-3 py-1 bg-red-600 text-white rounded">+ เพิ่มรหัส IRB</button>

        <!-- E: FTE -->
        <hr class="my-6 border-t-2 border-gray-300">
        <h3 class="text-lg font-semibold text-blue-700 mb-2">E: รายชื่อผู้มีส่วนร่วม (FTE)</h3>
        <div id="fte-container" class="space-y-2 mb-4">
            <div class="grid grid-cols-3 gap-4">
                <input name="fte_roles[0][role_group]" placeholder="กลุ่ม" class="w-full border p-2 rounded">
                <input name="fte_roles[0][name]" placeholder="ชื่อ" class="w-full border p-2 rounded">
                <input name="fte_roles[0][fte_value]" type="number" step="0.01" placeholder="FTE" class="w-full border p-2 rounded">
            </div>
        </div>
        <button type="button" onclick="addFTERow()" class="mb-6 px-3 py-1 bg-blue-600 text-white rounded">+ เพิ่มผู้มีส่วนร่วม</button>

        <!-- F: Output -->
        <hr class="my-6 border-t-2 border-gray-300">
        <h3 class="text-lg font-semibold text-blue-700 mb-2">F: ผลงานวิจัย (Output)</h3>
        <div id="output-container" class="space-y-2 mb-4">
            <div class="grid grid-cols-3 gap-4">
                <input name="outputs[0][type]" placeholder="ประเภท" class="w-full border p-2 rounded">
                <input name="outputs[0][title]" placeholder="ชื่อผลงาน" class="w-full border p-2 rounded">
                <input name="outputs[0][year]" placeholder="ปี" class="w-full border p-2 rounded">
            </div>
        </div>
        <button type="button" onclick="addOutputRow()" class="mb-6 px-3 py-1 bg-green-600 text-white rounded">+ เพิ่ม Output</button>

        <!-- G: ยุทธศาสตร์ -->
        <hr class="my-6 border-t-2 border-gray-300">
        <h3 class="text-lg font-semibold text-blue-700 mb-2">G: ยุทธศาสตร์ Siriraj Health Policy</h3>
        @include('projects.partials.strategy-checkboxes')

        <!-- Submit -->
        <div class="mt-6">
            <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded hover:bg-indigo-700">💾 บันทึกข้อมูล</button>
        </div>
    </form>
</div>

<script>
flatpickr(".date-th", {
    locale: "th",
    dateFormat: "d F Y",
    altInput: true,
    altFormat: "d F Y",
    defaultDate: null,
    onReady: (selectedDates, dateStr, instance) => {
        const year = instance.currentYear + 543;
        instance.altInput.value = instance.altInput.value.replace(instance.currentYear, year);
    },
    onChange: (selectedDates, dateStr, instance) => {
        const date = selectedDates[0];
        if (date) {
            const buddhistYear = date.getFullYear() + 543;
            const formatted = instance.formatDate(date, "d F") + " " + buddhistYear;
            instance.altInput.value = formatted;
        }
    }
});

let rCodeIndex = 1;
function addRCode() {
    const container = document.getElementById('r-code-container');
    const id = `r-${rCodeIndex++}`;
    container.insertAdjacentHTML('beforeend', `
        <div id="${id}" class="flex gap-2">
            <input name="r_codes[]" type="text" placeholder="R:xxx" class="w-full border p-2 rounded">
            <button type="button" onclick="removeRow('${id}')" class="text-red-600">✕</button>
        </div>
    `);
}

let irbCodeIndex = 1;
function addIRBCode() {
    const container = document.getElementById('irb-code-container');
    const id = `irb-${irbCodeIndex++}`;
    container.insertAdjacentHTML('beforeend', `
        <div id="${id}" class="grid grid-cols-2 gap-2">
            <input name="irb_codes[]" type="text" placeholder="ECxxxx" class="w-full border p-2 rounded">
            <div class="flex gap-2">
                <input name="irb_sources[]" type="text" placeholder="จาก (ชื่อหน่วยงาน)" class="w-full border p-2 rounded">
                <button type="button" onclick="removeRow('${id}')" class="text-red-600">✕</button>
            </div>
        </div>
    `);
}

let fteIndex = 1;
function addFTERow() {
    const id = `fte-${fteIndex++}`;
    document.getElementById('fte-container').insertAdjacentHTML('beforeend', `
        <div id="${id}" class="grid grid-cols-3 gap-4 items-center">
            <input name="fte_roles[${fteIndex}][role_group]" class="w-full border p-2 rounded" placeholder="กลุ่ม">
            <input name="fte_roles[${fteIndex}][name]" class="w-full border p-2 rounded" placeholder="ชื่อ">
            <div class="flex gap-2">
                <input name="fte_roles[${fteIndex}][fte_value]" type="number" step="0.01" class="w-full border p-2 rounded" placeholder="FTE">
                <button type="button" onclick="removeRow('${id}')" class="text-red-600">✕</button>
            </div>
        </div>
    `);
}

let outputIndex = 1;
function addOutputRow() {
    const id = `output-${outputIndex++}`;
    document.getElementById('output-container').insertAdjacentHTML('beforeend', `
        <div id="${id}" class="grid grid-cols-3 gap-4 items-center">
            <input name="outputs[${outputIndex}][type]" class="w-full border p-2 rounded" placeholder="ประเภท">
            <input name="outputs[${outputIndex}][title]" class="w-full border p-2 rounded" placeholder="ชื่อผลงาน">
            <div class="flex gap-2">
                <input name="outputs[${outputIndex}][year]" class="w-full border p-2 rounded" placeholder="ปี">
                <button type="button" onclick="removeRow('${id}')" class="text-red-600">✕</button>
            </div>
        </div>
    `);
}

function removeRow(id) {
    const el = document.getElementById(id);
    if (el) el.remove();
}
</script>
@endsection
