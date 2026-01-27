@extends('layouts.app')
@section('content')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="max-w-xl mx-auto mt-6 px-4 space-y-4">
    <div class="flex flex-col gap-3">
        <a href="javascript:history.back()" class="inline-flex items-center text-blue-700 hover:underline font-medium">
            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
            ย้อนกลับ
        </a>
        <h1 class="text-xl font-bold text-gray-800">📜 ประวัติการเปลี่ยนแปลงโครงการ</h1>
        <form action="{{ route('project_logs.purge_old') }}" method="POST" class="purge-old-form">
            @csrf
            @method('DELETE')
            <button type="submit" class="w-full px-3 py-2 rounded bg-red-600 text-white text-sm hover:bg-red-700">
                ลบ log เก่ากว่า 3 เดือน
            </button>
        </form>
    </div>

    <form action="{{ route('project_logs.index') }}" method="GET" class="bg-white border rounded-lg p-3 space-y-3">
        <div>
            <label class="block text-xs text-gray-500 mb-1">ค้นหา</label>
            <input type="text" name="q" value="{{ $search ?? '' }}" placeholder="ชื่อโครงการ/รหัส/ผู้ใช้"
                class="border rounded px-3 py-2 text-sm w-full">
        </div>
        <div class="grid grid-cols-1 gap-3">
            <div>
                <label class="block text-xs text-gray-500 mb-1">ประเภท</label>
                <select name="entity" class="border rounded px-3 py-2 text-sm w-full">
                    @foreach($entityOptions ?? [] as $value => $label)
                        <option value="{{ $value }}" @if(($entity ?? 'all') === $value) selected @endif>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">เหตุการณ์</label>
                <select name="verb" class="border rounded px-3 py-2 text-sm w-full">
                    @foreach($verbOptions ?? [] as $value => $label)
                        <option value="{{ $value }}" @if(($verb ?? 'all') === $value) selected @endif>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">เรียงตาม</label>
                <select name="sort" class="border rounded px-3 py-2 text-sm w-full">
                    <option value="latest" @selected(($sort ?? 'latest') === 'latest')>สร้างล่าสุด → เก่าสุด</option>
                    <option value="oldest" @selected(($sort ?? '') === 'oldest')>สร้างเก่าสุด → ล่าสุด</option>
                    <option value="project_asc" @selected(($sort ?? '') === 'project_asc')>ชื่อโครงการ (ก-ฮ)</option>
                    <option value="project_desc" @selected(($sort ?? '') === 'project_desc')>ชื่อโครงการ (ฮ-ก)</option>
                    <option value="user_asc" @selected(($sort ?? '') === 'user_asc')>ผู้ใช้ (ก-ฮ)</option>
                    <option value="user_desc" @selected(($sort ?? '') === 'user_desc')>ผู้ใช้ (ฮ-ก)</option>
                    <option value="action_asc" @selected(($sort ?? '') === 'action_asc')>เหตุการณ์ (ก-ฮ)</option>
                    <option value="action_desc" @selected(($sort ?? '') === 'action_desc')>เหตุการณ์ (ฮ-ก)</option>
                </select>
            </div>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="flex-1 px-3 py-2 rounded bg-blue-600 text-white text-sm hover:bg-blue-700">ค้นหา</button>
            <a href="{{ route('project_logs.index') }}" class="flex-1 px-3 py-2 rounded bg-gray-100 text-gray-700 text-sm text-center hover:bg-gray-200">ล้าง</a>
        </div>
    </form>

    @if(session('success'))
        <div class="rounded bg-green-50 text-green-700 px-4 py-2 text-sm">
            {{ session('success') }}
        </div>
    @endif

    @forelse($groupedLogs ?? [] as $group)
        @php
            $first = $group->first();
            $thaiDate = \Carbon\Carbon::parse($first->created_at)->addYears(543)->format('d/m/Y H:i:s');
            $projectName = $first->project?->project_name_th ?? $first->project_name_th ?? '-';
            $userName = $first->user?->prefix . $first->user?->name;
            $eventItems = $group->map(function ($log) {
                return [
                    'id' => $log->id,
                    'action' => $log->entity_label . ': ' . $log->action_label,
                ];
            })->values()->all();
        @endphp
        <div class="bg-white border rounded-xl shadow-sm p-4 space-y-2">
            <div class="text-xs text-gray-500">{{ $thaiDate }}</div>
            <div class="font-semibold text-gray-800">
                <a href="{{ route('project_logs.project', $first->project_id) }}" class="text-blue-700 hover:underline">
                    {{ $projectName }}
                </a>
            </div>
            <div class="text-sm text-gray-600">โดย {{ $userName }}</div>
            <div class="flex flex-wrap gap-2 pt-1">
                @foreach($group as $log)
                    <span class="inline-block {{ $log->action_color_class }} px-2 py-0.5 rounded text-xs">
                        {{ $log->entity_label }}: {{ $log->action_label }}
                    </span>
                @endforeach
            </div>
            <div class="flex items-center justify-between pt-2">
                <span class="text-blue-700 hover:underline text-sm cursor-pointer open-log-drawer"
                    data-time="{{ $thaiDate }}"
                    data-project="{{ $projectName }}"
                    data-user="{{ $userName }}"
                    data-events='@json($eventItems)'>
                    ดูรายละเอียด
                </span>
                <form action="{{ route('project_logs.destroy', $first->id) }}" method="POST" class="inline delete-log-form">
                    @csrf @method('DELETE')
                    <button type="submit" class="px-3 py-1 rounded bg-red-600 text-white text-xs hover:bg-red-700">ลบ</button>
                </form>
            </div>
        </div>
        @foreach($group as $log)
            <div id="log-details-content-{{ $log->id }}" class="hidden">
                @include('project_logs._changes', ['log' => $log])
            </div>
        @endforeach
    @empty
        <div class="text-center py-10 text-gray-400">ไม่มี log ในระบบ</div>
    @endforelse

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
        <div class="p-5 max-h-[70vh] overflow-auto" id="log-drawer-body"></div>
    </div>
</div>

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

    document.querySelectorAll('.open-log-drawer').forEach(function(button) {
        button.addEventListener('click', function() {
            var events = [];
            try {
                events = JSON.parse(button.getAttribute('data-events') || '[]');
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
                time: button.getAttribute('data-time'),
                action: 'เหตุการณ์ทั้งหมด',
                project: button.getAttribute('data-project'),
                user: button.getAttribute('data-user'),
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
