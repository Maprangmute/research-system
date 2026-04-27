@extends('layouts.app')

@section('content')
<style>
    .audit-detail-shell {
        border: 1px solid #cfe5ff;
        border-radius: 24px;
        background: linear-gradient(135deg, #f8fbff 0%, #ffffff 55%, #f5fbff 100%);
        box-shadow: 0 14px 32px rgba(15, 23, 42, 0.07);
    }
    .audit-detail-card {
        border: 1px solid #dbe7f5;
        border-radius: 18px;
        background: #ffffff;
        box-shadow: 0 8px 22px rgba(15, 23, 42, 0.05);
    }
    .audit-label {
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #64748b;
    }
    .audit-value {
        color: #0f172a;
        font-size: 0.95rem;
        line-height: 1.7;
    }
</style>

<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="audit-detail-shell p-6">
        <div class="mb-5 flex items-center justify-between gap-4">
            <a href="{{ route('back_office_action_logs.index') }}" class="inline-flex items-center text-sky-700 hover:underline font-medium">
                <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                ย้อนกลับไปหน้ารายการ Log
            </a>
        </div>

        <div class="audit-detail-card p-6">
            <div class="mb-5 flex flex-wrap items-start justify-between gap-4">
                <div>
                    <div class="text-xs text-slate-500">
                        {{ optional($log->created_at)->setTimezone('Asia/Bangkok')->format('d/m/Y H:i:s') }}
                    </div>
                    <div class="mt-1 text-2xl font-bold text-slate-900">Back Office Action Log</div>
                    <div class="mt-2 text-sm text-slate-600">
                        {{ trim(($log->user->prefix ?? '') . ' ' . ($log->user->name ?? '')) ?: 'ไม่ทราบผู้ใช้' }}
                        @if ($log->user?->email)
                            <span class="text-slate-400">· {{ $log->user->email }}</span>
                        @endif
                    </div>
                </div>
                <span class="inline-flex rounded-full bg-sky-50 px-3 py-1.5 text-sm font-semibold text-sky-700">
                    {{ $log->action }}
                </span>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div class="audit-detail-card p-4">
                    <div class="audit-label">หัวข้อ</div>
                    <div class="audit-value mt-1">{{ $log->subject_label ?: '-' }}</div>
                </div>
                <div class="audit-detail-card p-4">
                    <div class="audit-label">ประเภท</div>
                    <div class="audit-value mt-1">{{ $log->subject_type ?: '-' }}</div>
                </div>
                <div class="audit-detail-card p-4">
                    <div class="audit-label">Task</div>
                    <div class="audit-value mt-1">{{ $log->task_title ?: '-' }}</div>
                </div>
                <div class="audit-detail-card p-4">
                    <div class="audit-label">Person</div>
                    <div class="audit-value mt-1">{{ $log->entry_person_name ?: '-' }}</div>
                </div>
                <div class="audit-detail-card p-4 md:col-span-2">
                    <div class="audit-label">Output</div>
                    <div class="audit-value mt-1">{{ $log->output_title ?: '-' }}</div>
                </div>
            </div>

            <div class="mt-5 grid grid-cols-1 gap-4 md:grid-cols-3">
                <div class="audit-detail-card p-4">
                    <div class="audit-label">Method / Route</div>
                    <div class="audit-value mt-1">{{ ($log->http_method ?: '-') . ' ' . ($log->route_name ?: '-') }}</div>
                </div>
                <div class="audit-detail-card p-4">
                    <div class="audit-label">IP Address</div>
                    <div class="audit-value mt-1">{{ $log->ip_address ?: '-' }}</div>
                </div>
                <div class="audit-detail-card p-4">
                    <div class="audit-label">Device</div>
                    <div class="audit-value mt-1">{{ $log->device_type ?: '-' }}</div>
                </div>
            </div>

            <div class="mt-5 audit-detail-card p-4">
                <div class="audit-label">URL</div>
                <div class="audit-value mt-1 break-all">{{ $log->url ?: '-' }}</div>
            </div>

            <div class="mt-5 audit-detail-card p-4">
                <div class="audit-label">User Agent</div>
                <div class="audit-value mt-1 break-all">{{ $log->user_agent ?: '-' }}</div>
            </div>

            <div class="mt-5 audit-detail-card p-4">
                <div class="audit-label">Changes</div>
                @php
                    $decodedChanges = is_array($log->changes)
                        ? $log->changes
                        : (json_decode((string) $log->changes, true) ?: null);
                @endphp
                @if ($decodedChanges)
                    <pre class="mt-2 overflow-x-auto rounded-xl bg-slate-950 p-4 text-xs leading-6 text-slate-100">{{ json_encode($decodedChanges, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                @else
                    <div class="audit-value mt-1">-</div>
                @endif
            </div>

            <div class="mt-5 flex flex-wrap gap-3">
                @if ($log->task)
                    <a href="{{ route('back_office.show', $log->task) }}" class="inline-flex items-center rounded-xl border border-sky-200 bg-sky-50 px-4 py-2 text-sm font-semibold text-sky-700 hover:bg-sky-100">
                        ดู Back Office Task
                    </a>
                @endif
                <a href="{{ route('back_office_action_logs.index') }}" class="inline-flex items-center rounded-xl border border-slate-200 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                    กลับไปหน้ารายการ
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
