@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto mt-8 px-2">

    @if(session('success'))
        <div class="mb-4 text-green-600 font-semibold text-center">
            ✅ {{ session('success') }}
        </div>
    @endif

    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between mb-5 gap-2">
        <div class="min-w-0">
            <a href="{{ route('projects.show', $project->id) }}" class="text-blue-600 hover:underline text-sm block mb-3">
                ← กลับหน้าโครงการ
            </a>
            <div class="flex items-center gap-2 mb-2">
                <span class="text-xl font-extrabold text-black-500 drop-shadow-sm">
                    <svg class="inline h-7 w-7 text-blue-400 -mt-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"></path></svg>
                    {{ $project->project_name_th }}
                </span>
            </div>
            <div class="text-gray-500 text-base font-light break-words whitespace-pre-line max-w-full sm:max-w-4xl mt-1" style="word-break: break-word;">
                <span class="font-medium">Progress Reports</span>
            </div>
        </div>
      <a href="{{ route('projects.progress_reports.create', $project->id) }}"
   class="bg-gradient-to-r from-blue-500 to-blue-700 hover:from-blue-600 hover:to-blue-800 text-white px-5 py-2 rounded-xl shadow-lg transition text-sm font-semibold flex items-center gap-1 w-fit self-end">
    <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"></path>
    </svg>
    เพิ่ม Progress
</a>

    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full bg-white rounded-2xl shadow-lg ring-1 ring-slate-100 border-separate border-spacing-0">
    <thead class="bg-blue-50 sticky top-0 z-10">
        <tr>
            <th class="py-3 px-4 text-center font-semibold rounded-tl-2xl w-12">#</th>
            <th class="py-3 px-4 text-left font-semibold">ชื่อผู้ส่ง</th>
            <th class="py-3 px-4 text-left font-semibold">วันที่ส่ง</th>
            <th class="py-3 px-4 text-left font-semibold">รายละเอียดไฟล์</th>
            <th class="py-3 px-4 text-center font-semibold rounded-tr-2xl w-40">ดำเนินการ</th>
        </tr>
    </thead>
    <tbody>
        @forelse($progressReports as $i => $report)
            <tr class="even:bg-slate-50 hover:bg-blue-100/40 transition">
                <td class="py-3 px-4 text-center">{{ $i+1 }}</td>
                <td class="py-3 px-4 text-left break-all max-w-xs">{{ $report->submitter_name }}</td>
                <td class="py-3 px-4 text-left whitespace-nowrap">
                    {{ \Carbon\Carbon::parse($report->submit_date)->locale('th')->translatedFormat('j F ') . (\Carbon\Carbon::parse($report->submit_date)->year + 543) }}
                </td>
                <td class="py-3 px-4 text-left break-all max-w-sm" title="{{ $report->file_info }}">
                    {{ $report->file_info ?? '-' }}
                </td>
                <td class="py-3 px-4 flex flex-wrap gap-2 justify-center text-center">
                    <a href="{{ route('projects.progress_reports.edit', [$project->id, $report->id]) }}"
                        class="inline-flex items-center gap-1 px-3 py-1 rounded-lg bg-yellow-50 text-yellow-700 border border-yellow-200 hover:bg-yellow-400 hover:text-white transition text-sm font-semibold">แก้ไข</a>
                    <form action="{{ route('projects.progress_reports.destroy', [$project->id, $report->id]) }}" method="POST" onsubmit="return confirm('ยืนยันการลบ?')" class="inline">
                        @csrf @method('DELETE')
                        <button type="submit" class="inline-flex items-center gap-1 px-3 py-1 rounded-lg bg-red-50 text-red-600 border border-red-200 hover:bg-red-600 hover:text-white transition text-sm font-semibold">ลบ</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="py-8 text-center text-gray-400">ไม่มี Progress Report ในโครงการนี้</td>
            </tr>
        @endforelse
    </tbody>
</table>
    </div>
</div>
@endsection
