@extends('layouts.app')
@section('content')
<div class="max-w-6xl mx-auto mt-10">
    <div class="flex items-center justify-between mb-6">
        <a href="javascript:history.back()" class="inline-flex items-center text-blue-700 hover:underline font-medium">
            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
            ย้อนกลับ
        </a>
        <form action="{{ route('project_logs.purge_old') }}" method="POST" class="inline purge-old-form">
            @csrf
            @method('DELETE')
            <button type="submit" class="px-3 py-1.5 rounded bg-red-600 text-white text-sm hover:bg-red-700">
                ลบ log เก่ากว่า 3 เดือน
            </button>
        </form>
    </div>
<h1 class="text-2xl font-bold text-gray-800 mb-2">📜 ประวัติการเปลี่ยนแปลงโครงการ (ทั้งหมด)</h1>
    <form action="{{ route('project_logs.index') }}" method="GET" class="mb-4 bg-white border rounded-lg p-3 flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs text-gray-500 mb-1">ค้นหา</label>
            <input type="text" name="q" value="{{ $search ?? '' }}" placeholder="ชื่อโครงการ/รหัส/ผู้ใช้" class="border rounded px-3 py-1.5 text-sm w-56">
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">ประเภท</label>
            <select name="entity" class="border rounded px-3 py-1.5 text-sm w-40">
                @foreach($entityOptions ?? [] as $value => $label)
                    <option value="{{ $value }}" @if(($entity ?? 'all') === $value) selected @endif>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">เหตุการณ์</label>
            <select name="verb" class="border rounded px-3 py-1.5 text-sm w-32">
                @foreach($verbOptions ?? [] as $value => $label)
                    <option value="{{ $value }}" @if(($verb ?? 'all') === $value) selected @endif>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="px-3 py-1.5 rounded bg-blue-600 text-white text-sm hover:bg-blue-700">ค้นหา</button>
            <a href="{{ route('project_logs.index') }}" class="px-3 py-1.5 rounded bg-gray-100 text-gray-700 text-sm hover:bg-gray-200">ล้าง</a>
        </div>
    </form>
    @if(session('success'))
        <div class="mb-4 rounded bg-green-50 text-green-700 px-4 py-2 text-sm">
            {{ session('success') }}
        </div>
    @endif
    <div class="overflow-x-auto bg-white shadow-lg rounded-lg">
        <table class="min-w-full table-auto border">
            <thead class="bg-gray-50 text-sm uppercase">
                <tr>
                    <th class="border px-4 py-2 text-left">วันเวลา</th>
                    <th class="border px-4 py-2 text-left">ชื่อโครงการ</th>
                    <th class="border px-4 py-2 text-left">โดย</th>
                    <th class="border px-4 py-2 text-left">เหตุการณ์</th>
                    <th class="border px-4 py-2"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($groupedLogs ?? [] as $group)
                    @php
                        $first = $group->first();
                        $thaiDate = \Carbon\Carbon::parse($first->created_at)->addYears(543)->format('d/m/Y H:i:s');
                        $projectName = $first->project?->project_name_th ?? $first->project_name_th ?? '-';
                        $userName = $first->user?->prefix . $first->user?->name;
                        $eventLabels = $group->map(function ($log) {
                            return $log->entity_label . ': ' . $log->action_label;
                        })->all();
                        $eventItems = $group->map(function ($log) {
                            return [
                                'id' => $log->id,
                                'action' => $log->entity_label . ': ' . $log->action_label,
                            ];
                        })->values()->all();
                    @endphp
                    <tr class="hover:bg-gray-100 transition cursor-pointer open-log-drawer"
                        data-time="{{ $thaiDate }}"
                        data-project="{{ $projectName }}"
                        data-user="{{ $userName }}"
                        data-events='@json($eventItems)'>
                        <td class="border px-4 py-2 text-xs text-gray-600 whitespace-nowrap">
                            {{ $thaiDate }}
                        </td>
                        <td class="border px-4 py-2">
                            <a href="{{ route('project_logs.project', $first->project_id) }}" class="text-blue-600 hover:underline">
                                {{ $projectName }}
                            </a>
                        </td>
                        <td class="border px-4 py-2">
                            {{ $userName }}
                        </td>
                        <td class="border px-4 py-2">
                            <div class="flex flex-wrap gap-2">
                                @foreach($group as $log)
                                    <span class="inline-block {{ $log->action_color_class }} px-2 py-0.5 rounded text-xs">
                                        {{ $log->entity_label }}: {{ $log->action_label }}
                                    </span>
                                @endforeach
                            </div>
                        </td>
                        <td class="border px-4 py-2 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <span class="text-blue-700 text-sm">ดูรายละเอียด</span>
                                <form action="{{ route('project_logs.destroy', $first->id) }}" method="POST" class="inline delete-log-form">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800 font-bold" title="ลบ log">
                                        🗑️
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <tr class="hidden">
                        <td colspan="5">
                            @foreach($group as $log)
                                <div id="log-details-content-{{ $log->id }}">
                                    @include('project_logs._changes', ['log' => $log])
                                </div>
                            @endforeach
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center py-8 text-gray-400">ไม่มี log ในระบบ</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">
        {{ $logs->links('vendor.pagination.th') }}
    </div>
</div>

<div id="log-drawer" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/40 log-drawer-close"></div>
    <div class="absolute bottom-0 left-0 right-0 bg-white rounded-t-2xl shadow-xl transform translate-y-full transition-transform duration-300" id="log-drawer-panel">
        <div class="flex items-center justify-between px-5 py-3 border-b">
            <div>
                <div class="text-xs text-gray-500" id="log-drawer-time"></div>
                <div class="font-semibold text-gray-800" id="log-drawer-action"></div>
                <div class="text-sm text-gray-600" id="log-drawer-project"></div>
                <div class="text-sm text-gray-500" id="log-drawer-user"></div>
            </div>
            <button type="button" class="text-gray-500 hover:text-gray-700 log-drawer-close">ปิด</button>
        </div>
        <div class="p-5 max-h-[60vh] overflow-auto" id="log-drawer-body"></div>
    </div>
</div>

{{-- SweetAlert confirm --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    var drawer = document.getElementById('log-drawer');
    var drawerPanel = document.getElementById('log-drawer-panel');
    var drawerBody = document.getElementById('log-drawer-body');
    var drawerTime = document.getElementById('log-drawer-time');
    var drawerAction = document.getElementById('log-drawer-action');
    var drawerProject = document.getElementById('log-drawer-project');
    var drawerUser = document.getElementById('log-drawer-user');

    function openDrawer(payload) {
        drawerTime.textContent = payload.time || '';
        drawerAction.textContent = payload.action || '';
        drawerProject.textContent = payload.project || '';
        drawerUser.textContent = payload.user || '';
        drawerBody.innerHTML = payload.html || '';
        drawer.classList.remove('hidden');
        requestAnimationFrame(function() {
            drawerPanel.classList.remove('translate-y-full');
        });
    }

    function closeDrawer() {
        drawerPanel.classList.add('translate-y-full');
        setTimeout(function() {
            drawer.classList.add('hidden');
            drawerBody.innerHTML = '';
        }, 250);
    }

    document.querySelectorAll('.open-log-drawer').forEach(function(row) {
        row.addEventListener('click', function(e) {
            if (e.target.closest('form')) {
                return;
            }
            var events = [];
            try {
                events = JSON.parse(row.getAttribute('data-events') || '[]');
            } catch (err) {
                events = [];
            }

            var bodyHtml = '';
            events.forEach(function(item) {
                var contentEl = document.getElementById('log-details-content-' + item.id);
                bodyHtml += '<div class="mb-4 border rounded-lg p-3 bg-white">';
                bodyHtml += '<div class="font-semibold text-gray-800 mb-2">' + item.action + '</div>';
                bodyHtml += '<div>' + (contentEl ? contentEl.innerHTML : '-') + '</div>';
                bodyHtml += '</div>';
            });

            openDrawer({
                time: row.getAttribute('data-time'),
                action: 'เหตุการณ์ทั้งหมด',
                project: row.getAttribute('data-project'),
                user: row.getAttribute('data-user'),
                html: bodyHtml
            });
        });
    });

    document.querySelectorAll('.log-drawer-close').forEach(function(button) {
        button.addEventListener('click', function() {
            closeDrawer();
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
    document.querySelectorAll('.purge-old-form').forEach(function(form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            if(confirm('ยืนยันการลบ log ที่เก่ากว่า 3 เดือนทั้งหมด?')) {
                form.submit();
            }
        });
    });
});
</script>
@endsection
