@extends('layouts.app')
@section('content')
<div class="max-w-5xl mx-auto mt-10">
    <div class="flex items-center justify-between mb-6">
        <a href="{{ route('projects.show', $project->id) }}" class="inline-flex items-center text-blue-700 hover:underline font-medium">
            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
            ย้อนกลับไปหน้ารายละเอียดโครงการ
        </a>
        <div>
            <span class="font-bold text-xl text-gray-700">Log</span>
            <span class="ml-2 text-lg text-blue-700">{{ $project->project_name_th }}</span>
        </div>
    </div>

    <div class="overflow-x-auto bg-white shadow-lg rounded-lg">
        <table class="min-w-full table-auto border">
            <thead class="bg-gray-50 text-sm uppercase">
                <tr>
                    <th class="border px-4 py-2 text-left">วันเวลา</th>
                    <th class="border px-4 py-2 text-left">โดย</th>
                    <th class="border px-4 py-2 text-left">เหตุการณ์</th>
                    <th class="border px-4 py-2"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($groupedLogs ?? [] as $group)
                    @foreach($group as $log)
                        @php
                            $showMeta = $loop->first;
                            $thaiDate = \Carbon\Carbon::parse($log->created_at)->addYears(543)->format('d/m/Y H:i:s');
                            $userName = $log->user?->prefix . $log->user?->name;
                        @endphp
                        <tr class="hover:bg-gray-100 transition">
                            <td class="border px-4 py-2 text-xs text-gray-600 whitespace-nowrap">
                                @if($showMeta)
                                    {{ $thaiDate }}
                                @else
                                    <span class="text-gray-300">—</span>
                                @endif
                            </td>
                            <td class="border px-4 py-2">
                                @if($showMeta)
                                    {{ $userName }}
                                @else
                                    <span class="text-gray-300">—</span>
                                @endif
                            </td>
                            <td class="border px-4 py-2">
                                <span class="inline-block bg-blue-100 text-blue-700 px-2 py-0.5 rounded">
                                    {{ $log->entity_label }}: {{ $log->action_label }}
                                </span>
                            </td>
                            <td class="border px-4 py-2 text-center">
                                <button type="button" class="text-blue-700 hover:underline text-sm toggle-details" data-target="log-details-{{ $log->id }}">
                                    ดูรายละเอียด
                                </button>
                                <form action="{{ route('project_logs.destroy', $log->id) }}" method="POST" class="inline delete-log-form">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800 font-bold" title="ลบ log">
                                        🗑️
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <tr id="log-details-{{ $log->id }}" class="hidden">
                            <td colspan="4" class="border px-4 py-3 text-xs bg-gray-50">
                                @include('project_logs._changes', ['log' => $log])
                            </td>
                        </tr>
                    @endforeach
                @empty
                    <tr><td colspan="4" class="text-center py-8 text-gray-400">ไม่มี log ในระบบ</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- SweetAlert confirm --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.toggle-details').forEach(function(button) {
        button.addEventListener('click', function() {
            var targetId = button.getAttribute('data-target');
            var row = document.getElementById(targetId);
            if (row) {
                row.classList.toggle('hidden');
            }
        });
    });
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
