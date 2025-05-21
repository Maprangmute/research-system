@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto mt-6 bg-white shadow-md rounded p-6">
    <h2 class="text-2xl font-bold mb-6 text-gray-800">✏️ แก้ไขข้อมูลโครงการวิจัย</h2>

    @if(session('success'))
        <div class="mb-4 p-3 bg-green-100 text-green-700 rounded">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('projects.update', $project->id) }}">
        @csrf
        @method('PUT')

        <!-- A: Project Overview -->
        <h3 class="font-semibold text-lg text-blue-700 mb-2">A: ข้อมูลโครงการ</h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-sm font-medium">ชื่อโครงการ (ภาษาไทย)*</label>
                <input name="project_name_th" type="text" required class="w-full border border-gray-300 rounded p-2" value="{{ $project->project_name_th }}">
            </div>
            <div>
                <label class="block text-sm font-medium">ชื่อโครงการ (English)</label>
                <input name="project_name_en" type="text" class="w-full border border-gray-300 rounded p-2" value="{{ $project->project_name_en }}">
            </div>
            <div>
                <label class="block text-sm font-medium">ประเภทของโครงการ*</label>
                <input name="project_type" type="text" required class="w-full border border-gray-300 rounded p-2" value="{{ $project->project_type }}">
            </div>
            <div>
                <label class="block text-sm font-medium">กลุ่มเนื้อหา (Theme)*</label>
                <input name="theme" type="text" required class="w-full border border-gray-300 rounded p-2" value="{{ $project->theme }}">
            </div>
            <div>
                <label class="block text-sm font-medium">Shortcut / Google Drive</label>
                <input name="shortcut_link" type="text" class="w-full border border-gray-300 rounded p-2" value="{{ $project->shortcut_link }}">
            </div>
        </div>

        <!-- B: Timeline -->
        <h3 class="font-semibold text-lg text-blue-700 mb-2 mt-6">B: ระยะเวลาดำเนินโครงการ</h3>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
            <div>
                <label>เดือนเริ่มต้น*</label>
                <input name="start_month" type="text" required class="w-full border p-2 rounded" value="{{ $project->start_month }}">
            </div>
            <div>
                <label>ปีเริ่มต้น (พ.ศ.)*</label>
                <input name="start_year" type="text" required class="w-full border p-2 rounded" value="{{ $project->start_year }}">
            </div>
            <div>
                <label>เดือนสิ้นสุด</label>
                <input name="end_month" type="text" class="w-full border p-2 rounded" value="{{ $project->end_month }}">
            </div>
            <div>
                <label>ปีสิ้นสุด (พ.ศ.)</label>
                <input name="end_year" type="text" class="w-full border p-2 rounded" value="{{ $project->end_year }}">
            </div>
        </div>

        <!-- C: Funding -->
        <h3 class="font-semibold text-lg text-blue-700 mb-2 mt-6">C: ข้อมูลทุน</h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
                <label>ประเภทแหล่งทุน</label>
                <input name="funding_type" type="text" class="w-full border p-2 rounded" value="{{ $project->funding_type }}">
            </div>
            <div>
                <label>ชื่อแหล่งทุน</label>
                <input name="funding_source" type="text" class="w-full border p-2 rounded" value="{{ $project->funding_source }}">
            </div>
            <div>
                <label>ประเภทองค์กร (รัฐบาล/เอกชน/มูลนิธิ)</label>
                <input name="funding_org_type" type="text" class="w-full border p-2 rounded" value="{{ $project->funding_org_type }}">
            </div>
            <div>
                <label>ชื่อทุน</label>
                <input name="funding_name" type="text" class="w-full border p-2 rounded" value="{{ $project->funding_name }}">
            </div>
            <div>
                <label>งบประมาณ (บาท)</label>
                <input name="budget" type="number" step="0.01" class="w-full border p-2 rounded" value="{{ $project->budget }}">
            </div>
        </div>

        <!-- D: FTE Roles -->
        <h3 class="font-semibold text-lg text-blue-700 mb-2 mt-6">D: รายชื่อผู้มีส่วนร่วม (FTE)</h3>
        <div id="fte-container" class="space-y-2 mb-4">
            @foreach ($project->fteRoles as $i => $fte)
                <div class="grid grid-cols-3 gap-4">
                    <input name="fte_roles[{{ $i }}][role_group]" type="text" class="w-full border p-2 rounded" value="{{ $fte->role_group }}">
                    <input name="fte_roles[{{ $i }}][name]" type="text" class="w-full border p-2 rounded" value="{{ $fte->name }}">
                    <input name="fte_roles[{{ $i }}][fte_value]" type="number" step="0.01" class="w-full border p-2 rounded" value="{{ $fte->fte_value }}">
                </div>
            @endforeach
        </div>
        <button type="button" onclick="addFTERow()" class="px-3 py-1 bg-blue-600 text-white rounded">+ เพิ่มผู้มีส่วนร่วม</button>

        <!-- E: Output -->
        <h3 class="font-semibold text-lg text-blue-700 mb-2 mt-6">E: ผลงานวิจัย (Output)</h3>
        <div id="output-container" class="space-y-2 mb-4">
            @foreach ($project->outputs as $j => $output)
                <div class="grid grid-cols-3 gap-4">
                    <input name="outputs[{{ $j }}][type]" type="text" class="w-full border p-2 rounded" value="{{ $output->type }}">
                    <input name="outputs[{{ $j }}][title]" type="text" class="w-full border p-2 rounded" value="{{ $output->title }}">
                    <input name="outputs[{{ $j }}][year]" type="text" class="w-full border p-2 rounded" value="{{ $output->year }}">
                </div>
            @endforeach
        </div>
        <button type="button" onclick="addOutputRow()" class="px-3 py-1 bg-green-600 text-white rounded">+ เพิ่ม Output</button>

        <!-- Submit -->
        <div class="mt-6">
            <button type="submit" class="bg-primary bg-indigo-600 text-white px-6 py-2 rounded hover:bg-indigo-700">💾 บันทึกการเปลี่ยนแปลง</button>
        </div>
    </form>
</div>

<script>
let fteIndex = {{ count($project->fteRoles) }};
function addFTERow() {
    const container = document.getElementById('fte-container');
    container.insertAdjacentHTML('beforeend', `
        <div class="grid grid-cols-3 gap-4">
            <input name="fte_roles[${fteIndex}][role_group]" type="text" class="w-full border p-2 rounded" placeholder="กลุ่ม">
            <input name="fte_roles[${fteIndex}][name]" type="text" class="w-full border p-2 rounded" placeholder="ชื่อ">
            <input name="fte_roles[${fteIndex}][fte_value]" type="number" step="0.01" class="w-full border p-2 rounded" placeholder="FTE">
        </div>
    `);
    fteIndex++;
}

let outputIndex = {{ count($project->outputs) }};
function addOutputRow() {
    const container = document.getElementById('output-container');
    container.insertAdjacentHTML('beforeend', `
        <div class="grid grid-cols-3 gap-4">
            <input name="outputs[${outputIndex}][type]" type="text" class="w-full border p-2 rounded" placeholder="ประเภท">
            <input name="outputs[${outputIndex}][title]" type="text" class="w-full border p-2 rounded" placeholder="ชื่อผลงาน">
            <input name="outputs[${outputIndex}][year]" type="text" class="w-full border p-2 rounded" placeholder="ปี">
        </div>
    `);
    outputIndex++;
}
</script>
@endsection
