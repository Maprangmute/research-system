@extends('layouts.app')

@section('content')
    <div class="max-w-6xl mx-auto p-4">

        <div class="mb-5 flex items-center gap-4">
            <a href="javascript:history.back()" class="text-blue-600 hover:underline flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M15 19l-7-7 7-7" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                ย้อนกลับ
            </a>
        </div>

        <h2 class="text-xl mb-2 md:text-2xl font-bold">📊 สรุป FTE รายบุคคล</h2>

        <!-- Filter Section -->
        <div class="bg-gradient-to-r from-blue-50 to-white border border-blue-100 rounded-xl shadow mb-6 px-4 py-6">
            <form method="GET" id="filter-form" autocomplete="off"
                class="flex flex-wrap gap-x-4 gap-y-3 items-end w-full">

                {{-- Mode --}}
                <div class="flex flex-col flex-1 min-w-[130px] max-w-xs">
                    <label class="text-gray-800 font-semibold mb-1">ดูข้อมูล</label>
                    <select name="mode" id="mode"
                        class="rounded-lg border-gray-300 p-2 focus:ring-2 focus:ring-blue-300 transition w-full"
                        onchange="resetOtherFilters(); this.form.submit();">
                        <option value="year" {{ $mode == 'year' ? 'selected' : '' }}>รายปี</option>
                        <option value="month" {{ $mode == 'month' ? 'selected' : '' }}>เดือนนี้</option>
                        <option value="custom" {{ $mode == 'custom' ? 'selected' : '' }}>เลือกช่วงวันที่</option>
                    </select>
                </div>

                {{-- Year --}}
                <div id="filter-year"
                    class="flex flex-col flex-1 min-w-[110px] max-w-xs {{ $mode === 'year' ? '' : 'hidden' }}">
                    <label class="text-gray-800 mb-1">ปี</label>
                    <select name="year" id="yearSelect" class="rounded-lg border-gray-300 p-2 w-full"
                        onchange="this.form.submit();">
                        @foreach ($years as $y)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Month --}}
                <div id="filter-month"
                    class="flex flex-col flex-1 min-w-[130px] max-w-xs {{ $mode === 'month' ? '' : 'hidden' }}">
                    <label class="text-gray-800 mb-1">เดือน</label>
                    <div
                        class="rounded-lg border border-gray-200 bg-white p-2 px-3 text-base font-semibold text-blue-800 w-full">
                        {{ \Carbon\Carbon::now()->locale('th')->translatedFormat('F Y') }}
                    </div>
                </div>

                {{-- Custom Date --}}
                <div id="filter-custom"
                    class="flex flex-col flex-1 min-w-[220px] max-w-sm {{ $mode === 'custom' ? '' : 'hidden' }}">
                    <label class="text-gray-800 mb-1">ช่วงวันที่</label>
                    <div class="flex gap-2 items-end w-full">
                        <div class="relative w-[130px]">
                            <input name="start" id="dateStart"
                                class="rounded-lg border-gray-300 p-2 w-full pl-9 focus:ring-2 focus:ring-blue-200"
                                placeholder="วันเริ่มต้น" value="{{ $start ?? '' }}" autocomplete="off">
                            <span class="absolute left-2 top-2.5 text-gray-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                    <line x1="16" y1="2" x2="16" y2="6"></line>
                                    <line x1="8" y1="2" x2="8" y2="6"></line>
                                    <line x1="3" y1="10" x2="21" y2="10"></line>
                                </svg>
                            </span>
                        </div>
                        <span class="text-gray-500 mb-1">-</span>
                        <div class="relative w-[130px]">
                            <input name="end" id="dateEnd"
                                class="rounded-lg border-gray-300 p-2 w-full pl-9 focus:ring-2 focus:ring-blue-200"
                                placeholder="วันสิ้นสุด" value="{{ $end ?? '' }}" autocomplete="off">
                            <span class="absolute left-2 top-2.5 text-gray-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                    <line x1="16" y1="2" x2="16" y2="6"></line>
                                    <line x1="8" y1="2" x2="8" y2="6"></line>
                                    <line x1="3" y1="10" x2="21" y2="10"></line>
                                </svg>
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Person --}}
                <div class="flex flex-col flex-1 min-w-[130px] max-w-xs">
                    <label for="person" class="text-gray-800 mb-1">บุคคล</label>
                    <select name="person" id="person" class="rounded-lg border-gray-300 p-2 w-full"
                        onchange="this.form.submit();">
                        <option value="">ทั้งหมด</option>
                        @foreach ($personList as $name)
                            <option value="{{ $name }}" {{ $person == $name ? 'selected' : '' }}>
                                {{ $name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Reset --}}
                @if (request()->has('year') || request()->has('person') || request('mode') || request('start'))
                    <div class="flex flex-col min-w-[110px] max-w-xs">
                        <label class="invisible mb-1">ล้างตัวกรอง</label>
                        <a href="{{ route('fte.index', ['mode' => 'month']) }}"
                            class="w-full px-4 py-2 rounded-lg bg-gray-100 text-blue-700 font-bold shadow hover:bg-blue-200 text-center">
                            ล้างตัวกรอง
                        </a>
                    </div>
                @endif
            </form>
        </div>

        <!-- Main Table -->
        <div class="overflow-x-auto bg-white rounded-xl shadow border">
            <table class="min-w-full table-auto text-sm">
                <thead class="bg-gray-100 text-gray-800">
                    <tr>
                        <th class="px-4 py-3 text-left">ชื่อ</th>
                        <th class="px-4 py-3 text-right">FTE สะสม</th>
                        <th class="px-4 py-3 text-right">FTE เดือนนี้</th>
                        <th class="px-4 py-3 text-center">รายละเอียด</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($fteSummary as $row)
                        <tr class="border-t group hover:bg-blue-50 transition">
                            <td class="px-4 py-2 font-medium flex items-center gap-2">
                                @php
                                    $profileImage = $profiles[$row->name] ?? null;
                                @endphp
                                @if ($profileImage)
                                    <img src="{{ asset('storage/' . $profileImage) }}?v={{ time() }}"
                                        onerror="this.onerror=null;this.src='{{ asset('images/default-avatar.png') }}'"
                                        class="w-8 h-8 rounded-full border shadow object-cover" alt="avatar">
                                @else
                                    <img src="https://ui-avatars.com/api/?name={{ urlencode($row->name) }}&background=random&size=64"
                                        class="w-8 h-8 rounded-full border shadow object-cover" alt="avatar">
                                @endif

                                <a href="{{ route('fte.detail', ['person' => $row->name]) }}"
                                    class="text-blue-800 hover:underline font-bold">
                                    {{ $row->name }}
                                </a>
                            </td>
                            <td class="px-4 py-2 text-right font-semibold text-green-600">
                                {{ number_format($row->total_fte, 2) }}
                            </td>
                            <td class="px-4 py-2 text-right">
                                {{ number_format($row->fte_this_month ?? 0, 2) }}
                            </td>
                            <td class="px-4 py-2 text-center">
                                <button type="button"
                                    class="px-3 py-1 rounded bg-blue-100 text-blue-700 hover:bg-blue-200 font-bold"
                                    onclick="toggleDetail('{{ $loop->index }}')"
                                    data-detail="{{ $loop->index }}">ดูรายละเอียด</button>
                            </td>
                        </tr>

                        {{-- Detail Row --}}
                        <tr id="detail-row-{{ $loop->index }}" class="hidden bg-blue-50 animate-fade-in">
                            <td colspan="4" class="px-6 py-4">
                                @if (isset($monthlyFTE[$row->name]))
                                    @foreach ($monthlyFTE[$row->name] as $month => $items)
                                        @php
                                            $thaiMonth = '-';
                                            $thaiYear = '-';
                                            if (preg_match('/^\d{4}-\d{2}$/', $month)) {
                                                $carbon = \Carbon\Carbon::createFromFormat('Y-m-d', $month . '-01');
                                                $thaiYear = $carbon->year + 543;
                                                $thaiMonth = $carbon->locale('th')->translatedFormat('F');
                                            }
                                            $sum = collect($items)->sum('fte_month');
                                        @endphp

                                        <div class="mb-4 rounded-xl border border-blue-100 bg-blue-50">
                                            <div class="flex items-center gap-2 px-4 py-2 border-b border-blue-100 bg-blue-100 rounded-t-xl font-semibold">
                                                {{ $thaiMonth }} {{ $thaiYear }}
                                                <span class="ml-auto text-green-600 font-bold">
                                                    FTE: {{ number_format($sum, 2) }}
                                                </span>
                                            </div>
                                            <div class="p-2">
                                                <table class="w-full table-fixed text-xs border border-gray-200 rounded-lg overflow-hidden">
<thead class="bg-gray-100">
    <tr>
        <th class="p-2 text-left w-2/6">ชื่อโปรเจค</th>
        <th class="p-2 text-left w-1/6">กลุ่ม</th>
        <th class="p-2 text-left w-1/6">ภาระงาน</th>
        <th class="p-2 text-center w-1/12">วันที่เริ่ม</th>
        <th class="p-2 text-center w-1/12">วันที่สิ้นสุด</th>
        <th class="p-2 text-center w-1/12">จำนวนวันทำงาน</th>   {{-- ⭐ เพิ่มใหม่ --}}
        <th class="p-2 text-right w-1/12">FTE</th>
    </tr>
</thead>
<tbody>
    @foreach ($items as $item)
        <tr class="{{ $loop->even ? 'bg-white' : 'bg-gray-50' }}">
            <td class="p-2 text-blue-700 underline break-words whitespace-normal">
                <a href="{{ route('projects.show', $item->project->id) }}">
                    {{ $item->project->project_name_th ?? '-' }}
                </a>
            </td>

            <td class="p-2 break-words whitespace-normal">
                {{ $item->role_group ?? '-' }}
            </td>

            <td class="p-2 break-words whitespace-normal">
                {{ $item->job_detail ?? '-' }}
            </td>

            <td class="p-2 text-center whitespace-nowrap">
                {{ $item->start_date && \Carbon\Carbon::hasFormat($item->start_date, 'd-m-Y')
                    ? \Carbon\Carbon::createFromFormat('d-m-Y', $item->start_date)->locale('th')->isoFormat('D MMM YYYY')
                    : $item->start_date }}
            </td>

            <td class="p-2 text-center whitespace-nowrap">
                {{ $item->end_date && \Carbon\Carbon::hasFormat($item->end_date, 'd-m-Y')
                    ? \Carbon\Carbon::createFromFormat('d-m-Y', $item->end_date)->locale('th')->isoFormat('D MMM YYYY')
                    : $item->end_date }}
            </td>

            {{-- ⭐ จำนวนวันทำงาน (days_in_month) --}}
            <td class="p-2 text-center font-semibold text-gray-700">
                {{ $item->days_in_month }} วัน
            </td>

            {{-- ⭐ FTE + bar + tooltip --}}
            <td class="p-2 text-right font-semibold relative group">
                {{ number_format($item->fte_month, 3) }}

                @php
                    $percent = min(100, ($item->fte_month / 1) * 100);
                @endphp

                <div class="mt-1 h-2 w-full bg-gray-200 rounded-full overflow-hidden">
                    <div class="h-full bg-blue-500" style="width: {{ $percent }}%"></div>
                </div>

                <div class="absolute left-1/2 -translate-x-1/2 bottom-full mb-2 hidden group-hover:block
                    bg-black text-white text-xs rounded py-1 px-2 whitespace-nowrap z-20 shadow">

                    <div><b>{{ $item->project->project_name_th ?? '-' }}</b></div>
                    <div>ทำงาน: {{ $item->days_in_month }} วัน</div>
                    <div>FTE/วัน: {{ number_format($item->fte_per_day, 4) }}</div>
                    <div>FTE เดือนนี้: {{ number_format($item->fte_month, 4) }}</div>
                    <div>
                        ช่วงซ้อน:
                        {{ $item->overlap_start->locale('th')->isoFormat('D MMM YYYY') }}
                        -
                        {{ $item->overlap_end->locale('th')->isoFormat('D MMM YYYY') }}
                    </div>
                </div>
            </td>
        </tr>
    @endforeach
</tbody>

                                                </table>
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="text-gray-400 italic text-center py-6">ไม่มีข้อมูล FTE สำหรับบุคคลนี้</div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="p-8 text-center text-gray-400">ไม่พบข้อมูล</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Flatpickr --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/th.js"></script>
    <script>
        function resetOtherFilters() {
            let mode = document.getElementById('mode').value;
            if (mode === 'year' || mode === 'month') {
                const s = document.getElementById('dateStart');
                const e = document.getElementById('dateEnd');
                if (s) s.value = '';
                if (e) e.value = '';
            }
            if (mode === 'month' || mode === 'custom') {
                if (document.getElementById('yearSelect')) document.getElementById('yearSelect').value = '';
            }
        }

        function toggleDetail(idx) {
            const all = document.querySelectorAll('[id^="detail-row-"]');
            const row = document.getElementById('detail-row-' + idx);
            const willShow = row.classList.contains('hidden');

            all.forEach(el => el.classList.add('hidden'));
            if (willShow) row.classList.remove('hidden');
        }

        // ปิดลิ้นชักเมื่อคลิกนอกกรอบ
        document.addEventListener('click', function (e) {
            const rows = document.querySelectorAll('[id^="detail-row-"]');
            rows.forEach(row => {
                if (row.classList.contains('hidden')) return;
                const idx = row.id.replace('detail-row-', '');
                const btn = document.querySelector('button[data-detail="' + idx + '"]');
                if (!row.contains(e.target) && (!btn || !btn.contains(e.target))) {
                    row.classList.add('hidden');
                }
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            function updateFilter() {
                let mode = document.getElementById('mode').value;
                document.getElementById('filter-year').classList.toggle('hidden', mode !== 'year');
                document.getElementById('filter-month').classList.toggle('hidden', mode !== 'month');
                document.getElementById('filter-custom').classList.toggle('hidden', mode !== 'custom');
            }
            document.getElementById('mode').addEventListener('change', updateFilter);
            updateFilter();

            flatpickr("#dateStart", {
                dateFormat: "d/m/Y",
                locale: "th",
                allowInput: true,
                onClose: function(selectedDates, dateStr) {
                    let end = document.getElementById('dateEnd').value.trim();
                    if (dateStr && end) document.getElementById('filter-form').submit();
                }
            });
            flatpickr("#dateEnd", {
                dateFormat: "d/m/Y",
                locale: "th",
                allowInput: true,
                onClose: function(selectedDates, dateStr) {
                    let start = document.getElementById('dateStart').value.trim();
                    if (start && dateStr) document.getElementById('filter-form').submit();
                }
            });
        });
    </script>
    <style>
        #filter-form label { font-size: 0.97rem; }
        #filter-form select, #filter-form input { min-width: 100px; }

        @media (max-width: 800px) {
            #filter-form { flex-direction: column; gap: 8px; }
            #filter-form>div { width: 100%; }
        }

        @keyframes fade-in {
            from { opacity: 0; transform: translateY(24px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in { animation: fade-in 0.35s ease; }
    </style>
@endsection
