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

        <h2 class="text-xl mb-2 md:text-2xl font-bold">📊 สรุป FTE รายโปรเจค</h2>

        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm mb-6 px-4 py-6">
            <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                <div>
                    <div class="text-sm font-semibold text-slate-800">ตัวกรองข้อมูล</div>
                    <div class="text-xs text-slate-500">ปรับช่วงเวลา เลือกโปรเจค และค้นหาได้ทันที</div>
                </div>
                <div class="hidden md:flex items-center gap-2 text-xs text-slate-500">
                    <span class="inline-flex items-center gap-1 rounded-full border border-slate-200 bg-slate-50 px-3 py-1">
                        <span class="h-2 w-2 rounded-full bg-emerald-500"></span> อัปเดตล่าสุด
                    </span>
                </div>
            </div>
            <form method="GET" id="filter-form" autocomplete="off"
                class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                <div class="flex flex-col md:col-span-3">
                    <label class="text-slate-700 font-semibold mb-1">ดูข้อมูล</label>
                    <select name="mode" id="mode"
                        class="rounded-xl border-slate-200 bg-slate-50 p-2.5 focus:ring-2 focus:ring-blue-300 transition w-full"
                        onchange="resetOtherFilters(); this.form.submit();">
                        <option value="year" {{ $mode == 'year' ? 'selected' : '' }}>รายปี</option>
                        <option value="month" {{ $mode == 'month' ? 'selected' : '' }}>เดือนนี้</option>
                        <option value="custom" {{ $mode == 'custom' ? 'selected' : '' }}>เลือกช่วงวันที่</option>
                    </select>
                </div>

                <div id="filter-year"
                    class="flex flex-col md:col-span-2 {{ $mode === 'year' ? '' : 'hidden' }}">
                    <label class="text-slate-700 mb-1">ปี (พ.ศ.)</label>
                    <select name="year" id="yearSelect" class="rounded-xl border-slate-200 bg-slate-50 p-2.5 w-full"
                        onchange="this.form.submit();">
                        @foreach ($years as $y)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>

                <div id="filter-month"
                    class="flex flex-col md:col-span-3 {{ $mode === 'month' ? '' : 'hidden' }}">
                    <label class="text-slate-700 mb-1">เดือน</label>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-2.5 px-3 text-base font-semibold text-slate-800 w-full">
                        @php
                            $now = \Carbon\Carbon::now();
                            $monthLabel = $now->locale('th')->translatedFormat('F');
                            $yearLabel = $now->year + 543;
                        @endphp
                        {{ $monthLabel }} {{ $yearLabel }}
                    </div>
                </div>

                <div id="filter-custom"
                    class="flex flex-col md:col-span-4 {{ $mode === 'custom' ? '' : 'hidden' }}">
                    <label class="text-slate-700 mb-1">ช่วงวันที่</label>
                    <div class="flex gap-2 items-end w-full">
                        <div class="relative w-[130px]">
                            <input name="start" id="dateStart"
                                class="rounded-xl border-slate-200 bg-slate-50 p-2.5 w-full pl-9 focus:ring-2 focus:ring-blue-200"
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
                                class="rounded-xl border-slate-200 bg-slate-50 p-2.5 w-full pl-9 focus:ring-2 focus:ring-blue-200"
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

                <div class="flex flex-col md:col-span-3">
                    <label class="text-slate-700 mb-1">โปรเจค</label>
                    <select name="project_id" id="projectSelect" class="rounded-xl border-slate-200 bg-slate-50 p-2.5 w-full"
                        onchange="this.form.submit();">
                        <option value="">ทั้งหมด</option>
                        @foreach ($projects as $project)
                            <option value="{{ $project->id }}" {{ $projectId == $project->id ? 'selected' : '' }}>
                                {{ $project->project_name_th }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex flex-col md:col-span-3">
                    <label class="text-slate-700 mb-1">ค้นหาในตาราง</label>
                    <input id="fteProjectSearch" type="search" placeholder="พิมพ์ชื่อโปรเจค"
                        class="rounded-xl border-slate-200 bg-slate-50 p-2.5 w-full">
                </div>

                <div class="flex flex-col md:col-span-3">
                    <label class="text-slate-700 mb-1">เรียงลำดับ FTE</label>
                    <select id="fteSortSelect"
                        class="rounded-xl border-slate-200 bg-slate-50 p-2.5 w-full">
                        <option value="total_desc">FTE ทั้งหมด: มาก → น้อย</option>
                        <option value="total_asc">FTE ทั้งหมด: น้อย → มาก</option>
                        <option value="filtered_desc">FTE ตามตัวกรอง: มาก → น้อย</option>
                        <option value="filtered_asc">FTE ตามตัวกรอง: น้อย → มาก</option>
                        <option value="month_desc">FTE เดือนนี้: มาก → น้อย</option>
                        <option value="month_asc">FTE เดือนนี้: น้อย → มาก</option>
                        <option value="name_asc">ชื่อโปรเจค: A → Z</option>
                    </select>
                </div>

                @if (request()->has('year') || request()->has('project_id') || request('mode') || request('start'))
                    <div class="flex flex-col md:col-span-3">
                        <label class="invisible mb-1">ล้างตัวกรอง</label>
                        <a href="{{ route('fte.project.index', ['mode' => 'month']) }}"
                            class="w-full px-4 py-2.5 rounded-xl bg-slate-100 text-slate-700 font-semibold hover:bg-slate-200 text-center">
                            ล้างตัวกรอง
                        </a>
                    </div>
                @endif
            </form>
        </div>

        <div class="bg-white rounded-xl shadow border mb-4">
            <div class="flex flex-wrap items-center justify-between gap-4 px-4 py-3">
                <div class="text-sm text-slate-600">
                    สรุปทั้งหมด (ตามตัวกรอง)
                </div>
                <div class="flex flex-wrap items-center gap-3 text-sm font-semibold text-slate-800">
                    <span class="px-3 py-1 rounded-full bg-emerald-50 text-emerald-700">
                        FTE รวมทั้งหมด: {{ number_format($summaryTotalFte, 4) }}
                    </span>
                    <span class="px-3 py-1 rounded-full bg-blue-50 text-blue-700">
                        FTE เดือนนี้: {{ number_format($summaryMonthFte, 4) }}
                    </span>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto bg-white rounded-xl shadow border">
            <table class="min-w-full table-auto text-sm">
                <thead class="bg-emerald-50 text-emerald-900">
                    <tr>
                        <th class="px-4 py-3 text-left">โปรเจค</th>
                        <th class="px-4 py-3 text-center">ปีโปรเจค (พ.ศ.)</th>
                        <th class="px-4 py-3 text-right">FTE ทั้งหมดตั้งแต่เริ่มโปรเจค</th>
                        <th class="px-4 py-3 text-right">FTE ตามตัวกรอง</th>
                        <th class="px-4 py-3 text-right">FTE เดือนนี้</th>
                        <th class="px-4 py-3 text-center">รายละเอียด</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($fteSummary as $row)
                        <tr class="border-t group hover:bg-blue-50 transition"
                            data-project-name="{{ $row->project_name }}"
                            data-detail="{{ $loop->index }}"
                            data-total-fte="{{ $row->total_fte ?? 0 }}"
                            data-filtered-fte="{{ $row->filtered_fte ?? 0 }}"
                            data-month-fte="{{ $row->fte_this_month ?? 0 }}">
                            <td class="px-4 py-2 font-medium">
                                <a href="{{ route('fte.project.detail', $row->project_id) }}"
                                    class="text-blue-800 hover:underline font-bold">
                                    {{ $row->project_name }}
                                </a>
                            </td>
                            <td class="px-4 py-2 text-center text-slate-700">
                                {{ $row->project_year ?? '-' }}
                            </td>
                            <td class="px-4 py-2 text-right font-semibold text-green-600">
                                {{ number_format($row->total_fte, 7) }}
                            </td>
                            <td class="px-4 py-2 text-right font-semibold text-slate-700">
                                {{ number_format($row->filtered_fte ?? 0, 7) }}
                            </td>
                            <td class="px-4 py-2 text-right">
                                {{ number_format($row->fte_this_month ?? 0, 7) }}
                            </td>
                            <td class="px-4 py-2 text-center">
                                <button type="button"
                                    class="px-3 py-1 rounded bg-blue-100 text-blue-700 hover:bg-blue-200 font-bold"
                                    onclick="toggleDetail('{{ $loop->index }}')"
                                    data-detail="{{ $loop->index }}">ดูรายละเอียด</button>
                            </td>
                        </tr>

                        <tr id="detail-row-{{ $loop->index }}" class="hidden bg-blue-50 animate-fade-in">
                            <td colspan="6" class="px-6 py-4">
                                @if (isset($monthlyFTE[$row->project_id]))
                                    @foreach ($monthlyFTE[$row->project_id] as $month => $items)
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
                                            <div class="p-2 w-full">
                                                <table class="min-w-full w-full table-auto text-sm border border-gray-200 rounded-lg overflow-hidden">
                                                    <thead class="bg-gray-100">
                                                        <tr>
                                                            <th class="p-3 text-left font-semibold text-slate-700 min-w-[160px]">ชื่อบุคคล</th>
                                                            <th class="p-3 text-left font-semibold text-slate-700 min-w-[120px]">กลุ่ม</th>
                                                            <th class="p-3 text-left font-semibold text-slate-700 min-w-[200px]">ภาระงาน</th>
                                                            <th class="p-3 text-center font-semibold text-slate-700 min-w-[120px]">วันที่เริ่ม</th>
                                                            <th class="p-3 text-center font-semibold text-slate-700 min-w-[120px]">วันที่สิ้นสุด</th>
                                                            <th class="p-3 text-center font-semibold text-slate-700 min-w-[100px]">จำนวนวันทำงาน</th>
                                                            <th class="p-3 text-right font-semibold text-slate-700 min-w-[80px]">FTE</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($items as $item)
                                                            @php
                                                                $startDisplay = $item->start_date;
                                                                if ($item->start_date) {
                                                                    $d = \Carbon\Carbon::parse($item->start_date);
                                                                    $startDisplay = $d->locale('th')->translatedFormat('j F') . ' ' . ($d->year + 543);
                                                                }
                                                                $endDisplay = $item->end_date;
                                                                if ($item->end_date) {
                                                                    $d = \Carbon\Carbon::parse($item->end_date);
                                                                    $endDisplay = $d->locale('th')->translatedFormat('j F') . ' ' . ($d->year + 543);
                                                                }
                                                            @endphp
                                                            <tr class="{{ $loop->even ? 'bg-white' : 'bg-gray-50' }}">
                                                                <td class="p-3 break-words whitespace-normal">
                                                                    @if (!empty($item->name))
                                                                        <a href="{{ route('fte.detail', ['person' => $item->name]) }}"
                                                                            class="text-blue-700 underline hover:text-blue-900">
                                                                            {{ $item->name }}
                                                                        </a>
                                                                    @else
                                                                        -
                                                                    @endif
                                                                </td>
                                                                <td class="p-3 break-words whitespace-normal">
                                                                    {{ $item->role_group ?? '-' }}
                                                                </td>
                                                                <td class="p-3 break-words whitespace-normal">
                                                                    <div>{{ $item->job_detail ?? '-' }}</div>
                                                                    <div class="text-gray-500 text-xs mt-1">
                                                                        {{ $item->task_detail ?? '-' }}
                                                                    </div>
                                                                </td>
                                                                <td class="p-3 text-center whitespace-nowrap">
                                                                    {{ $startDisplay }}
                                                                </td>
                                                                <td class="p-3 text-center whitespace-nowrap">
                                                                    {{ $endDisplay }}
                                                                </td>
                                                                <td class="p-3 text-center font-semibold text-gray-700">
                                                                    {{ $item->days_in_month }} วัน
                                                                </td>
                                                                <td class="p-3 text-right font-semibold relative group">
                                                                    {{ number_format($item->fte_month, 7) }}
                                                                    @php
                                                                        $percent = min(100, ($item->fte_month / 1) * 100);
                                                                    @endphp
                                                                    <div class="mt-1 h-2 w-full bg-gray-200 rounded-full overflow-hidden">
                                                                        <div class="h-full bg-blue-500" style="width: {{ $percent }}%"></div>
                                                                    </div>
                                                                    <div class="absolute left-1/2 -translate-x-1/2 bottom-full mb-2 hidden group-hover:block
                                                                        bg-black text-white text-xs rounded py-1 px-2 whitespace-nowrap z-20 shadow">
                                                                        <div><b>{{ $item->name ?? '-' }}</b></div>
                                                                        <div>ทำงาน: {{ $item->days_in_month }} วัน</div>
                                                                        <div>FTE/วัน: {{ number_format($item->fte_per_day, 10) }}</div>
                                                                        <div>FTE เดือนนี้: {{ number_format($item->fte_month, 6) }}</div>
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
                                    <div class="text-gray-400 italic text-center py-6">ไม่มีข้อมูล FTE สำหรับโปรเจคนี้</div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-8 text-center text-gray-400">ไม่พบข้อมูล</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($fteSummary->hasPages())
            <div class="mt-4">
                {{ $fteSummary->onEachSide(1)->links() }}
            </div>
        @endif
    </div>

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

        function sortProjectRows(rows, mode) {
            const list = Array.from(rows);
            const by = {
                total_desc: (a, b) => Number(b.dataset.totalFte || 0) - Number(a.dataset.totalFte || 0),
                total_asc: (a, b) => Number(a.dataset.totalFte || 0) - Number(b.dataset.totalFte || 0),
                filtered_desc: (a, b) => Number(b.dataset.filteredFte || 0) - Number(a.dataset.filteredFte || 0),
                filtered_asc: (a, b) => Number(a.dataset.filteredFte || 0) - Number(b.dataset.filteredFte || 0),
                month_desc: (a, b) => Number(b.dataset.monthFte || 0) - Number(a.dataset.monthFte || 0),
                month_asc: (a, b) => Number(a.dataset.monthFte || 0) - Number(b.dataset.monthFte || 0),
                name_asc: (a, b) => String(a.dataset.projectName || '').localeCompare(String(b.dataset.projectName || '')),
            };
            list.sort(by[mode] || by.total_desc);
            return list;
        }

        function applyProjectFilter() {
            const searchEl = document.getElementById('fteProjectSearch');
            const query = (searchEl?.value || '').toLowerCase().trim();
            const sortEl = document.getElementById('fteSortSelect');
            const sortMode = sortEl?.value || 'total_desc';
            const rows = document.querySelectorAll('tr[data-project-name]');
            const tbody = document.querySelector('table tbody');
            const visibleRows = [];

            rows.forEach(row => {
                const name = (row.dataset.projectName || '').toLowerCase();
                const detail = document.querySelector(`#detail-row-${row.getAttribute('data-detail')}`);
                const match = !query || name.includes(query);
                row.classList.toggle('hidden', !match);
                if (detail) detail.classList.toggle('hidden', !match);
                if (match) visibleRows.push(row);
            });

            if (tbody) {
                sortProjectRows(visibleRows, sortMode).forEach(row => {
                    tbody.appendChild(row);
                    const detail = document.querySelector(`#detail-row-${row.getAttribute('data-detail')}`);
                    if (detail) tbody.appendChild(detail);
                });
            }
        }

        document.getElementById('fteProjectSearch')?.addEventListener('input', applyProjectFilter);
        document.getElementById('fteSortSelect')?.addEventListener('change', applyProjectFilter);
    </script>
@endsection
