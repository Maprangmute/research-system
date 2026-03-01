@extends('layouts.app')

@section('content')
    @php
        $allRows = $monthlyFTE->flatten(1);
        $personCount = $allRows->pluck('name')->filter()->unique()->count();
        $monthCount = $monthlyFTE->count();
        $totalFte = $allRows->sum('fte_value');
        $totalHours = $allRows->sum('hours_in_month');
        $currentMonthKey = now()->format('Y-m');
        $currentMonthFte = ($monthlyFTE[$currentMonthKey] ?? collect())->sum('fte_value');
    @endphp

    <div class="max-w-7xl mx-auto p-4 space-y-6">
        <div class="bg-white border shadow-sm rounded-2xl p-5">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <a href="{{ route('fte.project.index') }}"
                        class="inline-flex items-center gap-2 text-blue-700 font-semibold text-sm hover:text-blue-900">
                        ← กลับไปหน้ารายการ FTE โปรเจค
                    </a>
                    <h1 class="text-2xl font-semibold text-slate-900 mt-2">
                        FTE รายโปรเจค: {{ $project->project_name_th ?? '-' }}
                    </h1>
                    <p class="text-xs text-slate-500">สรุป FTE ตามช่วงเวลาและผู้มีส่วนร่วม</p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('fte.project.pdf', $project->id) }}" target="_blank" rel="noopener"
                        class="inline-flex items-center gap-2 px-3 py-2 text-sm bg-emerald-600 text-white rounded-lg hover:bg-emerald-700">
                        ดาวน์โหลด PDF
                    </a>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div class="bg-white border rounded-xl p-4">
                <div class="text-xs text-slate-500">จำนวนเดือนที่มีงาน</div>
                <div class="text-2xl font-semibold text-slate-900">{{ number_format($monthCount) }}</div>
            </div>
            <div class="bg-white border rounded-xl p-4">
                <div class="text-xs text-slate-500">จำนวนบุคคล</div>
                <div class="text-2xl font-semibold text-slate-900">{{ number_format($personCount) }}</div>
            </div>
            <div class="bg-white border rounded-xl p-4">
                <div class="text-xs text-slate-500">FTE เดือนนี้</div>
                <div class="text-2xl font-semibold text-slate-900">{{ number_format($currentMonthFte, 7) }}</div>
            </div>
            <div class="bg-white border rounded-xl p-4">
                <div class="text-xs text-slate-500">FTE รวมทั้งหมด</div>
                <div class="text-2xl font-semibold text-slate-900">{{ number_format($totalFte, 7) }}</div>
            </div>
            <div class="bg-white border rounded-xl p-4">
                <div class="text-xs text-slate-500">ชั่วโมงรวมทั้งหมด</div>
                <div class="text-2xl font-semibold text-slate-900">{{ number_format($totalHours, 2) }}</div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            <div class="lg:col-span-2 bg-white border rounded-2xl p-4 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-lg font-semibold text-slate-800">📊 สรุป FTE รายเดือน (Stacked)</h3>
                </div>
                <div class="bg-slate-50 rounded-xl border p-3" style="height:300px;">
                    <canvas id="fteStackedChart"></canvas>
                </div>
            </div>
            <div class="bg-white border rounded-2xl p-4 shadow-sm">
                <h3 class="text-lg font-semibold text-slate-800 mb-3">📈 FTE รวม 3 ปีล่าสุด</h3>
                <div class="bg-slate-50 rounded-xl border p-3" style="height:300px;">
                    <canvas id="fteYearChart"></canvas>
                </div>
            </div>
        </div>

        <div class="md:hidden bg-white rounded-2xl shadow-sm border p-4">
            <label class="block">
                <span class="text-xs font-semibold text-slate-600">ค้นหา</span>
                <input id="fteSearchMobile" type="text" placeholder="ชื่อ / กลุ่ม / ภาระงาน"
                    class="w-full border border-slate-200 rounded-lg p-2 text-sm">
            </label>
        </div>

        <details class="md:hidden bg-white rounded-2xl shadow-sm border p-4">
            <summary class="text-sm font-semibold text-slate-700 cursor-pointer">ตัวกรองข้อมูล</summary>
            <div class="grid grid-cols-1 gap-3 mt-4">
                <label class="block">
                    <span class="text-xs font-semibold text-slate-600">บุคคล</span>
                    <select id="ftePersonFilterMobile" class="w-full border border-slate-200 rounded-lg p-2 text-sm">
                        <option value="">ทั้งหมด</option>
                        @foreach ($personOptions as $name)
                            <option value="{{ $name }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="block">
                    <span class="text-xs font-semibold text-slate-600">กลุ่มงาน</span>
                    <select id="fteRoleFilterMobile" class="w-full border border-slate-200 rounded-lg p-2 text-sm">
                        <option value="">ทั้งหมด</option>
                        @foreach ($roleOptions as $role)
                            <option value="{{ $role }}">{{ $role }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="block">
                    <span class="text-xs font-semibold text-slate-600">ปี (พ.ศ.)</span>
                    <select id="fteYearFilterMobile" class="w-full border border-slate-200 rounded-lg p-2 text-sm">
                        <option value="">ทั้งหมด</option>
                        @foreach ($yearOptions as $year)
                            <option value="{{ $year }}">{{ $year + 543 }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="block">
                    <span class="text-xs font-semibold text-slate-600">เดือน</span>
                    <select id="fteMonthFilterMobile" class="w-full border border-slate-200 rounded-lg p-2 text-sm">
                        <option value="">ทั้งหมด</option>
                        @foreach ($monthOptions as $month)
                            @php
                                $mCarbon = \Carbon\Carbon::createFromFormat('Y-m', $month);
                                $mLabel = $mCarbon->locale('th')->translatedFormat('F') . ' ' . ($mCarbon->year + 543);
                            @endphp
                            <option value="{{ $month }}">{{ $mLabel }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="block">
                    <span class="text-xs font-semibold text-slate-600">เรียงลำดับรายการ</span>
                    <select id="fteSortMobile" class="w-full border border-slate-200 rounded-lg p-2 text-sm">
                        <option value="fte_desc">FTE มาก → น้อย</option>
                        <option value="fte_asc">FTE น้อย → มาก</option>
                        <option value="hours_desc">ชั่วโมง มาก → น้อย</option>
                        <option value="name_asc">ชื่อ A → Z</option>
                    </select>
                </label>
                <button id="fteResetFiltersMobile"
                    class="px-3 py-2 text-xs border rounded-lg hover:bg-slate-50">ล้างตัวกรอง</button>
            </div>
        </details>

        <div class="hidden md:block bg-white rounded-2xl shadow-sm border p-4">
            <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4">
                <div>
                    <h3 class="text-lg font-semibold text-slate-800">📅 รายการ FTE รายเดือน</h3>
                    <p class="text-xs text-slate-500">ค้นหาและกรองข้อมูลให้เห็นเฉพาะที่ต้องการ</p>
                </div>
                <button id="fteResetFilters"
                    class="px-3 py-2 text-xs border rounded-lg hover:bg-slate-50">ล้างตัวกรอง</button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-6 gap-3 mt-4">
                <label class="block md:col-span-2">
                    <span class="text-xs font-semibold text-slate-600">ค้นหา</span>
                    <input id="fteSearch" type="text" placeholder="ชื่อ / กลุ่ม / ภาระงาน"
                        class="w-full border border-slate-200 rounded-lg p-2 text-sm">
                </label>
                <label class="block">
                    <span class="text-xs font-semibold text-slate-600">บุคคล</span>
                    <select id="ftePersonFilter" class="w-full border border-slate-200 rounded-lg p-2 text-sm">
                        <option value="">ทั้งหมด</option>
                        @foreach ($personOptions as $name)
                            <option value="{{ $name }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="block">
                    <span class="text-xs font-semibold text-slate-600">กลุ่มงาน</span>
                    <select id="fteRoleFilter" class="w-full border border-slate-200 rounded-lg p-2 text-sm">
                        <option value="">ทั้งหมด</option>
                        @foreach ($roleOptions as $role)
                            <option value="{{ $role }}">{{ $role }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="block">
                    <span class="text-xs font-semibold text-slate-600">ปี (พ.ศ.)</span>
                    <select id="fteYearFilter" class="w-full border border-slate-200 rounded-lg p-2 text-sm">
                        <option value="">ทั้งหมด</option>
                        @foreach ($yearOptions as $year)
                            <option value="{{ $year }}">{{ $year + 543 }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="block">
                    <span class="text-xs font-semibold text-slate-600">เดือน</span>
                    <select id="fteMonthFilter" class="w-full border border-slate-200 rounded-lg p-2 text-sm">
                        <option value="">ทั้งหมด</option>
                        @foreach ($monthOptions as $month)
                            @php
                                $mCarbon = \Carbon\Carbon::createFromFormat('Y-m', $month);
                                $mLabel = $mCarbon->locale('th')->translatedFormat('F') . ' ' . ($mCarbon->year + 543);
                            @endphp
                            <option value="{{ $month }}">{{ $mLabel }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="block md:col-span-2">
                    <span class="text-xs font-semibold text-slate-600">เรียงลำดับรายการ</span>
                    <select id="fteSort" class="w-full border border-slate-200 rounded-lg p-2 text-sm">
                        <option value="fte_desc">FTE มาก → น้อย</option>
                        <option value="fte_asc">FTE น้อย → มาก</option>
                        <option value="hours_desc">ชั่วโมง มาก → น้อย</option>
                        <option value="name_asc">ชื่อ A → Z</option>
                    </select>
                </label>
            </div>
        </div>

        @php
            $monthPaginator = $monthlyFTEPage ?? null;
            $monthLoop = $monthPaginator ? $monthPaginator->items() : $monthlyFTE;
        @endphp

        {{-- Mobile cards --}}
        <div id="fte-months-mobile" class="md:hidden space-y-4">
            @forelse($monthLoop as $monthKey => $rows)
                @php
                    $carbon = \Carbon\Carbon::createFromFormat('Y-m', $monthKey);
                    $monthName = $carbon->locale('th')->translatedFormat('F');
                    $year = $carbon->year + 543;
                    $sumFTE = $rows->sum('fte_value');
                    $sumHours = $rows->sum('hours_in_month');
                @endphp

                <details class="month-card-mobile bg-white border rounded-2xl shadow-sm" open data-month="{{ $monthKey }}"
                    data-year="{{ $carbon->year }}">
                    <summary class="cursor-pointer px-4 py-3 flex flex-col gap-2">
                        <div class="text-sm font-semibold text-slate-800">
                            {{ $monthName }} {{ $year }}
                        </div>
                        <div class="text-xs text-slate-500">
                            {{ $rows->count() }} รายการ •
                            FTE {{ number_format($sumFTE, 7) }} •
                            ชั่วโมง {{ number_format($sumHours, 2) }}
                        </div>
                    </summary>

                    <div class="border-t p-3">
                        <div class="fte-card-list space-y-2">
                            @foreach ($rows as $item)
                                @php
                                    $os = \Carbon\Carbon::parse($item->overlap_start)
                                        ->locale('th')
                                        ->translatedFormat('j F') . ' ' . (\Carbon\Carbon::parse($item->overlap_start)->year + 543);
                                    $oe = \Carbon\Carbon::parse($item->overlap_end)
                                        ->locale('th')
                                        ->translatedFormat('j F') . ' ' . (\Carbon\Carbon::parse($item->overlap_end)->year + 543);
                                @endphp
                                <div class="fte-row-card bg-slate-50 border border-slate-200 rounded-xl p-3 text-sm text-slate-700"
                                    data-person="{{ $item->name }}"
                                    data-role="{{ $item->role_group }}"
                                    data-job="{{ $item->job_detail }}"
                                    data-month="{{ $monthKey }}"
                                    data-year="{{ $carbon->year }}"
                                    data-fte="{{ $item->fte_value }}"
                                    data-hours="{{ $item->hours_in_month }}">
                                    <div class="font-semibold text-slate-900">
                                        @if (!empty($item->name))
                                            <a href="{{ route('fte.detail', ['person' => $item->name]) }}"
                                                class="text-blue-700 underline hover:text-blue-900">
                                                {{ $item->name }}
                                            </a>
                                        @else
                                            -
                                        @endif
                                    </div>
                                    <div>กลุ่ม: <span class="text-slate-900">{{ $item->role_group ?? '-' }}</span></div>
                                    <div>ภาระงาน: <span class="text-slate-900">{{ $item->job_detail ?? '-' }}</span></div>
                                    <div class="text-xs text-slate-500">{{ $item->task_detail ?? '-' }}</div>
                                    <div class="flex flex-wrap gap-3 text-xs text-slate-600">
                                        <span>ช่วงเวลา: {{ $os }} - {{ $oe }}</span>
                                    </div>
                                    <div class="flex flex-wrap gap-3 text-xs text-slate-600">
                                        <span>ชั่วโมง: {{ number_format($item->hours_in_month, 2) }}</span>
                                        <span>วัน: {{ $item->days_in_month }}</span>
                                        <span>FTE/วัน: {{ number_format($item->fte_per_day, 10) }}</span>
                                        <span>FTE เดือนนี้: {{ number_format($item->fte_value, 7) }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </details>
            @empty
                <div class="bg-white border rounded-2xl shadow-sm p-6 text-center text-slate-500">ไม่มีข้อมูล</div>
            @endforelse
        </div>

        {{-- Desktop table --}}
        <div id="fte-months" class="hidden md:block space-y-4">
            @forelse($monthLoop as $monthKey => $rows)
                @php
                    $carbon = \Carbon\Carbon::createFromFormat('Y-m', $monthKey);
                    $monthName = $carbon->locale('th')->translatedFormat('F');
                    $year = $carbon->year + 543;
                    $sumFTE = $rows->sum('fte_value');
                    $sumHours = $rows->sum('hours_in_month');
                @endphp

                <details class="month-card bg-white border rounded-2xl shadow-sm" open data-month="{{ $monthKey }}"
                    data-year="{{ $carbon->year }}">
                    <summary class="cursor-pointer px-4 py-3 flex flex-col md:flex-row md:items-center gap-2">
                        <div class="text-sm font-semibold text-slate-800">
                            {{ $monthName }} {{ $year }}
                        </div>
                        <div class="text-xs text-slate-500">
                            {{ $rows->count() }} รายการ •
                            FTE {{ number_format($sumFTE, 7) }} •
                            ชั่วโมง {{ number_format($sumHours, 2) }}
                        </div>
                    </summary>

                    <div class="border-t">
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="bg-slate-50 border-b text-xs text-slate-600">
                                    <tr>
                                        <th class="p-2 text-left">บุคคล</th>
                                        <th class="p-2 text-left">กลุ่ม</th>
                                        <th class="p-2 text-left">ภาระงาน</th>
                                        <th class="p-2 text-left">รายละเอียดภารกิจ</th>
                                        <th class="p-2 text-center">ช่วงเวลา</th>
                                        <th class="p-2 text-center">ชั่วโมง</th>
                                        <th class="p-2 text-center">วัน</th>
                                        <th class="p-2 text-center">FTE/วัน</th>
                                        <th class="p-2 text-center">FTE เดือนนี้</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($rows as $item)
                                        @php
                                            $os = \Carbon\Carbon::parse($item->overlap_start)
                                                ->locale('th')
                                                ->translatedFormat('j F') . ' ' . (\Carbon\Carbon::parse($item->overlap_start)->year + 543);
                                            $oe = \Carbon\Carbon::parse($item->overlap_end)
                                                ->locale('th')
                                                ->translatedFormat('j F') . ' ' . (\Carbon\Carbon::parse($item->overlap_end)->year + 543);
                                        @endphp
                                        <tr class="border-b fte-row"
                                            data-person="{{ $item->name }}"
                                            data-role="{{ $item->role_group }}"
                                            data-job="{{ $item->job_detail }}"
                                            data-month="{{ $monthKey }}"
                                            data-year="{{ $carbon->year }}"
                                            data-fte="{{ $item->fte_value }}"
                                            data-hours="{{ $item->hours_in_month }}">
                                            <td class="p-2 text-slate-800 break-words">
                                                @if (!empty($item->name))
                                                    <a href="{{ route('fte.detail', ['person' => $item->name]) }}"
                                                        class="text-blue-700 underline hover:text-blue-900">
                                                        {{ $item->name }}
                                                    </a>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td class="p-2 break-words whitespace-normal">{{ $item->role_group ?? '-' }}</td>
                                            <td class="p-2 break-words whitespace-normal">{{ $item->job_detail ?? '-' }}</td>
                                            <td class="p-2 break-words whitespace-normal">{{ $item->task_detail ?? '-' }}</td>
                                            <td class="p-2 text-center">{{ $os }} - {{ $oe }}</td>
                                            <td class="p-2 text-center">{{ number_format($item->hours_in_month, 2) }}</td>
                                            <td class="p-2 text-center">{{ intval($item->days_in_month) }}</td>
                                            <td class="p-2 text-center">{{ number_format($item->fte_per_day, 10) }}</td>
                                            <td class="p-2 text-center font-semibold">
                                                {{ number_format($item->fte_value, 7) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </details>
            @empty
                <div class="text-gray-400 text-center py-8 bg-white border rounded-2xl">ไม่มีข้อมูล</div>
            @endforelse
            @if ($monthPaginator)
                <div class="mt-4">
                    {{ $monthPaginator->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            function stringToColor(str) {
                let hash = 0;
                for (let i = 0; i < str.length; i++) {
                    hash = str.charCodeAt(i) + ((hash << 5) - hash);
                }
                const r = (hash & 0xFF0000) >> 16;
                const g = (hash & 0x00FF00) >> 8;
                const b = hash & 0x0000FF;
                return `rgba(${Math.abs(r)}, ${Math.abs(g)}, ${Math.abs(b)}, 0.8)`;
            }

            const labelsRaw = @json($chartLabels);
            const labels = labelsRaw.map(monthKey => {
                const parts = String(monthKey || '').split('-');
                if (parts.length !== 2) return monthKey;
                const year = Number(parts[0]);
                const month = parts[1];
                if (!Number.isFinite(year)) return monthKey;
                return `${month}/${year + 543}`;
            });

            const personNames = [];
            @foreach ($monthlyFTE as $month => $items)
                @foreach ($items as $item)
                    personNames.push("{{ $item->name }}");
                @endforeach
            @endforeach
            const uniquePersons = [...new Set(personNames)];

            const datasets = uniquePersons.map(personName => ({
                label: personName,
                backgroundColor: stringToColor(personName),
                data: labelsRaw.map(monthKey => {
                    let total = 0;
                    @foreach ($monthlyFTE as $m => $items)
                        @foreach ($items as $item)
                            if (monthKey === "{{ $m }}" && personName === "{{ $item->name }}") {
                                total += {{ $item->fte_value }};
                            }
                        @endforeach
                    @endforeach
                    return total;
                }),
                stack: 'stack'
            }));

            new Chart(document.getElementById('fteStackedChart'), {
                type: 'bar',
                data: {
                    labels,
                    datasets
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            stacked: true
                        },
                        y: {
                            stacked: true,
                            beginAtZero: true,
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });

            new Chart(document.getElementById('fteYearChart'), {
                type: 'bar',
                data: {
                    labels: @json($yearChartLabels),
                    datasets: [{
                        label: 'FTE รวม',
                        data: @json($yearChartData),
                        backgroundColor: '#0f766e'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            const isDesktop = window.matchMedia('(min-width: 768px)').matches;
            const searchEl = document.getElementById(isDesktop ? 'fteSearch' : 'fteSearchMobile') || document.getElementById('fteSearch') || document.getElementById('fteSearchMobile');
            const personEl = document.getElementById(isDesktop ? 'ftePersonFilter' : 'ftePersonFilterMobile') || document.getElementById('ftePersonFilter') || document.getElementById('ftePersonFilterMobile');
            const roleEl = document.getElementById(isDesktop ? 'fteRoleFilter' : 'fteRoleFilterMobile') || document.getElementById('fteRoleFilter') || document.getElementById('fteRoleFilterMobile');
            const yearEl = document.getElementById(isDesktop ? 'fteYearFilter' : 'fteYearFilterMobile') || document.getElementById('fteYearFilter') || document.getElementById('fteYearFilterMobile');
            const monthEl = document.getElementById(isDesktop ? 'fteMonthFilter' : 'fteMonthFilterMobile') || document.getElementById('fteMonthFilter') || document.getElementById('fteMonthFilterMobile');
            const sortEl = document.getElementById(isDesktop ? 'fteSort' : 'fteSortMobile') || document.getElementById('fteSort') || document.getElementById('fteSortMobile');
            const resetBtn = document.getElementById(isDesktop ? 'fteResetFilters' : 'fteResetFiltersMobile') || document.getElementById('fteResetFilters') || document.getElementById('fteResetFiltersMobile');

            const normalize = (v) => String(v || '').toLowerCase().trim();

            function sortRows(rows, mode) {
                const list = Array.from(rows);
                const by = {
                    fte_desc: (a, b) => Number(b.dataset.fte) - Number(a.dataset.fte),
                    fte_asc: (a, b) => Number(a.dataset.fte) - Number(b.dataset.fte),
                    hours_desc: (a, b) => Number(b.dataset.hours) - Number(a.dataset.hours),
                    name_asc: (a, b) => normalize(a.dataset.person).localeCompare(normalize(b.dataset.person))
                };
                list.sort(by[mode] || by.fte_desc);
                return list;
            }

            function applyFilters() {
                const search = normalize(searchEl?.value);
                const person = normalize(personEl?.value);
                const role = normalize(roleEl?.value);
                const year = normalize(yearEl?.value);
                const month = normalize(monthEl?.value);
                const sortMode = sortEl?.value || 'fte_desc';

                document.querySelectorAll('.month-card').forEach(card => {
                    const rows = Array.from(card.querySelectorAll('.fte-row'));
                    let visible = 0;

                    rows.forEach(row => {
                        const rowText = normalize([
                            row.dataset.person,
                            row.dataset.role,
                            row.dataset.job
                        ].join(' '));
                        const matchSearch = !search || rowText.includes(search);
                        const matchPerson = !person || normalize(row.dataset.person) === person;
                        const matchRole = !role || normalize(row.dataset.role) === role;
                        const matchYear = !year || normalize(row.dataset.year) === year;
                        const matchMonth = !month || normalize(row.dataset.month) === month;

                        const show = matchSearch && matchPerson && matchRole && matchYear && matchMonth;
                        row.classList.toggle('hidden', !show);
                        if (show) visible++;
                    });

                    const tbody = card.querySelector('tbody');
                    const visibleRows = rows.filter(r => !r.classList.contains('hidden'));
                    sortRows(visibleRows, sortMode).forEach(row => tbody.appendChild(row));

                    card.classList.toggle('hidden', visible === 0);
                });

                document.querySelectorAll('.month-card-mobile').forEach(card => {
                    const rows = Array.from(card.querySelectorAll('.fte-row-card'));
                    let visible = 0;

                    rows.forEach(row => {
                        const rowText = normalize([
                            row.dataset.person,
                            row.dataset.role,
                            row.dataset.job
                        ].join(' '));
                        const matchSearch = !search || rowText.includes(search);
                        const matchPerson = !person || normalize(row.dataset.person) === person;
                        const matchRole = !role || normalize(row.dataset.role) === role;
                        const matchYear = !year || normalize(row.dataset.year) === year;
                        const matchMonth = !month || normalize(row.dataset.month) === month;

                        const show = matchSearch && matchPerson && matchRole && matchYear && matchMonth;
                        row.classList.toggle('hidden', !show);
                        if (show) visible++;
                    });

                    const wrap = card.querySelector('.fte-card-list');
                    const visibleRows = rows.filter(r => !r.classList.contains('hidden'));
                    sortRows(visibleRows, sortMode).forEach(row => wrap.appendChild(row));

                    card.classList.toggle('hidden', visible === 0);
                });
            }

            [searchEl, personEl, roleEl, yearEl, monthEl, sortEl].forEach(el => {
                el?.addEventListener('input', applyFilters);
                el?.addEventListener('change', applyFilters);
            });

            document.getElementById('fteSearch')?.addEventListener('input', applyFilters);
            document.getElementById('fteSearchMobile')?.addEventListener('input', applyFilters);
            document.getElementById('ftePersonFilter')?.addEventListener('change', applyFilters);
            document.getElementById('ftePersonFilterMobile')?.addEventListener('change', applyFilters);
            document.getElementById('fteRoleFilter')?.addEventListener('change', applyFilters);
            document.getElementById('fteRoleFilterMobile')?.addEventListener('change', applyFilters);
            document.getElementById('fteYearFilter')?.addEventListener('change', applyFilters);
            document.getElementById('fteYearFilterMobile')?.addEventListener('change', applyFilters);
            document.getElementById('fteMonthFilter')?.addEventListener('change', applyFilters);
            document.getElementById('fteMonthFilterMobile')?.addEventListener('change', applyFilters);
            document.getElementById('fteSort')?.addEventListener('change', applyFilters);
            document.getElementById('fteSortMobile')?.addEventListener('change', applyFilters);

            const resetHandler = (mode) => {
                const s = document.getElementById(mode === 'desktop' ? 'fteSearch' : 'fteSearchMobile');
                const p = document.getElementById(mode === 'desktop' ? 'ftePersonFilter' : 'ftePersonFilterMobile');
                const r = document.getElementById(mode === 'desktop' ? 'fteRoleFilter' : 'fteRoleFilterMobile');
                const y = document.getElementById(mode === 'desktop' ? 'fteYearFilter' : 'fteYearFilterMobile');
                const m = document.getElementById(mode === 'desktop' ? 'fteMonthFilter' : 'fteMonthFilterMobile');
                const sort = document.getElementById(mode === 'desktop' ? 'fteSort' : 'fteSortMobile');
                if (s) s.value = '';
                if (p) p.value = '';
                if (r) r.value = '';
                if (y) y.value = '';
                if (m) m.value = '';
                if (sort) sort.value = 'fte_desc';
                applyFilters();
            };

            document.getElementById('fteResetFilters')?.addEventListener('click', () => resetHandler('desktop'));
            document.getElementById('fteResetFiltersMobile')?.addEventListener('click', () => resetHandler('mobile'));

            applyFilters();
        });
    </script>
@endsection
