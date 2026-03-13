@extends('layouts.app')
@section('content')
<style>
    .log-detail-shell {
        border: 1px solid #bdeaf5;
        border-radius: 24px;
        background: linear-gradient(135deg, #f3fdff 0%, #ffffff 52%, #f8fcff 100%);
        box-shadow: 0 14px 30px rgba(15, 23, 42, 0.07);
    }
    .log-detail-card {
        border: 1px solid #dbe7f5;
        border-radius: 18px;
        background: #ffffff;
        box-shadow: 0 10px 22px rgba(15, 23, 42, 0.05);
    }
</style>
<div class="max-w-4xl mx-auto mt-10 px-4">
    <div class="log-detail-shell p-6">
        <div class="flex items-center justify-between mb-5">
            <a href="{{ route('project_logs.index') }}" class="inline-flex items-center text-sky-700 hover:underline font-medium">
                <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                ย้อนกลับไปหน้ารายการ Log
            </a>
        </div>

        @php
            $thaiDate = \Carbon\Carbon::parse($log->created_at)->setTimezone('Asia/Bangkok')->addYears(543)->format('d/m/Y H:i:s');
            $projectName = $log->project?->project_name_th ?? $log->project_name_th ?? '-';
            $userName = $log->user?->prefix . $log->user?->name;
        @endphp

        <div class="log-detail-card p-6">
            <div class="flex flex-col gap-2 mb-4">
                <div class="text-xs text-slate-500">{{ $thaiDate }}</div>
                <div class="text-xl font-semibold text-slate-800">
                    {{ $log->entity_label }}: {{ $log->action_label }}
                </div>
                <div class="text-sm text-slate-600 mt-1">โครงการ: {{ $projectName }}</div>
                <div class="text-sm text-slate-600">โดย: {{ $userName }}</div>
            </div>
            <div class="flex flex-wrap items-center gap-2 mb-4">
                <span class="inline-block {{ $log->action_color_class }} px-3 py-1 rounded text-xs font-semibold">
                    {{ $log->entity_label }}: {{ $log->action_label }}
                </span>
                @if($log->project_id)
                    <a href="{{ route('project_logs.project', $log->project_id) }}"
                        class="inline-flex items-center rounded-lg border border-sky-200 bg-sky-50 px-3 py-1.5 text-sky-700 hover:bg-sky-100 text-sm font-medium">
                        ดู Log ทั้งโครงการ
                    </a>
                @endif
            </div>

            <div class="border-t border-slate-200 pt-4">
                <div class="text-sm font-semibold text-slate-800 mb-2">รายละเอียดการเปลี่ยนแปลง</div>
                <div class="text-sm text-slate-700 leading-7">
                    @include('project_logs._changes', ['log' => $log])
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
