@extends('layouts.app')
@section('content')
<div class="max-w-4xl mx-auto mt-10 px-4">
    <div class="flex items-center justify-between mb-6">
        <a href="{{ route('project_logs.index') }}" class="inline-flex items-center text-blue-700 hover:underline font-medium">
            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            ย้อนกลับไปหน้ารายการ Log
        </a>
    </div>

    @php
        $thaiDate = \Carbon\Carbon::parse($log->created_at)->addYears(543)->format('d/m/Y H:i:s');
        $projectName = $log->project?->project_name_th ?? $log->project_name_th ?? '-';
        $userName = $log->user?->prefix . $log->user?->name;
    @endphp

    <div class="bg-white border rounded-xl shadow-sm p-6">
        <div class="flex flex-col gap-2 mb-4">
            <div>
                <div class="text-xs text-gray-500">{{ $thaiDate }}</div>
                <div class="text-lg font-semibold text-gray-800">
                    {{ $log->entity_label }}: {{ $log->action_label }}
                </div>
                <div class="text-sm text-gray-600 mt-1">โครงการ: {{ $projectName }}</div>
                <div class="text-sm text-gray-600">โดย: {{ $userName }}</div>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <span class="inline-block {{ $log->action_color_class }} px-3 py-1 rounded text-xs font-semibold">
                    {{ $log->entity_label }}: {{ $log->action_label }}
                </span>
                @if($log->project_id)
                    <a href="{{ route('project_logs.project', $log->project_id) }}"
                        class="text-blue-700 hover:underline text-sm">
                        ดู Log ทั้งโครงการ
                    </a>
                @endif
            </div>
        </div>

        <div class="border-t pt-4">
            <div class="text-sm font-semibold text-gray-800 mb-2">รายละเอียดการเปลี่ยนแปลง</div>
            <div class="text-sm text-gray-700">
                @include('project_logs._changes', ['log' => $log])
            </div>
        </div>
    </div>
</div>
@endsection
