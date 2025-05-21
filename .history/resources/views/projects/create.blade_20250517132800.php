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

        {{-- A: ข้อมูลโครงการ --}}
        <hr class="my-6 border-t-2 border-gray-300">
        <h3 class="font-semibold text-lg text-blue-700 mb-2">A: ข้อมูลโครงการ</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            @foreach([
                'project_name_th' => 'ชื่อโครงการ (ภาษาไทย)*',
                'project_name_en' => 'ชื่อโครงการ (English)',
                'project_type' => 'ประเภทของโครงการ*',
                'theme' => 'กลุ่มเนื้อหา (Theme)*',
                'shortcut_link' => 'ลิงก์ Google Drive / Shortcut',
                'size' => 'ขนาดโครงการ (S/M/L)',
            ] as $name => $label)
                <label class="w-full">
                    <span class="block text-sm text-gray-700 mb-1">{{ $label }}</span>
                    <input name="{{ $name }}" type="text" class="w-full border p-2 rounded" {{ str_contains($label, '*') ? 'required' : '' }}>
                </label>
            @endforeach
        </div>

        {{-- รหัสโครงการ R --}}
        <hr class="my-6 border-t-2 border-gray-300">
        <h3 class="font-semibold text-lg text-blue-700 mb-2">รหัสโครงการ R</h3>
        <div id="r-code-container" class="space-y-2 mb-4">
            <div id="r-0" class="flex gap-2">
                <input name="r_codes[]" type="text" placeholder="R:xxx" class="w-full border p-2 rounded">
            </div>
        </div>
        <button type="button" onclick="addRCode()" class="mb-6 px-3 py-1 bg-purple-600 text-white rounded">+ เพิ่มรหัส R</button>

        {{-- รหัส IRB --}}
        <hr class="my-6 border-t-2 border-gray-300">
        <h3 class="font-semibold text-lg text-blue-700 mb-2">รหัส IRB</h3>
        <div id="irb-code-container" class="space-y-2 mb-4">
            <div id="irb-0" class="grid grid-cols-2 gap-2 items-center">
                <input name="irb_codes[]" type="text" placeholder="EC2025" class="w-full border p-2 rounded">
                <input name="irb_sources[]" type="text" placeholder="ชื่อหน่วยงาน" class="w-full border p-2 rounded">
            </div>
        </div>
        <button type="button" onclick="addIRBCode()" class="mb-6 px-3 py-1 bg-red-600 text-white rounded">+ เพิ่มรหัส IRB</button>

        {{-- FTE --}}
        <hr class="my-6 border-t-2 border-gray-300">
        <h3 class="font-semibold text-lg text-blue-700 mb-2">D: รายชื่อผู้มีส่วนร่วม (FTE)</h3>
        <div id="fte-container" class="space-y-2 mb-4">
            <div id="fte-0" class="grid grid-cols-3 gap-4">
                <input name="fte_roles[0][role_group]" type="text" placeholder="กลุ่ม" class="w-full border p-2 rounded">
                <input name="fte_roles[0][name]" type="text" placeholder="ชื่อ" class="w-full border p-2 rounded">
                <input name="fte_roles[0][fte_value]" type="number" step="0.01" placeholder="FTE" class="w-full border p-2 rounded">
            </div>
        </div>
        <button type="button" onclick="addFTERow()" class="mb-6 px-3 py-1 bg-blue-600 text-white rounded">+ เพิ่มผู้มีส่วนร่วม</button>

        {{-- Output --}}
        <hr class="my-6 border-t-2 border-gray-300">
        <h3 class="font-semibold text-lg text-blue-700 mb-2">E: ผลงานวิจัย (Output)</h3>
        <div id="output-container" class="space-y-2 mb-4">
            <div id="output-0" class="grid grid-cols-3 gap-4">
                <input name="outputs[0][type]" type="text" placeholder="ประเภท" class="w-full border p-2 rounded">
                <input name="outputs[0][title]" type="text" placeholder="ชื่อผลงาน" class="w-full border p-2 rounded">
                <input name="outputs[0][year]" type="text" placeholder="ปี" class="w-full border p-2 rounded">
            </div>
        </div>
        <button type="button" onclick="addOutputRow()" class="mb-6 px-3 py-1 bg-green-600 text-white rounded">+ เพิ่ม Output</button>

        {{-- ยุทธศาสตร์ --}}
        <hr class="my-6 border-t-2 border-gray-300">
        <h3 class="font-semibold text-lg text-blue-700 mb-2">F: ยุทธศาสตร์</h3>
        <table class="w-full table-auto text-sm mb-6">
            <thead>
                <tr class="bg-gray-100">
                    <th class="px-3 py-2 text-left">หมวด</th>
                    <th class="px-3 py-2 text-left">รายการ</th>
                    <th class="px-3 py-2 text-center">มี</th>
                </tr>
            </thead>
            <tbody>
                @foreach(range(1, 5) as $sKey)
                    <tr class="border-t">
                        <td class="px-3 py-2 font-bold" colspan="3">AA50{{ $sKey }} - Strategy {{ $sKey }}</td>
                    </tr>
                    @foreach(range(1, 5) as $iKey)
                        <tr class="border-t">
                            <td class="px-3 py-2">ยุทธศาสตร์ {{ $sKey }}.{{ $iKey }}</td>
                            <td class="px-3 py-2">รายการ {{ $sKey }}.{{ $iKey }}</td>
                            <td class="px-3 py-2 text-center">
                                <input type="checkbox" name="strategy_{{ $sKey }}_{{ $iKey }}" class="mr-1">
                            </td>
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>

        {{-- Submit --}}
        <div class="mt-6">
            <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded hover:bg-indigo-700">
                💾 บันทึกข้อมูล
            </button>
        </div>
    </form>
</div>

{{-- Script --}}
<script>
function removeRow(id) {
    document.getElementById(id)?.remove();
}

let rCodeIndex = 1;
function addRCode() {
    const id = `r-${rCodeIndex++}`;
    document.getElementById('r-code-container').insertAdjacentHTML('beforeend', `
        <div id="${id}" class="flex gap-2">
            <input name="r_codes[]" type="text" placeholder="R:xxx" class="w-full border p-2 rounded">
            <button type="button" onclick="removeRow('${id}')" class="text-red-600">✕</button>
        </div>
    `);
}

let irbCodeIndex = 1;
function addIRBCode() {
    const id = `irb-${irbCodeIndex++}`;
    document.getElementById('irb-code-container').insertAdjacentHTML('beforeend', `
        <div id="${id}" class="grid grid-cols-2 gap-2 items-center">
            <input name="irb_codes[]" type="text" placeholder="EC2025" class="w-full border p-2 rounded">
            <div class="flex gap-2">
                <input name="irb_sources[]" type="text" placeholder="ชื่อหน่วยงาน" class="w-full border p-2 rounded">
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
            <input name="fte_roles[${fteIndex}][role_group]" type="text" placeholder="กลุ่ม" class="w-full border p-2 rounded">
            <input name="fte_roles[${fteIndex}][name]" type="text" placeholder="ชื่อ" class="w-full border p-2 rounded">
            <div class="flex gap-2">
                <input name="fte_roles[${fteIndex}][fte_value]" type="number" step="0.01" placeholder="FTE" class="w-full border p-2 rounded">
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
            <input name="outputs[${outputIndex}][type]" type="text" placeholder="ประเภท" class="w-full border p-2 rounded">
            <input name="outputs[${outputIndex}][title]" type="text" placeholder="ชื่อผลงาน" class="w-full border p-2 rounded">
            <div class="flex gap-2">
                <input name="outputs[${outputIndex}][year]" type="text" placeholder="ปี" class="w-full border p-2 rounded">
                <button type="button" onclick="removeRow('${id}')" class="text-red-600">✕</button>
            </div>
        </div>
    `);
}
</script>
@endsection
