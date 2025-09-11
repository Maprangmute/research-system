@extends('layouts.app')
@section('content')

{{-- FullCalendar (ต้องใช้ index.global.min.js ไม่ใช่ main.min.js) --}}
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>

<div class="max-w-7xl mx-auto p-6 space-y-8">

    {{-- Profile Card --}}
    <div class="bg-white shadow rounded-2xl p-6 flex items-center gap-6">
        @if($profile && $profile->profile_photo_path)
            <img src="{{ asset('storage/'.$profile->profile_photo_path) }}"
                 class="w-32 h-32 rounded-full border-4 border-blue-200 shadow-lg object-cover">
        @else
            <img src="https://ui-avatars.com/api/?name={{ urlencode($person) }}&background=random&size=128"
                 class="w-32 h-32 rounded-full border-4 border-blue-200 shadow-lg object-cover">
        @endif
        <div>
            <h1 class="text-3xl font-bold text-gray-800">{{ $person }}</h1>
            <p class="text-gray-500 mt-2">
                FTE รวม (ตาม filter):
                <span class="font-semibold text-green-600">{{ number_format($fteRows->sum('fte_value'), 2) }}</span>
            </p>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="bg-gradient-to-r from-blue-50 to-white border border-blue-100 rounded-xl shadow px-4 py-5">
        <form method="GET" id="filter-form" class="flex flex-wrap gap-4 items-end w-full">
            <input type="hidden" name="person" value="{{ $person }}">

            {{-- Mode --}}
            <div class="flex flex-col min-w-[140px]">
                <label class="text-gray-800 font-semibold mb-1">ดูข้อมูล</label>
                <select name="mode" id="mode"
                        class="rounded-lg border-gray-300 p-2 focus:ring-2 focus:ring-blue-300"
                        onchange="resetOtherFilters(); this.form.submit();">
                    <option value="year" {{ $mode == 'year' ? 'selected' : '' }}>รายปี</option>
                    <option value="month" {{ $mode == 'month' ? 'selected' : '' }}>เดือนนี้</option>
                    <option value="custom" {{ $mode == 'custom' ? 'selected' : '' }}>เลือกช่วงวันที่</option>
                </select>
            </div>

            {{-- Year --}}
            <div id="filter-year" class="flex flex-col min-w-[120px] {{ $mode === 'year' ? '' : 'hidden' }}">
                <label class="text-gray-800 mb-1">ปี</label>
                <select name="year" id="yearSelect" class="rounded-lg border-gray-300 p-2 w-full"
                        onchange="this.form.submit();">
                    @foreach ($allYears as $y)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Custom Range --}}
            <div id="filter-custom" class="flex flex-col min-w-[250px] {{ $mode === 'custom' ? '' : 'hidden' }}">
                <label class="text-gray-800 mb-1">ช่วงวันที่</label>
                <div class="flex gap-2 items-center">
                    <input name="start" id="dateStart"
                           class="rounded-lg border-gray-300 p-2 w-[140px] focus:ring-2 focus:ring-blue-200"
                           placeholder="วันเริ่มต้น" value="{{ $start ?? '' }}">
                    <span>-</span>
                    <input name="end" id="dateEnd"
                           class="rounded-lg border-gray-300 p-2 w-[140px] focus:ring-2 focus:ring-blue-200"
                           placeholder="วันสิ้นสุด" value="{{ $end ?? '' }}">
                </div>
            </div>

            {{-- Reset --}}
            @if (request()->has('year') || request()->has('mode') || request()->has('start'))
                <div class="flex flex-col">
                    <label class="invisible mb-1">ล้าง</label>
                    <a href="{{ route('fte.detail', ['person' => $person, 'mode' => 'year', 'year' => now()->year + 543]) }}"
                       class="px-4 py-2 rounded-lg bg-gray-100 text-blue-700 font-bold shadow hover:bg-blue-200 text-center">
                        ล้างตัวกรอง
                    </a>
                </div>
            @endif
        </form>
    </div>

    {{-- Tabs --}}
    <div>
        <div class="flex border-b mb-4 text-gray-600">
            <button class="tab-btn px-6 py-2 font-bold border-b-2 transition" data-tab="tab1">📋 ตาราง</button>
            <button class="tab-btn px-6 py-2 font-bold border-b-2 transition" data-tab="tab2">📈 กราฟ</button>
            <button class="tab-btn px-6 py-2 font-bold border-b-2 transition" data-tab="tab3">📆 ปฏิทิน</button>
        </div>

        {{-- ตาราง --}}
        <div id="tab1" class="tab-pane">
            <div class="space-y-6">
                @foreach($fteMonthly as $monthKey => $rows)
                    @php $c = \Carbon\Carbon::createFromFormat('Y-m', $monthKey); @endphp
                    <div class="bg-white rounded-xl shadow p-4">
                        <h3 class="font-semibold text-blue-700 mb-3">
                            {{ $c->locale('th')->isoFormat('MMMM YYYY') }}
                        </h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th class="p-2 text-left">ชื่อโปรเจค</th>
                                        <th class="p-2 text-left">กลุ่ม</th>
                                        <th class="p-2 text-left">ภาระงาน</th>
                                        <th class="p-2 text-center">วันที่เริ่ม</th>
                                        <th class="p-2 text-center">วันที่สิ้นสุด</th>
                                        <th class="p-2 text-right">FTE</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($rows as $item)
                                    <tr class="border-b">
                                        <td class="p-2 text-blue-700 underline">
                                            <a href="{{ route('projects.show', $item->project->id) }}">
                                                {{ $item->project->project_name_th ?? '-' }}
                                            </a>
                                        </td>
                                        <td class="p-2">{{ $item->role_group ?? '-' }}</td>
                                        <td class="p-2">{{ $item->job_detail ?? '-' }}</td>
                                        <td class="p-2 text-center">{{ $item->start_date }}</td>
                                        <td class="p-2 text-center">{{ $item->end_date }}</td>
                                        <td class="p-2 text-right font-semibold">{{ number_format($item->fte_value, 2) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- กราฟ --}}
        <div id="tab2" class="tab-pane hidden">
            <div class="bg-white rounded-xl shadow p-6 space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="font-semibold text-blue-700">แนวโน้มค่า FTE</h3>
                    <select id="compareSelect" class="rounded-lg border-gray-300 text-sm px-3 py-2">
                        <option value="">-- เปรียบเทียบ --</option>
                        @foreach($allPeople as $p)
                            <option value="{{ $p }}">{{ $p }}</option>
                        @endforeach
                    </select>
                </div>
                <canvas id="fteChart" height="120"></canvas>
            </div>
        </div>

        {{-- ปฏิทิน --}}
        <div id="tab3" class="tab-pane hidden">
            <div id="calendar" class="bg-white rounded-xl shadow p-6"></div>
        </div>
    </div>
</div>

{{-- Chart.js --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tabs
    const tabs = document.querySelectorAll('.tab-btn');
    const panes = document.querySelectorAll('.tab-pane');
    function activateTab(tab) {
        panes.forEach(x => x.classList.add('hidden'));
        tabs.forEach(x => x.classList.remove('border-blue-700','text-blue-700'));
        document.getElementById(tab).classList.remove('hidden');
        document.querySelector('.tab-btn[data-tab="'+tab+'"]').classList.add('border-blue-700','text-blue-700');

        if(tab === 'tab3' && !window.calendarRendered) {
            renderCalendar();
            window.calendarRendered = true;
        }
    }
    tabs.forEach(btn => btn.onclick = () => activateTab(btn.dataset.tab));
    activateTab('tab1');

    // Chart
    let ctx = document.getElementById('fteChart');
    let chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: {!! $chartLabels->toJson() !!},
            datasets: [{
                label: '{{ $person }}',
                data: {!! $chartData->toJson() !!},
                borderColor: '#2563eb',
                backgroundColor: 'rgba(37,99,235,0.2)',
                fill: false,
                tension: 0.3
            }]
        },
        options: { responsive: true, plugins: { legend: { position: 'bottom' }} }
    });

    // Compare AJAX
    document.getElementById('compareSelect').addEventListener('change', function() {
        const name = this.value;
        if (!name) {
            chart.data.datasets = chart.data.datasets.slice(0,1);
            chart.update();
            return;
        }
        fetch(`/fte/compare-data?name=${encodeURIComponent(name)}&mode={{ $mode }}&year={{ $year }}&start={{ $start }}&end={{ $end }}`)
            .then(res => res.json())
            .then(res => {
                chart.data.datasets = chart.data.datasets.slice(0,1);
                chart.data.datasets.push({
                    label: res.name,
                    data: res.data,
                    borderColor: '#ef4444',
                    backgroundColor: 'rgba(239,68,68,0.2)',
                    fill: false,
                    tension: 0.3
                });
                chart.update();
            });
    });
});

// Calendar
function renderCalendar() {
    let events = {!! $calendarEvents->toJson() !!};
    let colors = {};
    let palette = ['#2563eb','#16a34a','#dc2626','#d97706','#9333ea','#0891b2','#db2777','#65a30d'];
    let idx = 0;
    events = events.map(ev => {
        if(!colors[ev.title]) colors[ev.title] = palette[idx++ % palette.length];
        return { ...ev, color: colors[ev.title] };
    });

    let calendar = new FullCalendar.Calendar(document.getElementById('calendar'), {
        initialView: 'dayGridMonth',
        locale: 'th',
        height: 650,
        events: events,
        headerToolbar: { left: 'prev,next today', center: 'title', right: 'dayGridMonth,timeGridWeek,listWeek' },
        dayMaxEventRows: 3
    });
    calendar.render();
}
</script>

{{-- Flatpickr --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/th.js"></script>
<script>
flatpickr("#dateStart", { dateFormat: "d/m/Y", locale: "th" });
flatpickr("#dateEnd",   { dateFormat: "d/m/Y", locale: "th" });

function resetOtherFilters() {
    let mode = document.getElementById('mode').value;
    if (mode !== 'year' && document.getElementById('yearSelect')) {
        document.getElementById('yearSelect').value = '';
    }
    if (mode !== 'custom') {
        document.getElementById('dateStart').value = '';
        document.getElementById('dateEnd').value = '';
    }
}
</script>
@endsection
