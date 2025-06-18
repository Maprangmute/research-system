@extends('layouts.app')
@section('content')
<div class="max-w-6xl mx-auto mt-10">
    <div class="flex items-center justify-between mb-6">
        <a href="javascript:history.back()" class="inline-flex items-center text-blue-700 hover:underline font-medium">
            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
            ย้อนกลับ
        </a>
        
    </div>
<h1 class="text-2xl font-bold text-gray-800 mb-2">📜 ประวัติการเปลี่ยนแปลงโครงการ (ทั้งหมด)</h1>
    <div class="overflow-x-auto bg-white shadow-lg rounded-lg">
        <table class="min-w-full table-auto border">
            <thead class="bg-gray-50 text-sm uppercase">
                <tr>
                    <th class="border px-4 py-2 text-left">วันเวลา</th>
                    <th class="border px-4 py-2 text-left">ชื่อโครงการ</th>
                    <th class="border px-4 py-2 text-left">โดย</th>
                    <th class="border px-4 py-2 text-left">เหตุการณ์</th>
                    <th class="border px-4 py-2 text-left">รายละเอียด</th>
                    <th class="border px-4 py-2"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    <tr class="hover:bg-gray-100 transition">
                        <td class="border px-4 py-2 text-xs text-gray-600 whitespace-nowrap">
                            {{ \Carbon\Carbon::parse($log->created_at)->format('d/m/Y H:i') }}
                        </td>
                        <td class="border px-4 py-2">
                            <a href="{{ route('project_logs.project', $log->project_id) }}" class="text-blue-600 hover:underline">
                                {{ $log->project?->project_name_th ?? '-' }}
                            </a>
                        </td>
                        <td class="border px-4 py-2">
                            {{ $log->user?->prefix }}{{ $log->user?->name }}
                        </td>
                        <td class="border px-4 py-2">
                            <span class="inline-block bg-blue-100 text-blue-700 px-2 py-0.5 rounded">
                                {{ ucfirst($log->action) }}
                            </span>
                        </td>
                        <td class="border px-4 py-2 text-xs max-w-xs">
                            @if($log->changes)
                                <details>
                                    <summary class="cursor-pointer text-gray-600 select-none">ดูรายละเอียด</summary>
                                    <div class="mt-2">
                                    @foreach($log->changes as $k => $v)
                                        <div><span class="text-gray-700 font-medium">{{ $k }}</span>: <span class="text-gray-600">{{ is_array($v) ? json_encode($v, JSON_UNESCAPED_UNICODE) : $v }}</span></div>
                                    @endforeach
                                    </div>
                                </details>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="border px-4 py-2 text-center">
                            <form action="{{ route('project_logs.destroy', $log->id) }}" method="POST" class="inline delete-log-form">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800 font-bold" title="ลบ log">
                                    🗑️
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center py-8 text-gray-400">ไม่มี log ในระบบ</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">
        {{ $logs->links() }}
    </div>
</div>

{{-- SweetAlert confirm --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.delete-log-form').forEach(function(form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            if(confirm('ยืนยันการลบ log นี้?')) {
                form.submit();
            }
        });
    });
});
</script>
@endsection
