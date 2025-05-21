@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto mt-6 bg-white shadow-md rounded p-6">
    <h2 class="text-xl font-bold mb-6 text-gray-800">✏️ แก้ไขข้อมูลโครงการ</h2>

    @if(session('success'))
        <div class="mb-4 p-3 bg-green-100 text-green-700 rounded">
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('projects.update', $project->id) }}" method="POST">
        @csrf
        @method('PUT')

        <hr class="my-6 border-t-2 border-gray-300">
        <h3 class="font-semibold text-lg text-blue-700 mb-2">A: ข้อมูลโครงการ</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <label>ชื่อโครงการ (ภาษาไทย)* <input name="project_name_th" value="{{ old('project_name_th', $project->project_name_th) }}" required class="w-full border p-2 rounded"></label>
            <label>ชื่อโครงการ (English) <input name="project_name_en" value="{{ old('project_name_en', $project->project_name_en) }}" class="w-full border p-2 rounded"></label>
            <label>ประเภทของโครงการ* <input name="project_type" value="{{ old('project_type', $project->project_type) }}" required class="w-full border p-2 rounded"></label>
            <label>กลุ่มเนื้อหา (Theme)* <input name="theme" value="{{ old('theme', $project->theme) }}" required class="w-full border p-2 rounded"></label>
            <label>ลิงก์ Google Drive <input name="shortcut_link" value="{{ old('shortcut_link', $project->shortcut_link) }}" class="w-full border p-2 rounded"></label>
            <label>ขนาดโครงการ (S/M/L) <input name="size" value="{{ old('size', $project->size) }}" class="w-full border p-2 rounded"></label>
        </div>

        <hr class="my-6 border-t-2 border-gray-300">
        <h3 class="font-semibold text-lg text-blue-700 mb-2">B: ระยะเวลาดำเนินการ</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <label>BA000: สถานะเริ่มโครงการ <input name="planning_status" value="{{ $project->planning_status }}" class="w-full border p-2 rounded"></label>
            <label>BA001: วันที่เริ่มเขียน Proposal <input name="proposal_date" value="{{ $project->proposal_date }}" class="w-full border p-2 rounded"></label>
            <label>BC000: สถานะดำเนินโครงการ <input name="execution_status" value="{{ $project->execution_status }}" class="w-full border p-2 rounded"></label>
            <label>BC101: วันที่สัญญาอนุมัติ <input name="contract_date" value="{{ $project->contract_date }}" class="w-full border p-2 rounded"></label>
            <label>BC102: วันที่เริ่มโครงการจริง <input name="start_date" value="{{ $project->start_date }}" class="w-full border p-2 rounded"></label>
            <label>เดือนเริ่มต้น <input name="start_month" value="{{ $project->start_month }}" class="w-full border p-2 rounded"></label>
            <label>ปีเริ่มต้น <input name="start_year" value="{{ $project->start_year }}" class="w-full border p-2 rounded"></label>
            <label>เดือนสิ้นสุด <input name="end_month" value="{{ $project->end_month }}" class="w-full border p-2 rounded"></label>
            <label>ปีสิ้นสุด <input name="end_year" value="{{ $project->end_year }}" class="w-full border p-2 rounded"></label>
            <label>BD000: สถานะ Review <input name="review_status" value="{{ $project->review_status }}" class="w-full border p-2 rounded"></label>
            <label>BE000: สถานะ Closure <input name="closure_status" value="{{ $project->closure_status }}" class="w-full border p-2 rounded"></label>
        </div>

        <hr class="my-6 border-t-2 border-gray-300">
        <h3 class="font-semibold text-lg text-blue-700 mb-2">C: ข้อมูลทุน</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <label>ประเภทแหล่งทุน <input name="funding_type" value="{{ $project->funding_type }}" class="w-full border p-2 rounded"></label>
            <label>ชื่อแหล่งทุน <input name="funding_source" value="{{ $project->funding_source }}" class="w-full border p-2 rounded"></label>
            <label>ประเภทองค์กร (รัฐ/เอกชน/มูลนิธิ) <input name="funding_org_type" value="{{ $project->funding_org_type }}" class="w-full border p-2 rounded"></label>
            <label>ชื่อทุน <input name="funding_name" value="{{ $project->funding_name }}" class="w-full border p-2 rounded"></label>
            <label>งบประมาณ (บาท) <input name="budget" value="{{ $project->budget }}" class="w-full border p-2 rounded" type="number"></label>
        </div>

        <hr class="my-6 border-t-2 border-gray-300">
        <h3 class="font-semibold text-lg text-blue-700 mb-2">D: รหัส R / IRB</h3>
        <div class="mb-4" id="r-code-container">
            @foreach ($project->r_codes as $i => $code)
                <div class="flex gap-2 mb-2">
                    <input name="r_codes[]" value="{{ $code }}" class="w-full border p-2 rounded" placeholder="R:xxx">
                    <button type="button" onclick="this.parentElement.remove()" class="text-red-600">✕</button>
                </div>
            @endforeach
        </div>
        <button type="button" onclick="addRCode()" class="mb-6 px-3 py-1 bg-purple-600 text-white rounded">+ เพิ่มรหัส R</button>

        <div class="mb-4" id="irb-code-container">
            @foreach ($project->irb_codes as $i => $code)
                <div class="grid grid-cols-2 gap-2 mb-2 items-center">
                    <input name="irb_codes[]" value="{{ $code }}" class="w-full border p-2 rounded" placeholder="ECxxxx">
                    <div class="flex gap-2">
                        <input name="irb_sources[]" value="{{ $project->irb_sources[$i] ?? '' }}" class="w-full border p-2 rounded" placeholder="หน่วยงาน">
                        <button type="button" onclick="this.closest('.grid').remove()" class="text-red-600">✕</button>
                    </div>
                </div>
            @endforeach
        </div>
        <button type="button" onclick="addIRBCode()" class="mb-6 px-3 py-1 bg-red-600 text-white rounded">+ เพิ่มรหัส IRB</button>

        <hr class="my-6 border-t-2 border-gray-300">
        <h3 class="font-semibold text-lg text-blue-700 mb-2">E: รายชื่อผู้มีส่วนร่วม (FTE)</h3>
        <div class="space-y-2 mb-4" id="fte-container">
            @foreach ($project->fteRoles as $i => $fte)
                <div class="grid grid-cols-3 gap-4 items-center" id="fte-{{ $i }}">
                    <input name="fte_roles[{{ $i }}][role_group]" value="{{ $fte->role_group }}" class="w-full border p-2 rounded" placeholder="กลุ่ม">
                    <input name="fte_roles[{{ $i }}][name]" value="{{ $fte->name }}" class="w-full border p-2 rounded" placeholder="ชื่อ">
                    <div class="flex gap-2">
                        <input name="fte_roles[{{ $i }}][fte_value]" value="{{ $fte->fte_value }}" class="w-full border p-2 rounded" placeholder="FTE">
                        <button type="button" onclick="removeRow('fte-{{ $i }}')" class="text-red-600">✕</button>
                    </div>
                </div>
            @endforeach
        </div>
        <button type="button" onclick="addFTERow()" class="mb-6 px-3 py-1 bg-blue-600 text-white rounded">+ เพิ่มผู้มีส่วนร่วม</button>

        <hr class="my-6 border-t-2 border-gray-300">
        <h3 class="font-semibold text-lg text-blue-700 mb-2">F: ผลงานวิจัย (Output)</h3>
        <div class="space-y-2 mb-4" id="output-container">
            @foreach ($project->outputs as $i => $out)
                <div class="grid grid-cols-3 gap-4 items-center" id="output-{{ $i }}">
                    <input name="outputs[{{ $i }}][type]" value="{{ $out->type }}" class="w-full border p-2 rounded" placeholder="ประเภท">
                    <input name="outputs[{{ $i }}][title]" value="{{ $out->title }}" class="w-full border p-2 rounded" placeholder="ชื่อ">
                    <div class="flex gap-2">
                        <input name="outputs[{{ $i }}][year]" value="{{ $out->year }}" class="w-full border p-2 rounded" placeholder="ปี">
                        <button type="button" onclick="removeRow('output-{{ $i }}')" class="text-red-600">✕</button>
                    </div>
                </div>
            @endforeach
        </div>
        <button type="button" onclick="addOutputRow()" class="mb-6 px-3 py-1 bg-green-600 text-white rounded">+ เพิ่ม Output</button>

        <hr class="my-6 border-t-2 border-gray-300">
        @include('projects.partials.strategy-checkboxes', ['project' => $project])

        <div class="mt-6">
            <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded hover:bg-indigo-700">💾 บันทึกการแก้ไข</button>
        </div>
    </form>
</div>

<script>
let rCodeIndex = {{ count($project->r_codes ?? []) }};
function addRCode() {
    const id = `r-${rCodeIndex}`;
    document.getElementById('r-code-container').insertAdjacentHTML('beforeend', `
        <div id="${id}" class="flex gap-2 mb-2">
            <input name="r_codes[]" type="text" placeholder="R:xxx" class="w-full border p-2 rounded">
            <button type="button" onclick="removeRow('${id}')" class="text-red-600">✕</button>
        </div>
    `);
    rCodeIndex++;
}

let irbCodeIndex = {{ count($project->irb_codes ?? []) }};
function addIRBCode() {
    const id = `irb-${irbCodeIndex}`;
    document.getElementById('irb-code-container').insertAdjacentHTML('beforeend', `
        <div id="${id}" class="grid grid-cols-2 gap-2 mb-2 items-center">
            <input name="irb_codes[]" type="text" placeholder="ECxxx" class="w-full border p-2 rounded">
            <div class="flex gap-2">
                <input name="irb_sources[]" type="text" placeholder="หน่วยงาน" class="w-full border p-2 rounded">
                <button type="button" onclick="removeRow('${id}')" class="text-red-600">✕</button>
            </div>
        </div>
    `);
    irbCodeIndex++;
}

let fteIndex = {{ count($project->fteRoles ?? []) }};
function addFTERow() {
    const id = `fte-${fteIndex}`;
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
    fteIndex++;
}

let outputIndex = {{ count($project->outputs ?? []) }};
function addOutputRow() {
    const id = `output-${outputIndex}`;
    document.getElementById('output-container').insertAdjacentHTML('beforeend', `
        <div id="${id}" class="grid grid-cols-3 gap-4 items-center">
            <input name="outputs[${outputIndex}][type]" class="w-full border p-2 rounded" placeholder="ประเภท">
            <input name="outputs[${outputIndex}][title]" class="w-full border p-2 rounded" placeholder="ชื่อ">
            <div class="flex gap-2">
                <input name="outputs[${outputIndex}][year]" class="w-full border p-2 rounded" placeholder="ปี">
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
