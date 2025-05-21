@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto mt-6 bg-white shadow-md rounded p-6">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-2xl font-bold text-gray-800">🔍 รายละเอียดโครงการ</h2>
        <div class="space-x-2">
            <a href="{{ route('projects.edit', $project->id) }}" class="bg-yellow-500 text-white px-4 py-2 rounded">✏️ แก้ไข</a>
            <a href="{{ route('projects.pdf', $project->id) }}" target="_blank" class="bg-green-600 text-white px-4 py-2 rounded">🖨️ พิมพ์ PDF</a>
            <form action="{{ route('projects.destroy', $project->id) }}" method="POST" class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" onclick="return confirm('คุณแน่ใจหรือไม่ว่าต้องการลบโครงการนี้?')" class="bg-red-600 text-white px-4 py-2 rounded">🗑️ ลบ</button>
            </form>
        </div>
    </div>

    <h3 class="font-semibold text-lg text-blue-700 mb-2">A: ข้อมูลโครงการ</h3>
    <ul class="mb-4">
        <li><strong>ชื่อโครงการ (TH):</strong> {{ $project->project_name_th }}</li>
        <li><strong>ชื่อโครงการ (EN):</strong> {{ $project->project_name_en }}</li>
        <li><strong>ประเภท:</strong> {{ $project->project_type }}</li>
        <li><strong>Theme:</strong> {{ $project->theme }}</li>
        <li><strong>Shortcut:</strong> 
            @if($project->shortcut_link)
                <a href="{{ $project->shortcut_link }}" class="text-blue-600 underline" target="_blank">{{ $project->shortcut_link }}</a>
            @else
                -
            @endif
        </li>
        <li><strong>ขนาด:</strong> {{ $project->size ?? '-' }}</li>
        <li><strong>รหัสโครงการ R:</strong> {{ $project->r_code ?? '-' }}</li>
        <li><strong>ปีโปรเจค:</strong> {{ $project->start_year ?? '-' }}</li>
    </ul>
    

    <h3 class="font-semibold text-lg text-blue-700 mb-2">B: ข้อมูลแหล่งทุน</h3>
<ul class="mb-4">
    <li><strong>DA102 - ประเภทแหล่งทุน:</strong> {{ $project->funding_type ?? '-' }}</li>
    <li><strong>DA103 - ชื่อแหล่งทุน:</strong> {{ $project->funding_source ?? '-' }}</li>
    <li><strong>DA104 - รัฐบาล / เอกชน / มูลนิธิ:</strong> {{ $project->funding_org_type ?? '-' }}</li>
    <li><strong>DA105 - ชื่อทุน:</strong> {{ $project->funding_name ?? '-' }}</li>
    <li><strong>DA106 - งบประมาณ (บาท):</strong> {{ number_format($project->budget, 2) }}</li>
</ul>


    <h3 class="font-semibold text-lg text-blue-700 mb-2">C: รายชื่อผู้ร่วมวิจัย (COPI)</h3>
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

    <h3 class="font-semibold text-lg text-blue-700 mb-2">D: รหัส IRB</h3>
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

    <h3 class="font-semibold text-lg text-blue-700 mb-2">E: รายชื่อผู้มีส่วนร่วม (FTE)</h3>
    <table class="w-full table-auto border mb-6">
        <thead>
            <tr class="bg-gray-100">
                <th class="border px-2 py-1">กลุ่ม</th>
                <th class="border px-2 py-1">ชื่อ</th>
                <th class="border px-2 py-1">FTE</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($project->fteRoles as $fte)
            <tr>
                <td class="border px-2 py-1">{{ $fte->role_group }}</td>
                <td class="border px-2 py-1">{{ $fte->name }}</td>
                <td class="border px-2 py-1">{{ $fte->fte_value }}</td>
            </tr>
            @empty
            <tr><td colspan="3" class="text-center py-2">ไม่มีข้อมูล</td></tr>
            @endforelse
        </tbody>
    </table>

    <h3 class="font-semibold text-lg text-blue-700 mb-2">F: ผลงานวิจัย (Outputs)</h3>
    <table class="w-full table-auto border mb-6">
        <thead>
            <tr class="bg-gray-100">
                <th class="border px-2 py-1">ประเภท</th>
                <th class="border px-2 py-1">ชื่อผลงาน</th>
                <th class="border px-2 py-1">ปี</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($project->outputs as $output)
            <tr>
                <td class="border px-2 py-1">{{ $output->type }}</td>
                <td class="border px-2 py-1">{{ $output->title }}</td>
                <td class="border px-2 py-1">{{ $output->year }}</td>
            </tr>
            @empty
            <tr><td colspan="3" class="text-center py-2">ไม่มีข้อมูล</td></tr>
            @endforelse
        </tbody>
    </table>

    <h3 class="font-semibold text-lg text-blue-700 mb-2">G: ยุทธศาสตร์</h3>
    <ul class="list-disc list-inside space-y-1">
        @for ($i = 1; $i <= 5; $i++)
            @for ($j = 1; $j <= 5; $j++)
                @php $field = "strategy_{$i}_{$j}"; @endphp
                @if (!empty($project->$field))
                    <li>{{ $field }}</li>
                @endif
            @endfor
        @endfor
    </ul>
</div>
@endsection
