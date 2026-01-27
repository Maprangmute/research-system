@extends('layouts.app')
@section('content')
<div class="max-w-5xl mx-auto mt-10 px-4">
    <div class="flex flex-col gap-2 mb-6">
        <a href="{{ route('project_logs.index') }}" class="inline-flex items-center text-blue-700 hover:underline font-medium">
            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
            ย้อนกลับไปหน้ารายการ Log
        </a>
        <div class="text-2xl font-bold text-gray-800">Log: {{ $project->project_name_th }}</div>
    </div>

    <div class="space-y-4">
        @forelse($groupedLogs ?? [] as $group)
            @php
                $first = $group->first();
                $thaiDate = \Carbon\Carbon::parse($first->created_at)->addYears(543)->format('d/m/Y H:i:s');
                $userName = $first->user?->prefix . $first->user?->name;
                $groupId = 'log-group-' . $first->id;
            @endphp
            <div class="bg-white border rounded-xl shadow-sm p-4">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2">
                    <div>
                        <div class="text-xs text-gray-500">{{ $thaiDate }}</div>
                        <div class="text-sm text-gray-700">โดย {{ $userName }}</div>
                    </div>
                    <div class="flex items-center gap-3">
                        <button type="button" class="text-blue-700 hover:underline text-sm toggle-details" data-target="{{ $groupId }}">
                            ดูรายละเอียด
                        </button>
                        <form action="{{ route('project_logs.destroy', $first->id) }}" method="POST" class="inline delete-log-form">
                            @csrf @method('DELETE')
                            <button type="submit" class="px-3 py-1 rounded bg-red-600 text-white text-xs hover:bg-red-700" title="ลบ log">
                                ลบ
                            </button>
                        </form>
                    </div>
                </div>
                <div class="flex flex-wrap gap-2 mt-3">
                    @foreach($group as $log)
                        <span class="inline-block {{ $log->action_color_class }} px-2 py-0.5 rounded text-xs">
                            {{ $log->entity_label }}: {{ $log->action_label }}
                        </span>
                    @endforeach
                </div>
                <div id="{{ $groupId }}" class="hidden mt-4 space-y-3">
                    @foreach($group as $log)
                        <div class="border rounded-lg p-3 bg-gray-50">
                            <div class="font-semibold text-gray-800 text-sm mb-2">
                                {{ $log->entity_label }}: {{ $log->action_label }}
                            </div>
                            <div class="text-xs text-gray-700">
                                @include('project_logs._changes', ['log' => $log])
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="text-center py-8 text-gray-400">ไม่มี log ในระบบ</div>
        @endforelse
    </div>
</div>

{{-- SweetAlert confirm --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.toggle-details').forEach(function(button) {
        button.addEventListener('click', function() {
            var targetId = button.getAttribute('data-target');
            var panel = document.getElementById(targetId);
            if (panel) {
                panel.classList.toggle('hidden');
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
