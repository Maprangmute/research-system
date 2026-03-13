@extends('layouts.app')
@section('content')
<style>
    .log-mobile-shell {
        border: 1px solid #bdeaf5;
        border-radius: 20px;
        background: linear-gradient(135deg, #f3fdff 0%, #ffffff 56%, #f8fcff 100%);
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.07);
    }
    .log-mobile-card {
        border: 1px solid #dbe7f5;
        border-radius: 16px;
        background: #fff;
        box-shadow: 0 8px 18px rgba(15, 23, 42, 0.05);
    }
</style>
<div class="max-w-xl mx-auto mt-6 px-4 space-y-4">
    <div class="log-mobile-shell p-4 space-y-4">
        <div class="flex flex-col gap-2">
            <a href="{{ route('project_logs.index') }}" class="inline-flex items-center text-sky-700 hover:underline font-medium w-fit">
                <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                ย้อนกลับไปหน้ารายการ Log
            </a>
            <h1 class="text-xl font-bold text-slate-800">Log: {{ $project->project_name_th }}</h1>
            <p class="text-xs text-slate-500">ประวัติการแก้ไขของโครงการนี้</p>
        </div>

        @forelse($groupedLogs ?? [] as $group)
            @php
                $first = $group->first();
                $thaiDate = \Carbon\Carbon::parse($first->created_at)->setTimezone('Asia/Bangkok')->addYears(543)->format('d/m/Y H:i:s');
                $userName = $first->user?->prefix . $first->user?->name;
                $groupId = 'log-group-' . $first->id;
            @endphp
            <div class="log-mobile-card p-4 space-y-2">
                <div class="text-xs text-slate-500">{{ $thaiDate }}</div>
                <div class="text-sm text-slate-700">โดย {{ $userName }}</div>
                <div class="flex flex-wrap gap-2 pt-1">
                    @foreach($group as $log)
                        <span class="inline-block {{ $log->action_color_class }} px-2 py-0.5 rounded text-xs">
                            {{ $log->entity_label }}: {{ $log->action_label }}
                        </span>
                    @endforeach
                </div>
                <div class="flex items-center justify-between pt-1">
                    <button type="button" class="text-sky-700 text-sm font-semibold toggle-details" data-target="{{ $groupId }}">
                        ดูรายละเอียด
                    </button>
                    <form action="{{ route('project_logs.destroy', $first->id) }}" method="POST" class="inline delete-log-form">
                        @csrf @method('DELETE')
                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-rose-600 text-white text-xs font-semibold">ลบ</button>
                    </form>
                </div>
                <div id="{{ $groupId }}" class="hidden border-t pt-3 space-y-3">
                    @foreach($group as $log)
                        <div class="bg-slate-50 border border-slate-200 rounded-lg p-3">
                            <div class="font-semibold text-slate-800 mb-2 text-xs">
                                {{ $log->entity_label }}: {{ $log->action_label }}
                            </div>
                            <div class="text-xs text-slate-700 leading-6">
                                @include('project_logs._changes', ['log' => $log])
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="text-center py-10 text-slate-400">ไม่มี log ในระบบ</div>
        @endforelse
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.toggle-details').forEach(function(button) {
        button.addEventListener('click', function() {
            var targetId = button.getAttribute('data-target');
            var row = document.getElementById(targetId);
            if (!row) return;
            row.classList.toggle('hidden');
            button.textContent = row.classList.contains('hidden') ? 'ดูรายละเอียด' : 'ซ่อนรายละเอียด';
        });
    });
    document.querySelectorAll('.delete-log-form').forEach(function(form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            if (confirm('ยืนยันการลบ log นี้?')) {
                form.submit();
            }
        });
    });
});
</script>
@endsection
