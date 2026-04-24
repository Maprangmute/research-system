@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">User Activity Logs</h1>
            <p class="text-sm text-slate-500">บันทึก login, logout และ file download สำหรับ admin</p>
        </div>
        <a href="{{ route('project_logs.index') }}" class="inline-flex items-center rounded-xl border border-slate-200 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
            กลับไป Project Log
        </a>
    </div>

    <form method="GET" class="mb-5 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="grid grid-cols-1 gap-3 md:grid-cols-4">
            <div class="md:col-span-2">
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">ค้นหา</label>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="ผู้ใช้ / ไฟล์ / IP / URL" class="w-full rounded-xl border-slate-300 text-sm focus:border-sky-500 focus:ring-sky-500">
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Activity</label>
                <select name="activity_type" class="w-full rounded-xl border-slate-300 text-sm focus:border-sky-500 focus:ring-sky-500">
                    <option value="">ทั้งหมด</option>
                    @foreach ($activityOptions as $activity)
                        <option value="{{ $activity }}" @selected(request('activity_type') === $activity)>{{ $activity }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="inline-flex w-full justify-center rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-800">ค้นหา</button>
                <a href="{{ route('user_activity_logs.index') }}" class="inline-flex justify-center rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">ล้าง</a>
            </div>
        </div>
    </form>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <th class="px-4 py-3">เวลา</th>
                        <th class="px-4 py-3">ผู้ใช้</th>
                        <th class="px-4 py-3">Activity</th>
                        <th class="px-4 py-3">รายการ / ไฟล์</th>
                        <th class="px-4 py-3">อุปกรณ์ / IP</th>
                        <th class="px-4 py-3">เส้นทาง</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($logs as $log)
                        <tr class="align-top">
                            <td class="px-4 py-3 whitespace-nowrap text-slate-600">
                                {{ optional($log->created_at)->setTimezone('Asia/Bangkok')->format('d/m/Y H:i:s') }}
                            </td>
                            <td class="px-4 py-3 text-slate-700">
                                <div class="font-medium">{{ trim(($log->user->prefix ?? '') . ' ' . ($log->user->name ?? '')) ?: 'ไม่ทราบผู้ใช้' }}</div>
                                <div class="text-xs text-slate-400">{{ $log->user->email ?? '-' }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">{{ $log->activity_type }}</span>
                            </td>
                            <td class="px-4 py-3 text-slate-700">
                                <div class="font-medium">{{ $log->subject_label ?: '-' }}</div>
                                @if ($log->file_name)
                                    <div class="text-xs text-slate-500">File: {{ $log->file_name }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-slate-600">
                                <div>{{ $log->device_type ?: '-' }}</div>
                                <div class="text-xs text-slate-400">{{ $log->ip_address ?: '-' }}</div>
                            </td>
                            <td class="px-4 py-3 text-slate-600">
                                <div>{{ $log->http_method ?: '-' }} {{ $log->route_name ?: '-' }}</div>
                                <div class="max-w-md break-all text-xs text-slate-400">{{ $log->url ?: '-' }}</div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-10 text-center text-slate-400">ยังไม่มีข้อมูล activity log</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-slate-200 px-4 py-3">
            {{ $logs->links() }}
        </div>
    </div>
</div>
@endsection
