@extends('layouts.app')
@section('content')

<!-- TailwindCSS + Alpine.js Calendar Component -->
<div class="max-w-4xl mx-auto p-4">
    <!-- Profile -->
    <div class="flex items-center gap-4 mb-6">
        @if($profile && $profile->profile_photo_path)
            <img src="{{ asset('storage/'.$profile->profile_photo_path) }}" class="w-16 h-16 rounded-full border object-cover">
        @else
            <img src="https://ui-avatars.com/api/?name={{ urlencode($person) }}&background=random&size=96" class="w-16 h-16 rounded-full border object-cover">
        @endif
        <div>
            <div class="text-xl font-bold">{{ $person }}</div>
            <div class="text-gray-500">FTE ทั้งหมด: {{ number_format($fteRows->sum('fte_value'), 2) }}</div>
        </div>
    </div>

    <!-- Tabs -->
    <div>
        <div class="flex border-b mb-4">
            <button class="tab-btn px-6 py-2 font-bold border-b-2 transition" data-tab="tab1">📋 ตาราง</button>
            <button class="tab-btn px-6 py-2 font-bold border-b-2 transition" data-tab="tab2">📆 ปฏิทิน</button>
            <button class="tab-btn px-6 py-2 font-bold border-b-2 transition" data-tab="tab3">📈 กราฟ</button>
        </div>
        <div>
            <!-- Tab 1: ตาราง -->
            <div id="tab1" class="tab-pane">
                @foreach($fteMonthly as $monthKey => $rows)
                    @php
                        $c = \Carbon\Carbon::createFromFormat('Y-m', $monthKey);
                    @endphp
                    <div>
                        <div class="text-blue-700 font-bold text-base mb-2">
                            {{ $c->locale('th')->isoFormat('MMMM YYYY') }}
                        </div>
                        <div class="bg-white border rounded-xl p-3 shadow-sm overflow-x-auto">
                            <table class="min-w-full text-xs table-auto">
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
                                    <tr>
                                        <td class="p-2 break-words max-w-[180px]">{{ $item->project->project_name_th ?? '-' }}</td>
                                        <td class="p-2">{{ $item->role_group ?? '-' }}</td>
                                        <td class="p-2">{{ $item->job_detail ?? '-' }}</td>
                                        <td class="p-2 text-center">{{ $item->start_date }}</td>
                                        <td class="p-2 text-center">{{ $item->end_date }}</td>
                                        <td class="p-2 text-right">{{ number_format($item->fte_value, 2) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Tab 2: Modern Tailwind Calendar -->
            <div id="tab2" class="tab-pane hidden">
                <div 
                  x-data="calendarApp({{
                        json_encode(
                            $fteRows->map(function($item){
                                return [
                                    'title' => $item->project->project_name_th ?? '-',
                                    'start' => $item->start_date,
                                    'end' => $item->end_date,
                                ];
                            })->values()
                        )
                  }})"
                  x-init="init()"
                  class="w-full bg-white rounded-xl border shadow-sm p-4"
                  >
                  <div class="flex flex-wrap justify-between items-center mb-3 gap-2">
                    <!-- Year and Month -->
                    <div class="flex items-center gap-2">
                        <button @click="prevMonth()" class="p-1 rounded hover:bg-blue-50 text-gray-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                              <path d="M15 19l-7-7 7-7" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>
                        <select x-model="month" @change="updateCalendar()" class="rounded border-gray-300 py-1 px-2">
                            <template x-for="(m, idx) in monthNames" :key="idx">
                              <option :value="idx+1" x-text="m"></option>
                            </template>
                        </select>
                        <select x-model="year" @change="updateCalendar()" class="rounded border-gray-300 py-1 px-2 ml-2">
                            <template x-for="y in years" :key="y">
                              <option :value="y" x-text="y"></option>
                            </template>
                        </select>
                        <button @click="nextMonth()" class="p-1 rounded hover:bg-blue-50 text-gray-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                              <path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>
                        <button @click="today()" class="ml-4 px-2 py-1 rounded bg-blue-50 hover:bg-blue-200 text-blue-700 font-bold text-sm">วันนี้</button>
                    </div>
                  </div>

                  <!-- Calendar Grid -->
                  <div class="grid grid-cols-7 bg-gray-50 rounded-t-xl text-center text-xs text-gray-600 font-bold mb-2">
                      <template x-for="day in dayNames" :key="day">
                        <div class="py-2" x-text="day"></div>
                      </template>
                  </div>
                  <div class="grid grid-cols-7 gap-px">
                    <template x-for="(date, idx) in calendarCells" :key="idx">
                      <div 
                        :class="[
                            'bg-white min-h-[72px] p-2 text-xs relative transition-all group flex flex-col',
                            date.isOtherMonth ? 'bg-gray-50 text-gray-300' : '',
                            date.isToday ? 'ring-2 ring-blue-400 bg-blue-50 text-blue-700 font-bold' : '',
                        ]"
                        :title="date.events.length ? date.events.map(ev => ev.title).join('\n') : ''"
                      >
                        <div class="flex justify-between items-center mb-1">
                            <span x-text="date.day"></span>
                            <template x-if="date.isToday">
                              <span class="ml-1 inline-block bg-blue-100 text-blue-700 px-1 rounded text-[10px]">วันนี้</span>
                            </template>
                        </div>
                        <template x-for="ev in date.events.slice(0,2)" :key="ev.title">
                          <div class="truncate bg-gradient-to-r from-blue-100 to-blue-200 text-blue-900 px-1 rounded mb-1 text-xs cursor-pointer"
                            x-tooltip="ev.title"
                          >
                            <span x-text="ev.title"></span>
                          </div>
                        </template>
                        <template x-if="date.events.length > 2">
                            <div class="text-[10px] text-blue-600 mt-1">+ อีก <span x-text="date.events.length-2"></span> งาน</div>
                        </template>
                      </div>
                    </template>
                  </div>
                </div>
            </div>

            <!-- Tab 3: กราฟ -->
            <div id="tab3" class="tab-pane hidden">
                <canvas id="fteChart" height="110"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Alpine.js (v3+) -->
<script src="//unpkg.com/alpinejs" defer></script>

<script>
// modern calendar
function calendarApp(events) {
    return {
        month: new Date().getMonth() + 1,
        year: new Date().getFullYear() + 543, // แสดง พ.ศ.
        todayY: new Date().getFullYear() + 543,
        todayM: new Date().getMonth() + 1,
        todayD: new Date().getDate(),
        years: [],
        calendarCells: [],
        dayNames: ['อา.', 'จ.', 'อ.', 'พ.', 'พฤ.', 'ศ.', 'ส.'],
        monthNames: ['มกราคม','กุมภาพันธ์','มีนาคม','เมษายน','พฤษภาคม','มิถุนายน','กรกฎาคม','สิงหาคม','กันยายน','ตุลาคม','พฤศจิกายน','ธันวาคม'],
        allEvents: events.map(ev => ({
            ...ev,
            start: toYMD(ev.start),
            end: toYMD(ev.end)
        })),
        init() {
            // ปี: -5 ถึง +3 ปี
            let base = (new Date()).getFullYear() + 543;
            this.years = [];
            for(let y=base-5; y<=base+3; y++) this.years.push(y);
            this.updateCalendar();
        },
        today() {
            this.month = this.todayM;
            this.year = this.todayY;
            this.updateCalendar();
        },
        prevMonth() {
            this.month--;
            if (this.month < 1) { this.month = 12; this.year--; }
            this.updateCalendar();
        },
        nextMonth() {
            this.month++;
            if (this.month > 12) { this.month = 1; this.year++; }
            this.updateCalendar();
        },
        updateCalendar() {
            let firstOfMonth = new Date(this.year-543, this.month-1, 1);
            let start = new Date(firstOfMonth);
            start.setDate(1 - start.getDay()); // Sunday
            let cells = [];
            for(let i=0;i<42;i++) { // 6 weeks
                let d = new Date(start);
                d.setDate(start.getDate()+i);
                let y = d.getFullYear() + 543;
                let m = d.getMonth()+1;
                let day = d.getDate();
                let isOtherMonth = m !== this.month || y !== this.year;
                let isToday = (day === this.todayD && m === this.todayM && y === this.todayY);
                let ymd = toYMD_BS(day, m, y);
                let events = this.allEvents.filter(ev =>
                    (ymd >= ev.start && ymd <= ev.end)
                );
                cells.push({
                    day, isOtherMonth, isToday, events,
                });
            }
            this.calendarCells = cells;
        }
    };
}

// Convert dd-mm-yyyy -> yyyy-mm-dd
function toYMD(str) {
    if(!str) return '';
    let [d,m,y] = str.split('-');
    return `${y}-${m.padStart(2,'0')}-${d.padStart(2,'0')}`;
}
// Convert (day, month, buddhistYear) -> yyyy-mm-dd
function toYMD_BS(d, m, y) {
    return `${y-543}-${String(m).padStart(2,'0')}-${String(d).padStart(2,'0')}`;
}

// Tooltip (Tailwind/Alpine)
document.addEventListener('alpine:init', () => {
    Alpine.directive('tooltip', (el, {expression}) => {
        el.setAttribute('title', expression)
    })
});
</script>

{{-- Chart.js --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tab
    const tabs = document.querySelectorAll('.tab-btn');
    const panes = document.querySelectorAll('.tab-pane');
    function activateTab(tab) {
        panes.forEach(x => x.classList.add('hidden'));
        tabs.forEach(x => x.classList.remove('border-blue-700', 'text-blue-700'));
        document.getElementById(tab).classList.remove('hidden');
        document.querySelector('.tab-btn[data-tab="'+tab+'"]').classList.add('border-blue-700', 'text-blue-700');
    }
    tabs.forEach(btn => {
        btn.onclick = () => activateTab(btn.dataset.tab);
    });
    activateTab('tab1');

    // Chart.js
    let ctx = document.getElementById('fteChart');
    if (ctx) {
        let chart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: {!! $chartLabels->toJson() !!},
                datasets: [{
                    label: 'FTE',
                    data: {!! $chartData->toJson() !!},
                    borderWidth: 1,
                    backgroundColor: 'rgba(37,99,235,0.5)'
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false }},
                scales: { y: { beginAtZero: true } }
            }
        });
    }
});
</script>
@endsection
