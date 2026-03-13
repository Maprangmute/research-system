@extends('layouts.app')
@section('content')
<style>
    .log-page-shell {
        border: 1px solid #bdeaf5;
        border-radius: 24px;
        background: linear-gradient(135deg, #f3fdff 0%, #ffffff 52%, #f8fcff 100%);
        box-shadow: 0 14px 30px rgba(15, 23, 42, 0.07);
    }
    .log-row-card {
        border: 1px solid #dbe7f5;
        border-radius: 18px;
        background: #ffffff;
        box-shadow: 0 10px 22px rgba(15, 23, 42, 0.05);
    }
    .log-soft-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        border-radius: 9999px;
        border: 1px solid #bae6fd;
        background: #f0f9ff;
        color: #0369a1;
        padding: 0.28rem 0.65rem;
        font-size: 0.72rem;
        font-weight: 700;
    }
    .log-action-btn {
        border-radius: 12px;
        padding: 0.4rem 0.8rem;
        font-size: 0.78rem;
        font-weight: 700;
        transition: all 0.2s ease;
    }
    .log-action-btn:hover { transform: translateY(-1px); }
</style>
<div class="max-w-5xl mx-auto mt-10 px-4">
    <div class="log-page-shell p-5 md:p-6">
        <div class="flex flex-col gap-2 mb-6">
            <a href="{{ route('project_logs.index') }}" class="inline-flex items-center text-sky-700 hover:underline font-medium w-fit">
                <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                ย้อนกลับไปหน้ารายการ Log
            </a>
            <div class="text-2xl font-bold text-slate-800">Log: {{ $project->project_name_th }}</div>
            <div class="text-sm text-slate-500">ประวัติการแก้ไขทั้งหมดของโครงการนี้</div>
        </div>

        <div class="space-y-4">
            @forelse($groupedLogs ?? [] as $group)
                @php
                    $first = $group->first();
                    $thaiDate = \Carbon\Carbon::parse($first->created_at)->setTimezone('Asia/Bangkok')->addYears(543)->format('d/m/Y H:i:s');
                    $userName = $first->user?->prefix . $first->user?->name;
                    $groupId = 'log-group-' . $first->id;
                @endphp
                <div class="log-row-card p-4 md:p-5">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                        <div>
                            <div class="text-xs text-slate-500">{{ $thaiDate }}</div>
                            <div class="text-sm text-slate-700 mt-0.5">โดย {{ $userName }}</div>
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button" class="log-action-btn bg-sky-50 text-sky-700 border border-sky-200 hover:bg-sky-100 toggle-details" data-target="{{ $groupId }}">
                                ดูรายละเอียด
                            </button>
                            <form action="{{ route('project_logs.destroy', $first->id) }}" method="POST" class="inline delete-log-form">
                                @csrf @method('DELETE')
                                <button type="submit" class="log-action-btn bg-rose-600 text-white hover:bg-rose-700" title="ลบ log">
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
                            <div class="border border-slate-200 rounded-xl p-3 bg-slate-50/70">
                                <div class="font-semibold text-slate-800 text-sm mb-2">
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
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.toggle-details').forEach(function(button) {
        button.addEventListener('click', function() {
            var targetId = button.getAttribute('data-target');
            var panel = document.getElementById(targetId);
            if (!panel) return;
            panel.classList.toggle('hidden');
            button.textContent = panel.classList.contains('hidden') ? 'ดูรายละเอียด' : 'ซ่อนรายละเอียด';
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
