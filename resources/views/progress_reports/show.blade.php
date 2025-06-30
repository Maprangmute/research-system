@extends('layouts.app')

@section('content')
<div class="max-w-lg mx-auto mt-8 bg-white rounded shadow p-6">
    <h2 class="text-xl font-bold mb-4 text-gray-800">🔍 รายละเอียด Progress Report</h2>
    <div class="mb-3"><span class="font-medium">ชื่อผู้ส่ง:</span> {{ $report->submitter_name }}</div>
    <div class="mb-3"><span class="font-medium">วันที่ส่ง:</span>
        {{ \Carbon\Carbon::parse($report->submit_date)->locale('th')->isoFormat('D MMM YYYY') }}
    </div>
    <div class="mb-3"><span class="font-medium">รายละเอียดไฟล์ / ลิงก์:</span> 
        {{ $report->file_info ?: '-' }}
    </div>
    <div class="mb-3 text-xs text-gray-400">
        สร้างโดย: {{ $report->creator->name ?? '-' }},
        อัปเดตโดย: {{ $report->updater->name ?? '-' }},
        ล่าสุด: {{ $report->updated_at ? $report->updated_at->diffForHumans() : '-' }}
    </div>

    <div class="flex flex-wrap gap-2 mt-4">
        <a href="{{ route('projects.progress_reports.edit', [$project->id, $report->id]) }}"
           class="bg-yellow-200 hover:bg-yellow-300 text-yellow-800 px-4 py-2 rounded shadow-sm">✏️ แก้ไข</a>
        <form action="{{ route('projects.progress_reports.destroy', [$project->id, $report->id]) }}"
              method="POST" onsubmit="return confirm('ยืนยันการลบ?')" class="inline">
            @csrf @method('DELETE')
            <button type="submit" class="bg-red-200 hover:bg-red-300 text-red-800 px-4 py-2 rounded shadow-sm">ลบ</button>
        </form>
        <a href="{{ route('projects.progress_reports.index', $project->id) }}" class="text-blue-500 hover:underline">← กลับหน้ารายการ</a>
    </div>
</div>
@endsection
