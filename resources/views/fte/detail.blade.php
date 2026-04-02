@extends('layouts.app')

@section('content')
    <script>
        window.disableRowCollapse = true;
        document.addEventListener('DOMContentLoaded', () => {
            document.body.classList.add('no-row-collapse-summary');
            document.body.classList.add('no-row-collapse-toggle');
        });
    </script>
    <style>
        @media (max-width: 768px) {
            .fte-table thead {
                display: none;
            }

            .fte-table,
            .fte-table tbody,
            .fte-table tr,
            .fte-table td {
                display: block;
                width: 100%;
            }

            .fte-table tr {
                border: 1px solid #e5e7eb;
                border-radius: 12px;
                padding: 10px 12px;
                margin-bottom: 10px;
                background: #ffffff;
            }

            .fte-table td {
                display: flex;
                justify-content: space-between;
                gap: 12px;
                padding: 6px 0;
                text-align: left;
            }

            .fte-table td::before {
                content: attr(data-label);
                font-size: 11px;
                text-transform: uppercase;
                color: #94a3b8;
            }
        }
    </style>
    @php
        $allRows = $monthlyFTE->flatten(1);
        $projectCount = $allRows->pluck('project.id')->filter()->unique()->count();
        $monthCount = $monthlyFTE->count();
        $totalFte = $allRows->sum('fte_value');
        $totalHours = $allRows->sum('hours_in_month');
        $projectOptions = $allRows->pluck('project.project_name_th')->filter()->unique()->sort()->values();
        $roleOptions = $allRows->pluck('role_group')->filter()->unique()->sort()->values();
        $yearOptions = $monthlyFTE->keys()->map(fn($m) => substr($m, 0, 4))->unique()->sort()->values();
        $monthOptions = $monthlyFTE->keys()->values();
    @endphp

    <div class="max-w-7xl mx-auto p-4 space-y-6">
        <div class="bg-white border shadow-sm rounded-2xl p-5">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="flex items-center gap-4">
                    @if ($profile && $profile->profile_photo_path)
                        <img src="{{ asset('storage/' . $profile->profile_photo_path) }}?v={{ time() }}"
                            class="w-40 h-40 rounded-2xl border shadow object-cover">
                    @else
                        <img src="https://ui-avatars.com/api/?name={{ urlencode($person) }}&background=random&size=128"
                            class="w-40 h-40 rounded-2xl border shadow object-cover">
                    @endif
                    <div>
                        <h1 class="text-2xl font-semibold text-slate-900">{{ $person }}</h1>
                        <div class="text-xs text-slate-500">
                            @if (!empty($profile?->department))
                                • {{ $profile->department }}
                            @endif
                        </div>
                        <div class="mt-2 inline-flex items-center rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">
                            ช่วงข้อมูล: {{ $pdfFilter['label'] ?? 'ทั้งหมด' }}
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('fte.index') }}"
                        class="inline-flex items-center gap-2 px-3 py-2 text-sm border rounded-lg hover:bg-slate-50">
                        ย้อนกลับ
                    </a>
                    <button type="button" id="openPdfFilterModal"
                        class="inline-flex items-center gap-2 px-3 py-2 text-sm bg-emerald-600 text-white rounded-lg hover:bg-emerald-700">
                        ดาวน์โหลดภาระงาน
                    </button>
                </div>
            </div>
        </div>

        <div id="pdfFilterModal" class="hidden fixed inset-0 z-[1000] bg-slate-900/50 px-4">
            <div class="h-full w-full flex items-center justify-center">
                <div class="w-full max-w-xl rounded-2xl bg-white shadow-2xl border border-slate-200">
                    <div class="flex items-center justify-between px-5 py-4 border-b border-slate-200">
                        <h2 class="text-lg font-semibold text-slate-900">เลือกช่วงข้อมูลสำหรับดาวน์โหลดภาระงาน</h2>
                        <button type="button" id="closePdfFilterModal"
                            class="inline-flex items-center justify-center h-8 w-8 rounded-lg border border-slate-300 text-slate-600 hover:bg-slate-50">✕</button>
                    </div>

                    <form id="pdfFilterForm" action="{{ route('fte.pdf', $person) }}" method="GET" target="_blank" class="px-5 py-4 space-y-4">
                        <label class="block">
                            <span class="text-xs font-semibold text-slate-600">รูปแบบช่วงข้อมูล</span>
                            <select id="pdfMode" name="mode" class="mt-1 w-full border border-slate-200 rounded-lg p-2 text-sm">
                                <option value="all">ทั้งหมด</option>
                                <option value="year">เฉพาะปี</option>
                                <option value="custom">กำหนดช่วงวัน</option>
                            </select>
                        </label>

                        <label id="pdfYearWrap" class="hidden block">
                            <span class="text-xs font-semibold text-slate-600">ปี (พ.ศ.)</span>
                            <select id="pdfYear" name="year" class="mt-1 w-full border border-slate-200 rounded-lg p-2 text-sm">
                                <option value="">เลือกปี</option>
                                @foreach ($yearOptions as $year)
                                    <option value="{{ $year }}">{{ $year + 543 }}</option>
                                @endforeach
                            </select>
                        </label>

                        <div id="pdfDateWrap" class="hidden grid grid-cols-1 md:grid-cols-2 gap-3">
                            <label class="block">
                                <span class="text-xs font-semibold text-slate-600">วันที่เริ่มต้น</span>
                                <div class="relative">
                                    <input id="pdfStartDate" type="text" name="start"
                                        class="thai-datepicker mt-1 w-full border border-slate-200 rounded-lg p-2 pl-10 text-sm"
                                        placeholder="กรุณาเลือกวันที่เริ่มต้น">
                                    <button type="button"
                                        class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-blue-600"
                                        data-open-picker data-target="pdfStartDate" aria-label="เปิดปฏิทินเริ่มต้น">
                                        📅
                                    </button>
                                </div>
                            </label>
                            <label class="block">
                                <span class="text-xs font-semibold text-slate-600">วันที่สิ้นสุด</span>
                                <div class="relative">
                                    <input id="pdfEndDate" type="text" name="end"
                                        class="thai-datepicker mt-1 w-full border border-slate-200 rounded-lg p-2 pl-10 text-sm"
                                        placeholder="กรุณาเลือกวันที่สิ้นสุด">
                                    <button type="button"
                                        class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-blue-600"
                                        data-open-picker data-target="pdfEndDate" aria-label="เปิดปฏิทินสิ้นสุด">
                                        📅
                                    </button>
                                </div>
                            </label>
                        </div>

                        <p class="text-xs text-slate-500">หมายเหตุ: ถ้าไม่เลือก filter ระบบจะสร้างรายงานจากข้อมูลทั้งหมด</p>

                        <div class="pt-2 flex items-center justify-end gap-2">
                            <button type="button" id="cancelPdfFilterModal"
                                class="px-4 py-2 text-sm border border-slate-300 rounded-lg hover:bg-slate-50">ยกเลิก</button>
                            <button type="submit" formaction="{{ route('fte.word', $person) }}"
                                class="px-4 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700">ดาวน์โหลด Word</button>
                            <button type="submit"
                                class="px-4 py-2 text-sm bg-emerald-600 text-white rounded-lg hover:bg-emerald-700">เปิด PDF</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white border rounded-xl p-4">
                <div class="text-xs text-slate-500">จำนวนเดือนที่มีงาน</div>
                <div class="text-2xl font-semibold text-slate-900">{{ number_format($monthCount) }}</div>
            </div>
            <div class="bg-white border rounded-xl p-4">
                <div class="text-xs text-slate-500">จำนวนโครงการ</div>
                <div class="text-2xl font-semibold text-slate-900">{{ number_format($projectCount) }}</div>
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

        <div class="bg-white border rounded-2xl p-4 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-lg font-semibold text-slate-800">📊 สรุป FTE รายเดือน (Stacked)</h3>
            </div>
            <div id="chart-container" class="bg-slate-50 rounded-xl border p-3" style="height:300px;">
                <canvas id="fteStackedChart"></canvas>
            </div>
        </div>

        <div id="fte-filters" class="bg-white rounded-2xl shadow-sm border p-4">
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
                    <input id="fteSearch" type="text" placeholder="ชื่อโครงการ / กลุ่ม / ภาระงาน"
                        class="w-full border border-slate-200 rounded-lg p-2 text-sm">
                </label>
                <label class="block">
                    <span class="text-xs font-semibold text-slate-600">โครงการ</span>
                    <select id="fteProjectFilter" class="w-full border border-slate-200 rounded-lg p-2 text-sm">
                        <option value="">ทั้งหมด</option>
                        @foreach ($projectOptions as $project)
                            <option value="{{ $project }}">{{ $project }}</option>
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
                                $mLabel = $mCarbon->locale('th')->translatedFormat('F Y');
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
                        <option value="project_asc">ชื่อโครงการ A → Z</option>
                    </select>
                </label>
            </div>
        </div>

        <div id="fte-months" class="space-y-4">
            @php
                $monthPaginator = $monthlyFTEPage ?? null;
                $monthLoop = $monthPaginator ? $monthPaginator->items() : $monthlyFTE;
            @endphp
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
                            <table class="fte-table w-full text-sm">
                                <thead class="bg-slate-50 border-b text-xs text-slate-600">
                                    <tr>
                                        <th class="p-2 text-left">ชื่อโปรเจค / งานนอกโครงการ</th>
                                        <th class="p-2 text-left">กลุ่ม</th>
                                        <th class="p-2 text-left">รายละเอียดงาน</th>
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
                                            $osCarbon = \Carbon\Carbon::parse($item->overlap_start);
                                            $oeCarbon = \Carbon\Carbon::parse($item->overlap_end);
                                            $os = $osCarbon->locale('th')->translatedFormat('j F');
                                            $oe = $oeCarbon->locale('th')->translatedFormat('j F');
                                            $osYear = $osCarbon->year + 543;
                                            $oeYear = $oeCarbon->year + 543;
                                            $projectName = $item->project->project_name_th ?? '-';
                                            $roleGroup = $item->role_group ?? '-';
                                            $jobDetailRaw = trim((string) ($item->job_detail ?? ''));
                                            $taskDetailRaw = trim((string) ($item->task_detail ?? ''));
                                            $jobDetail = $jobDetailRaw !== '' ? $jobDetailRaw : '-';
                                            $taskDetail = $taskDetailRaw !== '' ? $taskDetailRaw : '-';
                                            $combinedJob = trim($jobDetailRaw . ' ' . $taskDetailRaw);
                                        @endphp
                                        <tr class="border-b fte-row"
                                            data-project="{{ $projectName }}"
                                            data-role="{{ $roleGroup }}"
                                            data-job="{{ $combinedJob }}"
                                            data-month="{{ $monthKey }}"
                                            data-year="{{ $carbon->year }}"
                                            data-fte="{{ $item->fte_value }}"
                                            data-hours="{{ $item->hours_in_month }}">
                                            <td class="p-2 break-words max-w-[420px]" data-label="โครงการ">
                                                @if ($item->project?->id)
                                                    <a href="{{ route('projects.show', $item->project) }}" target="_blank" rel="noopener"
                                                        class="text-blue-700 hover:underline">
                                                        {{ $projectName }}
                                                    </a>
                                                @else
                                                    @php
                                                        $backOfficeTitle = $item->task_detail ?? '-';
                                                    @endphp
                                                    @if (!empty($item->task_id))
                                                        <a href="{{ route('back_office.show', $item->task_id) }}" target="_blank" rel="noopener"
                                                            class="text-blue-700 hover:underline">
                                                            {{ $backOfficeTitle }}
                                                        </a>
                                                    @else
                                                        <span class="text-slate-800">{{ $backOfficeTitle }}</span>
                                                    @endif
                                                    <div class="text-xs text-slate-500 mt-1">งานนอกโครงการ</div>
                                                @endif
                                            </td>
                                            <td class="p-2 break-words whitespace-normal" data-label="กลุ่ม">
                                                {{ $roleGroup }}
                                            </td>
                                            <td class="p-2 break-words whitespace-normal" data-label="รายละเอียดงาน">
                                                <div class="text-slate-700">{{ $jobDetail }}</div>
                                                @if ($taskDetail !== '-')
                                                    <div class="text-slate-500 text-sm mt-0.5">{{ $taskDetail }}</div>
                                                @endif
                                            </td>
                                            <td class="p-2 text-center" data-label="ช่วงเวลา">
                                                {{ $os }} {{ $osYear }} - {{ $oe }} {{ $oeYear }}
                                            </td>
                                            <td class="p-2 text-center" data-label="ชั่วโมง">
                                                {{ number_format($item->hours_in_month, 2) }}
                                            </td>
                                            <td class="p-2 text-center" data-label="วัน">
                                                {{ intval($item->days_in_month) }}
                                            </td>
                                            <td class="p-2 text-center" data-label="FTE/วัน">
                                                {{ number_format($item->fte_per_day, 10) }}
                                            </td>
                                            <td class="p-2 text-center font-semibold" data-label="FTE เดือนนี้">
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

            const projectNames = [];
            @foreach ($monthlyFTE as $month => $items)
                @foreach ($items as $item)
                    @php
                        $chartTitle = $item->project?->id
                            ? ($item->project->project_name_th ?? '-')
                            : ($item->task_detail ?? '-');
                    @endphp
                    projectNames.push(@json($chartTitle));
                @endforeach
            @endforeach

            const uniqueProjects = [...new Set(projectNames)];

            const datasets = uniqueProjects.map(projectName => ({
                label: projectName,
                backgroundColor: stringToColor(projectName),
                data: labelsRaw.map(monthKey => {
                    let total = 0;

                    @foreach ($monthlyFTE as $m => $items)
                        @foreach ($items as $item)
                            @php
                                $chartTitle = $item->project?->id
                                    ? ($item->project->project_name_th ?? '-')
                                    : ($item->task_detail ?? '-');
                            @endphp
                            if (monthKey === "{{ $m }}" && projectName === @json($chartTitle)) {
                                total = {{ $item->fte_value }};
                            }
                        @endforeach
                    @endforeach

                    return total;
                }),
                stack: 'stack'
            }));

            const truncateLegendLabel = (text, max = 18) => {
                const value = String(text ?? '');
                if (value.length <= max) return value;
                return value.slice(0, max - 1) + '…';
            };

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
                            position: 'bottom',
                            labels: {
                                generateLabels: (chart) => {
                                    const base = Chart.defaults.plugins.legend.labels.generateLabels;
                                    return base(chart).map(item => ({
                                        ...item,
                                        text: truncateLegendLabel(item.text),
                                    }));
                                }
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: (ctx) => {
                                    const name = ctx.dataset?.label || '';
                                    const value = ctx.parsed?.y ?? ctx.raw;
                                    return `${name}: ${value}`;
                                }
                            }
                        }
                    }
                }
            });

            const searchEl = document.getElementById('fteSearch');
            const projectEl = document.getElementById('fteProjectFilter');
            const roleEl = document.getElementById('fteRoleFilter');
            const yearEl = document.getElementById('fteYearFilter');
            const monthEl = document.getElementById('fteMonthFilter');
            const sortEl = document.getElementById('fteSort');
            const resetBtn = document.getElementById('fteResetFilters');
            const openPdfModalBtn = document.getElementById('openPdfFilterModal');
            const pdfModal = document.getElementById('pdfFilterModal');
            const closePdfModalBtn = document.getElementById('closePdfFilterModal');
            const cancelPdfModalBtn = document.getElementById('cancelPdfFilterModal');
            const pdfModeEl = document.getElementById('pdfMode');
            const pdfYearWrap = document.getElementById('pdfYearWrap');
            const pdfYearEl = document.getElementById('pdfYear');
            const pdfDateWrap = document.getElementById('pdfDateWrap');
            const pdfStartDateEl = document.getElementById('pdfStartDate');
            const pdfEndDateEl = document.getElementById('pdfEndDate');
            const pdfFilterForm = document.getElementById('pdfFilterForm');

            const normalize = (v) => String(v || '').toLowerCase().trim();

            const closePdfModal = () => {
                if (!pdfModal) return;
                pdfModal.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            };

            const openPdfModal = () => {
                if (!pdfModal) return;
                pdfModal.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
            };

            const syncPdfMode = () => {
                const mode = pdfModeEl?.value || 'all';
                const showYear = mode === 'year';
                const showDate = mode === 'custom';

                if (pdfYearWrap) pdfYearWrap.classList.toggle('hidden', !showYear);
                if (pdfDateWrap) pdfDateWrap.classList.toggle('hidden', !showDate);

                if (pdfYearEl) {
                    pdfYearEl.disabled = !showYear;
                    pdfYearEl.required = showYear;
                    if (!showYear) pdfYearEl.value = '';
                }

                if (pdfStartDateEl) {
                    pdfStartDateEl.disabled = !showDate;
                    pdfStartDateEl.required = showDate;
                    if (!showDate) pdfStartDateEl.value = '';
                }

                if (pdfEndDateEl) {
                    pdfEndDateEl.disabled = !showDate;
                    pdfEndDateEl.required = showDate;
                    if (!showDate) pdfEndDateEl.value = '';
                }
            };

            const initPdfThaiDatepickers = () => {
                const toBuddhist = (selectedDates, dateStr, instance) => {
                    const altInput = instance?.altInput;
                    if (altInput) {
                        altInput.readOnly = true;
                    }

                    if (selectedDates && selectedDates.length > 0 && altInput) {
                        const date = selectedDates[0];
                        const day = date.getDate();
                        const month = date.toLocaleString('th-TH', {
                            month: 'long'
                        });
                        const year = date.getFullYear() + 543;
                        altInput.value = `${day} ${month} ${year}`;
                    }

                    const years = instance?.calendarContainer?.querySelectorAll('.flatpickr-current-month input.cur-year') || [];
                    years.forEach((el) => {
                        const year = parseInt(el.value, 10);
                        if (!Number.isNaN(year)) {
                            el.value = year + 543;
                        }

                        if (el.dataset.beBound === '1') return;
                        el.dataset.beBound = '1';
                        el.addEventListener('blur', () => {
                            const inputYear = parseInt(el.value, 10);
                            if (inputYear > 2400) {
                                el.value = inputYear - 543;
                                if (instance.selectedDates[0]) {
                                    instance.setDate(new Date(instance.selectedDates[0]));
                                }
                            }
                        });
                    });
                };

                document.querySelectorAll('#pdfFilterForm .thai-datepicker').forEach((input) => {
                    if (input._flatpickr) return;
                    flatpickr(input, {
                        locale: 'th',
                        dateFormat: 'd-m-Y',
                        altInput: true,
                        altFormat: 'j F Y',
                        prevArrow: '',
                        nextArrow: '',
                        disableMobile: true,
                        onReady: toBuddhist,
                        onChange: toBuddhist,
                        onMonthChange: toBuddhist,
                        onYearChange: toBuddhist,
                    });

                    const pickerBtn = document.querySelector(
                        `[data-open-picker][data-target="${input.id}"]`
                    );
                    if (pickerBtn && pickerBtn.dataset.bound !== '1') {
                        pickerBtn.dataset.bound = '1';
                        pickerBtn.addEventListener('click', () => {
                            input._flatpickr?.open();
                        });
                    }
                });
            };

            const parsePickerDate = (value) => {
                const raw = String(value || '').trim();
                if (!raw) return null;

                let match = raw.match(/^(\d{1,2})[-/](\d{1,2})[-/](\d{4})$/);
                if (match) {
                    let year = Number(match[3]);
                    if (year > 2400) year -= 543;
                    const parsed = new Date(year, Number(match[2]) - 1, Number(match[1]));
                    return Number.isNaN(parsed.getTime()) ? null : parsed;
                }

                match = raw.match(/^(\d{4})[-/](\d{1,2})[-/](\d{1,2})$/);
                if (match) {
                    let year = Number(match[1]);
                    if (year > 2400) year -= 543;
                    const parsed = new Date(year, Number(match[2]) - 1, Number(match[3]));
                    return Number.isNaN(parsed.getTime()) ? null : parsed;
                }

                return null;
            };

            function sortRows(rows, mode) {
                const list = Array.from(rows);
                const by = {
                    fte_desc: (a, b) => Number(b.dataset.fte) - Number(a.dataset.fte),
                    fte_asc: (a, b) => Number(a.dataset.fte) - Number(b.dataset.fte),
                    hours_desc: (a, b) => Number(b.dataset.hours) - Number(a.dataset.hours),
                    project_asc: (a, b) => normalize(a.dataset.project).localeCompare(normalize(b.dataset.project))
                };
                list.sort(by[mode] || by.fte_desc);
                return list;
            }

            function applyFilters() {
                const search = normalize(searchEl?.value);
                const project = normalize(projectEl?.value);
                const role = normalize(roleEl?.value);
                const year = normalize(yearEl?.value);
                const month = normalize(monthEl?.value);
                const sortMode = sortEl?.value || 'fte_desc';

                document.querySelectorAll('.month-card').forEach(card => {
                    const rows = Array.from(card.querySelectorAll('.fte-row'));
                    let visible = 0;

                    rows.forEach(row => {
                        const rowText = normalize([
                            row.dataset.project,
                            row.dataset.role,
                            row.dataset.job
                        ].join(' '));
                        const matchSearch = !search || rowText.includes(search);
                        const matchProject = !project || normalize(row.dataset.project) === project;
                        const matchRole = !role || normalize(row.dataset.role) === role;
                        const matchYear = !year || normalize(row.dataset.year) === year;
                        const matchMonth = !month || normalize(row.dataset.month) === month;

                        const show = matchSearch && matchProject && matchRole && matchYear && matchMonth;
                        row.classList.toggle('hidden', !show);
                        if (show) visible++;
                    });

                    const tbody = card.querySelector('tbody');
                    const visibleRows = rows.filter(r => !r.classList.contains('hidden'));
                    sortRows(visibleRows, sortMode).forEach(row => tbody.appendChild(row));

                    card.classList.toggle('hidden', visible === 0);
                });
            }

            [searchEl, projectEl, roleEl, yearEl, monthEl, sortEl].forEach(el => {
                el?.addEventListener('input', applyFilters);
                el?.addEventListener('change', applyFilters);
            });

            openPdfModalBtn?.addEventListener('click', openPdfModal);
            closePdfModalBtn?.addEventListener('click', closePdfModal);
            cancelPdfModalBtn?.addEventListener('click', closePdfModal);
            pdfModeEl?.addEventListener('change', syncPdfMode);
            initPdfThaiDatepickers();
            syncPdfMode();

            pdfModal?.addEventListener('click', (event) => {
                if (event.target === pdfModal) {
                    closePdfModal();
                }
            });

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && pdfModal && !pdfModal.classList.contains('hidden')) {
                    closePdfModal();
                }
            });

            pdfFilterForm?.addEventListener('submit', (event) => {
                const mode = pdfModeEl?.value || 'all';
                if (mode === 'custom') {
                    const startValue = pdfStartDateEl?.value || '';
                    const endValue = pdfEndDateEl?.value || '';
                    const startDate = parsePickerDate(startValue);
                    const endDate = parsePickerDate(endValue);
                    if (startDate && endDate && startDate > endDate) {
                        event.preventDefault();
                        alert('วันที่เริ่มต้นต้องไม่มากกว่าวันที่สิ้นสุด');
                        return;
                    }
                }
                closePdfModal();
            });

            resetBtn?.addEventListener('click', () => {
                if (searchEl) searchEl.value = '';
                if (projectEl) projectEl.value = '';
                if (roleEl) roleEl.value = '';
                if (yearEl) yearEl.value = '';
                if (monthEl) monthEl.value = '';
                if (sortEl) sortEl.value = 'fte_desc';
                applyFilters();
            });

            applyFilters();
        });
    </script>
@endsection
