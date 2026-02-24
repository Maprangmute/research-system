@extends('layouts.app')

@section('content')
    <style>
        @media (max-width: 768px) {

            .fte-index-table thead {
                display: none;
            }

            .fte-index-table,
            .fte-index-table tbody,
            .fte-index-table tr,
            .fte-index-table td {
                display: block;
                width: 100%;
            }

            .fte-index-table tr {
                border: 1px solid #e5e7eb;
                border-radius: 12px;
                padding: 10px 12px;
                margin-bottom: 10px;
                background: #ffffff;
            }

            .fte-index-table td {
                display: flex;
                justify-content: space-between;
                gap: 12px;
                padding: 6px 0;
                text-align: left;
            }

            .fte-index-table td::before {
                content: attr(data-label);
                font-size: 11px;
                text-transform: uppercase;
                color: #94a3b8;
            }

            .fte-index-table .fte-detail-row td {
                display: block;
            }

            .fte-index-table .fte-detail-row td::before {
                content: none;
            }

            .fte-detail-table thead {
                display: none;
            }

            .fte-detail-table,
            .fte-detail-table tbody,
            .fte-detail-table tr,
            .fte-detail-table td {
                display: block;
                width: 100%;
            }

            .fte-detail-table tr {
                border: 1px solid #e5e7eb;
                border-radius: 12px;
                padding: 10px 12px;
                margin-bottom: 10px;
                background: #ffffff;
            }

            .fte-detail-table td {
                display: flex;
                justify-content: space-between;
                gap: 12px;
                padding: 6px 0;
                text-align: left;
            }

            .fte-detail-table td::before {
                content: attr(data-label);
                font-size: 11px;
                text-transform: uppercase;
                color: #94a3b8;
            }
        }
    </style>
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
                    <label class="text-gray-800 mb-1">ปี (พ.ศ.)</label>
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
                        @php
                            $now = \Carbon\Carbon::now();
                            $monthLabel = $now->locale('th')->translatedFormat('F');
                            $yearLabel = $now->year + 543;
                        @endphp
                        {{ $monthLabel }} {{ $yearLabel }}
                    </div>
                </div>

                {{-- Custom Date --}}
                <div id="filter-custom"
                    class="flex flex-col flex-1 min-w-[220px] max-w-sm {{ $mode === 'custom' ? '' : 'hidden' }}">
                    <label class="text-gray-800 mb-1">ช่วงวันที่</label>
                    <div class="flex gap-2 items-end w-full">
                        <div class="relative w-[130px]">
                            <input name="start" id="dateStart"
                                class="js-date-be rounded-lg border-gray-300 p-2 w-full pl-9 focus:ring-2 focus:ring-blue-200"
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
                                class="js-date-be rounded-lg border-gray-300 p-2 w-full pl-9 focus:ring-2 focus:ring-blue-200"
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
            <table class="fte-index-table min-w-full table-auto text-sm">
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
                            <td class="px-4 py-2 font-medium flex items-center gap-2" data-label="ชื่อ">
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
                            <td class="px-4 py-2 text-right font-semibold text-green-600" data-label="FTE สะสม">
                                {{ number_format($row->total_fte, 7) }}
                            </td>
                            <td class="px-4 py-2 text-right" data-label="FTE เดือนนี้">
                                {{ number_format($row->fte_this_month ?? 0, 7) }}
                            </td>
                            <td class="px-4 py-2 text-center" data-label="รายละเอียด">
                                <button type="button"
                                    class="px-3 py-1 rounded bg-blue-100 text-blue-700 hover:bg-blue-200 font-bold"
                                    onclick="toggleDetail('{{ $loop->index }}')"
                                    data-detail="{{ $loop->index }}">ดูรายละเอียด</button>
                            </td>
                        </tr>

                        {{-- Detail Row --}}
                        <tr id="detail-row-{{ $loop->index }}" class="fte-detail-row bg-blue-50 animate-fade-in">
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
                                            <div class="flex items-center gap-2 px-4 py-3 border-b border-blue-100 bg-blue-100 rounded-t-xl font-semibold text-base md:text-lg">
                                                {{ $thaiMonth }} {{ $thaiYear }}
                                                <span class="ml-auto text-green-600 font-bold">
                                                    FTE: {{ number_format($sum, 2) }}
                                                </span>
                                            </div>
                                            <div class="p-2">
                                                <table class="fte-detail-table w-full table-auto text-sm border border-slate-200 rounded-lg overflow-hidden">
<thead class="bg-gray-100">
    <tr>
        <th class="p-3 text-left font-semibold text-slate-700 min-w-[260px]">ชื่อโปรเจค / งานนอกโครงการ</th>
        <th class="p-3 text-left font-semibold text-slate-700 min-w-[120px]">กลุ่ม</th>
        <th class="p-3 text-left font-semibold text-slate-700 min-w-[240px]">รายละเอียดงาน</th>
        <th class="p-3 text-center font-semibold text-slate-700 min-w-[110px]">จำนวนวันทำงาน</th>
        <th class="p-3 text-right font-semibold text-slate-700 min-w-[90px]">FTE</th>
    </tr>
</thead>
<tbody>
    @foreach ($items as $item)
        <tr class="{{ $loop->even ? 'bg-white' : 'bg-gray-50' }}">
            <td class="p-3 break-words whitespace-normal align-top" data-label="โปรเจค/งาน">
                @if ($item->project?->id)
                    <a href="{{ route('projects.show', $item->project) }}" class="text-blue-700 underline">
                        {{ $item->project->project_name_th ?? '-' }}
                    </a>
                @else
                    @php
                        $backOfficeTitle = $item->task_detail ?? '-';
                    @endphp
                                            @php
                                                $backOfficeId = $item->task_id ?? ($item->task?->id ?? null);
                                            @endphp
                                            @if (!is_null($backOfficeId))
                                                <a href="{{ route('back_office.show', $backOfficeId) }}"
                                                    class="text-blue-700 underline relative z-20 pointer-events-auto"
                                                    onclick="event.stopPropagation();">
                                                    {{ $backOfficeTitle }}
                                                </a>
                    @else
                        <span class="text-slate-800">{{ $backOfficeTitle }}</span>
                    @endif
                    <div class="text-gray-400 text-xs">งานนอกโครงการ</div>
                @endif
            </td>

            <td class="p-3 break-words whitespace-normal align-top" data-label="กลุ่ม">
                {{ trim((string) ($item->role_group ?? '')) !== '' ? $item->role_group : '-' }}
            </td>

            <td class="p-3 break-words whitespace-normal align-top" data-label="รายละเอียดงาน">
                @php
                    $jobDetail = trim((string) ($item->job_detail ?? ''));
                    $taskDetail = trim((string) ($item->task_detail ?? ''));
                    $hasJob = $jobDetail !== '';
                    $hasTask = $taskDetail !== '';
                @endphp
                <div class="text-slate-700">{{ $hasJob ? $jobDetail : '-' }}</div>
                @if ($hasTask)
                    <div class="text-slate-500 text-sm mt-0.5">{{ $taskDetail }}</div>
                @endif
            </td>

            {{-- ⭐ จำนวนวันทำงาน (days_in_month) --}}
            <td class="p-3 text-center font-semibold text-slate-700 align-top" data-label="วันทำงาน">
                {{ $item->days_in_month }} วัน
            </td>

            {{-- ⭐ FTE --}}
            <td class="p-3 text-right font-semibold align-top" data-label="FTE">
                {{ number_format($item->fte_month, 7) }}
                {{-- <div class="text-xs text-gray-400">
                   {{ number_format($item->fte_per_day, 2) }}  FTE / วัน
                </div> --}}
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
            const all = document.querySelectorAll('.fte-detail-row');
            const row = document.getElementById('detail-row-' + idx);
            const buttons = document.querySelectorAll('button[data-detail]');
            const willShow = !row.classList.contains('is-open');

            all.forEach(el => el.classList.remove('is-open'));
            buttons.forEach(btn => {
                btn.textContent = 'ดูรายละเอียด';
                btn.setAttribute('aria-expanded', 'false');
            });
            if (willShow) {
                row.classList.add('is-open');
                const btn = document.querySelector(`button[data-detail="${idx}"]`);
                if (btn) {
                    btn.textContent = 'ซ่อนรายละเอียด';
                    btn.setAttribute('aria-expanded', 'true');
                }
            }
        }

        // ปิดลิ้นชักเมื่อคลิกนอกกรอบ
        document.addEventListener('click', function (e) {
            const rows = document.querySelectorAll('[id^="detail-row-"]');
            rows.forEach(row => {
                if (!row.classList.contains('is-open')) return;
                const idx = row.id.replace('detail-row-', '');
                const btn = document.querySelector('button[data-detail="' + idx + '"]');
                if (!row.contains(e.target) && (!btn || !btn.contains(e.target))) {
                    row.classList.remove('is-open');
                    if (btn) {
                        btn.textContent = 'ดูรายละเอียด';
                        btn.setAttribute('aria-expanded', 'false');
                    }
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
            document.querySelectorAll('.fte-detail-row').forEach(row => row.classList.remove('is-open'));
            document.querySelectorAll('button[data-detail]').forEach(btn => {
                btn.textContent = 'ดูรายละเอียด';
                btn.setAttribute('aria-expanded', 'false');
            });
            document.getElementById('mode').addEventListener('change', updateFilter);
            updateFilter();
        });
    </script>
    <style>
        .fte-index-table tr.fte-detail-row {
            display: none;
        }

        .fte-index-table tr.fte-detail-row.is-open {
            display: table-row;
        }

        @media (max-width: 768px) {
            .fte-index-table tr.fte-detail-row.is-open {
                display: block;
            }
        }

        @keyframes fade-in {
            from { opacity: 0; transform: translateY(24px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in { animation: fade-in 0.35s ease; }
    </style>
@endsection
