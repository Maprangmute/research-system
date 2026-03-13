@extends('layouts.app')
@section('content')
<style>
    .log-mobile-detail-shell {
        border: 1px solid #bdeaf5;
        border-radius: 20px;
        background: linear-gradient(135deg, #f3fdff 0%, #ffffff 56%, #f8fcff 100%);
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.07);
    }
    .log-mobile-detail-card {
        border: 1px solid #dbe7f5;
        border-radius: 16px;
        background: #ffffff;
        box-shadow: 0 8px 18px rgba(15, 23, 42, 0.05);
    }
</style>
<div class="max-w-xl mx-auto mt-6 px-4 space-y-4">
    <div class="log-mobile-detail-shell p-4 space-y-4">
        <div class="flex flex-col gap-2">
            <a href="javascript:history.back()" class="inline-flex items-center text-sky-700 hover:underline font-medium w-fit">
                <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                ย้อนกลับ
            </a>
            <h1 class="text-xl font-bold text-slate-800">รายละเอียด Log</h1>
        </div>

        @php
            $thaiDate = \Carbon\Carbon::parse($log->created_at)->setTimezone('Asia/Bangkok')->addYears(543)->format('d/m/Y H:i:s');
            $projectName = $log->project?->project_name_th ?? $log->project_name_th ?? '-';
            $userName = $log->user?->prefix . $log->user?->name;
        @endphp

        <div class="log-mobile-detail-card p-4 space-y-2">
            <div class="text-xs text-slate-500">{{ $thaiDate }}</div>
            <div class="text-sm font-semibold text-slate-800">
                {{ $log->entity_label }}: {{ $log->action_label }}
            </div>
            <div class="text-sm text-slate-600">โครงการ: {{ $projectName }}</div>
            <div class="text-sm text-slate-600">โดย: {{ $userName }}</div>
            <div class="pt-2 flex flex-wrap gap-2">
                <span class="inline-block {{ $log->action_color_class }} px-2 py-0.5 rounded text-xs">
                    {{ $log->entity_label }}: {{ $log->action_label }}
                </span>
                @if($log->project_id)
                    <a href="{{ route('project_logs.project', $log->project_id) }}"
                        class="inline-flex items-center rounded-lg border border-sky-200 bg-sky-50 px-2.5 py-1 text-[11px] text-sky-700 font-semibold">
                        ดู Log ทั้งโครงการ
                    </a>
                @endif
            </div>
        </div>

        <div class="log-mobile-detail-card p-4">
            <div class="text-sm font-semibold text-slate-800 mb-2">รายละเอียดการเปลี่ยนแปลง</div>
            <div class="text-xs text-slate-700 leading-6">
                @include('project_logs._changes', ['log' => $log])
            </div>
        </div>
    </div>
</div>
@endsection
