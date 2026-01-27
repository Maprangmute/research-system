@extends('layouts.app')
@section('content')
<div class="max-w-xl mx-auto mt-6 px-4 space-y-4">
    <div class="flex flex-col gap-2">
        <a href="javascript:history.back()" class="inline-flex items-center text-blue-700 hover:underline font-medium">
            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
            ย้อนกลับ
        </a>
        <h1 class="text-xl font-bold text-gray-800">รายละเอียด Log</h1>
    </div>

    @php
        $thaiDate = \Carbon\Carbon::parse($log->created_at)->addYears(543)->format('d/m/Y H:i:s');
        $projectName = $log->project?->project_name_th ?? $log->project_name_th ?? '-';
        $userName = $log->user?->prefix . $log->user?->name;
    @endphp

    <div class="bg-white border rounded-xl shadow-sm p-4 space-y-2">
        <div class="text-xs text-gray-500">{{ $thaiDate }}</div>
        <div class="text-sm font-semibold text-gray-800">
            {{ $log->entity_label }}: {{ $log->action_label }}
        </div>
        <div class="text-sm text-gray-600">โครงการ: {{ $projectName }}</div>
        <div class="text-sm text-gray-600">โดย: {{ $userName }}</div>
        <div class="pt-2">
            <span class="inline-block {{ $log->action_color_class }} px-2 py-0.5 rounded text-xs">
                {{ $log->entity_label }}: {{ $log->action_label }}
            </span>
        </div>
    </div>

    <div class="bg-white border rounded-xl shadow-sm p-4">
        <div class="text-sm font-semibold text-gray-800 mb-2">รายละเอียดการเปลี่ยนแปลง</div>
        <div class="text-xs text-gray-700">
            @include('project_logs._changes', ['log' => $log])
        </div>
    </div>
</div>
@endsection
