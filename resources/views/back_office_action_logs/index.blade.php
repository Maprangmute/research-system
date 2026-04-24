@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Back Office Logs</h1>
            <p class="text-sm text-slate-500">บันทึกทุก action ของ Back Office สำหรับ admin</p>
        </div>
        <a href="{{ route('project_logs.index') }}" class="inline-flex items-center rounded-xl border border-slate-200 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
            กลับไป Project Log
        </a>
    </div>

    <form method="GET" class="mb-5 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="grid grid-cols-1 gap-3 md:grid-cols-4">
            <div class="md:col-span-2">
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">ค้นหา</label>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="ชื่องาน / ผู้ใช้ / IP / output" class="w-full rounded-xl border-slate-300 text-sm focus:border-sky-500 focus:ring-sky-500">
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Action</label>
                <select name="action" class="w-full rounded-xl border-slate-300 text-sm focus:border-sky-500 focus:ring-sky-500">
                    <option value="">ทั้งหมด</option>
                    @foreach ($actionOptions as $action)
                        <option value="{{ $action }}" @selected(request('action') === $action)>{{ $action }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="inline-flex w-full justify-center rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-800">ค้นหา</button>
                <a href="{{ route('back_office_action_logs.index') }}" class="inline-flex justify-center rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">ล้าง</a>
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
                        <th class="px-4 py-3">Action</th>
                        <th class="px-4 py-3">รายการ</th>
                        <th class="px-4 py-3">อุปกรณ์ / IP</th>
                        <th class="px-4 py-3">เส้นทาง</th>
                        <th class="px-4 py-3"></th>
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
                                <span class="inline-flex rounded-full bg-sky-50 px-2.5 py-1 text-xs font-semibold text-sky-700">{{ $log->action }}</span>
                            </td>
                            <td class="px-4 py-3 text-slate-700">
                                <div class="font-medium">{{ $log->subject_label ?: '-' }}</div>
                                @if ($log->task_title)
                                    <div class="text-xs text-slate-500">Task: {{ $log->task_title }}</div>
                                @endif
                                @if ($log->entry_person_name)
                                    <div class="text-xs text-slate-500">Person: {{ $log->entry_person_name }}</div>
                                @endif
                                @if ($log->output_title)
                                    <div class="text-xs text-slate-500">Output: {{ $log->output_title }}</div>
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
                            <td class="px-4 py-3 whitespace-nowrap">
                                <a href="{{ route('back_office_action_logs.show', $log->id) }}" class="inline-flex items-center rounded-lg border border-sky-200 bg-sky-50 px-3 py-1.5 text-xs font-semibold text-sky-700 hover:bg-sky-100">
                                    ดูรายละเอียด
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-10 text-center text-slate-400">ยังไม่มีข้อมูล log</td>
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
