@extends('layouts.app')

@section('content')
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
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('fte.index') }}"
                        class="inline-flex items-center gap-2 px-3 py-2 text-sm border rounded-lg hover:bg-slate-50">
                        ย้อนกลับ
                    </a>
                    <a href="{{ route('fte.pdf', $person) }}" target="_blank" rel="noopener"
                        class="inline-flex items-center gap-2 px-3 py-2 text-sm bg-emerald-600 text-white rounded-lg hover:bg-emerald-700">
                        ดาวน์โหลด PDF
                    </a>
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
                <div class="text-2xl font-semibold text-slate-900">{{ number_format($totalFte, 4) }}</div>
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

        <div class="bg-white rounded-2xl shadow-sm border p-4">
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
            @forelse($monthlyFTE as $monthKey => $rows)
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
                            FTE {{ number_format($sumFTE, 4) }} •
                            ชั่วโมง {{ number_format($sumHours, 2) }}
                        </div>
                    </summary>

                    <div class="border-t">
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="bg-slate-50 border-b text-xs text-slate-600">
                                    <tr>
                                        <th class="p-2 text-left">โครงการ</th>
                                        <th class="p-2 text-left">กลุ่ม</th>
                                        <th class="p-2 text-left">ภาระงาน</th>
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
                                        @endphp
                                        <tr class="border-b fte-row"
                                            data-project="{{ $projectName }}"
                                            data-role="{{ $roleGroup }}"
                                            data-job="{{ $jobDetail }}"
                                            data-month="{{ $monthKey }}"
                                            data-year="{{ $carbon->year }}"
                                            data-fte="{{ $item->fte_value }}"
                                            data-hours="{{ $item->hours_in_month }}">
                                            <td class="p-2 text-blue-700 hover:underline break-words max-w-[420px]">
                                                @if ($item->project?->id)
                                                    <a href="{{ route('projects.show', $item->project->id) }}" target="_blank" rel="noopener">
                                                        {{ $projectName }}
                                                    </a>
                                                @else
                                                    {{ $projectName }}
                                                @endif
                                            </td>
                                            <td class="p-2 break-words whitespace-normal">
                                                {{ $roleGroup }}
                                            </td>
                                            <td class="p-2 break-words whitespace-normal">
                                                <div>{{ $jobDetail }}</div>
                                                <div class="text-xs text-slate-500 mt-1">{{ $taskDetail }}</div>
                                            </td>
                                            <td class="p-2 text-center">
                                                {{ $os }} {{ $osYear }} - {{ $oe }} {{ $oeYear }}
                                            </td>
                                            <td class="p-2 text-center">
                                                {{ number_format($item->hours_in_month, 2) }}
                                            </td>
                                            <td class="p-2 text-center">
                                                {{ intval($item->days_in_month) }}
                                            </td>
                                            <td class="p-2 text-center">
                                                {{ number_format($item->fte_per_day, 10) }}
                                            </td>
                                            <td class="p-2 text-center font-semibold">
                                                {{ number_format($item->fte_value, 6) }}
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
                    projectNames.push("{{ $item->project->project_name_th }}");
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
                            if (monthKey === "{{ $m }}" && projectName ===
                                "{{ $item->project->project_name_th }}") {
                                total = {{ $item->fte_value }};
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

            const searchEl = document.getElementById('fteSearch');
            const projectEl = document.getElementById('fteProjectFilter');
            const roleEl = document.getElementById('fteRoleFilter');
            const yearEl = document.getElementById('fteYearFilter');
            const monthEl = document.getElementById('fteMonthFilter');
            const sortEl = document.getElementById('fteSort');
            const resetBtn = document.getElementById('fteResetFilters');

            const normalize = (v) => String(v || '').toLowerCase().trim();

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
