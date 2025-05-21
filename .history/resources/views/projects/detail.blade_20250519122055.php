@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto mt-6 bg-white shadow-md rounded p-6">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-2xl font-bold text-gray-800">🔍 รายละเอียดโครงการ</h2>
        <div class="space-x-2">
            <a href="{{ route('projects.edit', $project->id) }}" class="bg-yellow-500 text-white px-4 py-2 rounded">✏️ แก้ไข</a>
            <a href="{{ route('projects.pdf', $project->id) }}" target="_blank" class="bg-green-600 text-white px-4 py-2 rounded">🖨️ พิมพ์ PDF</a>
        </div>
    </div>

    <h3 class="font-semibold text-lg text-blue-700 mb-2">A: ข้อมูลโครงการ</h3>
    <ul class="mb-4">
        <li><strong>ชื่อโครงการ (TH):</strong> {{ $project->project_name_th }}</li>
        <li><strong>ชื่อโครงการ (EN):</strong> {{ $project->project_name_en }}</li>
        <li><strong>ประเภท:</strong> {{ $project->project_type }}</li>
        <li><strong>Theme:</strong> {{ $project->theme }}</li>
        <li><strong>Shortcut:</strong> <a href="{{ $project->shortcut_link }}" class="text-blue-600 underline" target="_blank">{{ $project->shortcut_link }}</a></li>
        <li><strong>ขนาด:</strong> {{ $project->size }}</li>
    </ul>

    <h3 class="font-semibold text-lg text-blue-700 mb-2">B: รายชื่อผู้ร่วมวิจัย (COPI)</h3>
    <table class="w-full table-auto border mb-6">
        <thead>
            <tr class="bg-gray-100">
                <th class="border px-2 py-1">ชื่อ</th>
                <th class="border px-2 py-1">สังกัด</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($project->copis as $copi)
            <tr>
                <td class="border px-2 py-1">{{ $copi->name }}</td>
                <td class="border px-2 py-1">{{ $copi->affiliation }}</td>
            </tr>
            @empty
            <tr><td colspan="2" class="text-center py-2">ไม่มีข้อมูล</td></tr>
            @endforelse
        </tbody>
    </table>

    <h3 class="font-semibold text-lg text-blue-700 mb-2">C: รหัส IRB</h3>
    <table class="w-full table-auto border mb-6">
        <thead>
            <tr class="bg-gray-100">
                <th class="border px-2 py-1">รหัส</th>
                <th class="border px-2 py-1">หน่วยงาน</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($project->irbs as $irb)
            <tr>
                <td class="border px-2 py-1">{{ $irb->code }}</td>
                <td class="border px-2 py-1">{{ $irb->source }}</td>
            </tr>
            @empty
            <tr><td colspan="2" class="text-center py-2">ไม่มีข้อมูล</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
