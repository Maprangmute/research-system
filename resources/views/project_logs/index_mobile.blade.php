@extends('layouts.app')
@section('content')
<script>
    window.PROJECT_MOBILE_WHEEL_DATE = true;
</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
    .log-mobile-filter {
        border-radius: 20px;
        border: 1px solid #bdeaf5;
        background: linear-gradient(135deg, #f3fdff 0%, #ffffff 50%, #f8fcff 100%);
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.07);
    }
    .log-mobile-filter input,
    .log-mobile-filter select {
        border-color: #bfdbfe;
        border-radius: 12px;
        min-height: 42px;
    }
    .log-mobile-filter input:focus,
    .log-mobile-filter select:focus {
        outline: none;
        border-color: #38bdf8;
        box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.2);
    }
    .log-mobile-head {
        border-radius: 14px;
        border: 1px solid #bae6fd;
        background: linear-gradient(90deg, rgba(14, 165, 233, 0.08) 0%, rgba(56, 189, 248, 0.04) 100%);
        padding: 0.6rem 0.7rem;
    }
    .log-mobile-summary {
        border-radius: 18px;
        border: 1px solid #dbe3ee;
        box-shadow: 0 6px 22px rgba(15, 23, 42, 0.07);
    }
    .log-mobile-pill {
        border: 1px solid #7dd3fc;
        background: rgba(255, 255, 255, 0.88);
        color: #075985;
        border-radius: 9999px;
        padding: 0.34rem 0.7rem;
        font-size: 0.72rem;
        font-weight: 700;
        transition: all 0.2s ease;
    }
    .log-mobile-pill.is-active {
        border-color: #0284c7;
        background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
        color: #ffffff;
        box-shadow: 0 6px 14px rgba(2, 132, 199, 0.22);
    }
</style>

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

    <div id="project-log-ajax-root-mobile"
        data-chart-labels='@json($topUpdaterLabels ?? [])'
        data-chart-counts='@json($topUpdaterCounts ?? [])'
        data-suggest-url="{{ route('project_logs.project_suggestions') }}"
        data-export-url="{{ route('project_logs.export') }}">
    <form id="project-log-filter-form-mobile" action="{{ route('project_logs.index') }}" method="GET" class="log-mobile-filter p-3 space-y-3">
        <div class="log-mobile-head flex flex-wrap items-center gap-2">
            <span class="text-[11px] font-semibold text-sky-700">ดูข้อมูลแบบเร็ว:</span>
            <button type="button" class="log-mobile-pill js-quick-period-mobile {{ ($timeMode ?? 'month') === 'month' ? 'is-active' : '' }}" data-period="month_current">เดือนนี้</button>
            <button type="button" class="log-mobile-pill js-quick-period-mobile {{ ($timeMode ?? '') === 'year' ? 'is-active' : '' }}" data-period="year_current">ปีนี้</button>
            <button type="button" class="log-mobile-pill js-quick-period-mobile {{ ($timeMode ?? '') === 'range' ? 'is-active' : '' }}" data-period="range_custom">ช่วงเวลา</button>
            <span class="text-[11px] text-slate-600">กำลังดู: <span class="font-semibold text-slate-700">{{ $periodLabel ?? 'ทุกช่วงเวลา' }}</span></span>
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">ค้นหา</label>
            <input id="project-log-search-input-mobile" type="text" name="q" value="{{ $search ?? '' }}" list="project-log-suggestions-mobile" autocomplete="off" placeholder="ชื่อโครงการ/รหัส/ผู้ใช้"
                class="border rounded px-3 py-2 text-sm w-full">
            <datalist id="project-log-suggestions-mobile"></datalist>
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
            <div>
                <label class="block text-xs text-gray-500 mb-1">ช่วงเวลา</label>
                <select name="time_mode" class="border rounded px-3 py-2 text-sm w-full">
                    <option value="month" @selected(($timeMode ?? 'month') === 'month')>รายเดือน</option>
                    <option value="year" @selected(($timeMode ?? '') === 'year')>รายปี</option>
                    <option value="range" @selected(($timeMode ?? '') === 'range')>ช่วงวันที่</option>
                    <option value="all" @selected(($timeMode ?? '') === 'all')>ทั้งหมด</option>
                </select>
            </div>
            <div data-time-field="month">
                <label class="block text-xs text-gray-500 mb-1">เดือน</label>
                <select name="filter_month_th" class="border rounded px-3 py-2 text-sm w-full">
                    @foreach(($thaiMonths ?? []) as $monthNo => $monthName)
                        <option value="{{ $monthNo }}" @selected((int) ($month ?? now()->format('n')) === (int) $monthNo)>{{ $monthName }}</option>
                    @endforeach
                </select>
            </div>
            <div data-time-field="year">
                <label class="block text-xs text-gray-500 mb-1">ปี (พ.ศ.)</label>
                @php
                    $currentBe = (int) (now()->year + 543);
                    $selectedBe = (int) ($yearBeFromRequest ?? $currentBe);
                @endphp
                <select name="filter_year_be" class="border rounded px-3 py-2 text-sm w-full">
                    @for($year = $currentBe + 2; $year >= $currentBe - 15; $year--)
                        <option value="{{ $year }}" @selected($selectedBe === $year)>{{ $year }}</option>
                    @endfor
                </select>
            </div>
            <div data-time-field="start">
                <label class="block text-xs text-gray-500 mb-1">จากวันที่</label>
                <div class="relative">
                    <input id="projectLogStartDateMobile" type="text" name="start_date" value="{{ $startDate ?? '' }}" class="js-date-be border rounded px-3 py-2 text-sm w-full pr-10 bg-white cursor-pointer" readonly>
                    <button type="button" class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-500" data-open-picker data-target="projectLogStartDateMobile">📅</button>
                </div>
                <p class="text-[11px] text-slate-500 mt-1">แตะเพื่อเปิดวงล้อ วัน/เดือน/ปี (พ.ศ.)</p>
            </div>
            <div data-time-field="end">
                <label class="block text-xs text-gray-500 mb-1">ถึงวันที่</label>
                <div class="relative">
                    <input id="projectLogEndDateMobile" type="text" name="end_date" value="{{ $endDate ?? '' }}" class="js-date-be border rounded px-3 py-2 text-sm w-full pr-10 bg-white cursor-pointer" readonly>
                    <button type="button" class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-500" data-open-picker data-target="projectLogEndDateMobile">📅</button>
                </div>
                <p class="text-[11px] text-slate-500 mt-1">บันทึกลงฐานข้อมูลเป็นวันที่จริงอัตโนมัติ</p>
            </div>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="flex-1 px-3 py-2 rounded bg-blue-600 text-white text-sm hover:bg-blue-700">ค้นหา</button>
            <a href="{{ route('project_logs.index') }}" class="flex-1 px-3 py-2 rounded bg-gray-100 text-gray-700 text-sm text-center hover:bg-gray-200">ล้าง</a>
        </div>
        <div class="grid grid-cols-3 gap-2">
            <button type="button" class="px-2 py-2 rounded bg-emerald-600 text-white text-xs js-export-logs-mobile" data-format="csv">CSV</button>
            <button type="button" class="px-2 py-2 rounded bg-indigo-600 text-white text-xs js-export-logs-mobile" data-format="xls">Excel</button>
            <button type="button" class="px-2 py-2 rounded bg-rose-600 text-white text-xs js-export-logs-mobile" data-format="pdf">PDF</button>
        </div>
    </form>

    <div id="project-log-results-shell-mobile">
    @if(session('success'))
        <div class="rounded bg-green-50 text-green-700 px-4 py-2 text-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="log-mobile-summary bg-white p-4 space-y-3">
        <div class="flex items-start justify-between gap-2">
            <div>
                <h2 class="text-base font-semibold text-slate-800">Top 10 ผู้ที่อัปเดต</h2>
                <p class="text-xs text-slate-500">นับจำนวนโครงการ • {{ $periodLabel ?? 'ทุกช่วงเวลา' }}</p>
            </div>
            <div class="rounded-lg border border-blue-100 bg-blue-50 px-2.5 py-1.5 text-right">
                <div class="text-[10px] text-blue-700">รวม</div>
                <div class="text-sm font-bold text-blue-800">{{ number_format((int) ($totalProjectsInRange ?? 0)) }}</div>
            </div>
        </div>
        <div class="h-72">
            <canvas id="topUpdatersChartMobile"></canvas>
        </div>
        <div class="border rounded-lg overflow-hidden">
            <table class="w-full text-xs">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="text-left px-2 py-2 font-semibold text-slate-700">#</th>
                        <th class="text-left px-2 py-2 font-semibold text-slate-700">ผู้ใช้</th>
                        <th class="text-right px-2 py-2 font-semibold text-slate-700">โครงการ</th>
                        <th class="text-right px-2 py-2 font-semibold text-slate-700">log</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse(($topUpdaters ?? []) as $index => $item)
                        <tr class="border-t">
                            <td class="px-2 py-2 text-slate-500">{{ $index + 1 }}</td>
                            <td class="px-2 py-2 text-slate-800">{{ $item->user_label }}</td>
                            <td class="px-2 py-2 text-right font-semibold text-blue-700">{{ number_format((int) $item->total_projects) }}</td>
                            <td class="px-2 py-2 text-right font-semibold text-slate-500">{{ number_format((int) $item->total_logs) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-2 py-5 text-center text-slate-400">ไม่มีข้อมูล</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @forelse($groupedLogs ?? [] as $group)
        @php
            $first = $group->first();
            $groupKey = 'group_'.$first->id;
            $thaiDate = \Carbon\Carbon::parse($first->created_at)->setTimezone('Asia/Bangkok')->addYears(543)->format('d/m/Y H:i:s');
            $projectName = $first->project?->project_name_th ?? $first->project_name_th ?? '-';
            $userName = $first->user?->prefix . $first->user?->name;
            $verbLabel = (string) ($group->pluck('action_label')->filter()->first() ?? 'แก้ไข');
            $entityLabels = $group->pluck('entity_label')->filter()->unique()->values();
            $entityText = $entityLabels->implode(', ');
            $eventSummary = trim($verbLabel . ' ' . $entityText);
            $eventSearchText = trim($group->map(fn ($log) => $log->entity_label . ' ' . $log->action_label)->implode(' '));
            $searchText = trim(implode(' ', array_filter([$thaiDate, $projectName, $userName, $eventSummary, $eventSearchText])));
            $eventItems = $group->map(function ($log) {
                return [
                    'id' => $log->id,
                    'action' => $log->entity_label . ': ' . $log->action_label,
                ];
            })->values()->all();
        @endphp
        <div class="bg-white border rounded-xl shadow-sm p-4 space-y-2" data-log-search-text="{{ $searchText }}" data-log-row-key="{{ $groupKey }}">
            <div class="text-xs text-gray-500">{{ $thaiDate }}</div>
            <div class="font-semibold text-gray-800">
                <a href="{{ route('project_logs.project', $first->project_id) }}" class="text-blue-700 hover:underline">
                    {{ $projectName }}
                </a>
            </div>
            <div class="text-sm text-gray-600">โดย {{ $userName }}</div>
            <div class="text-sm text-slate-800 font-medium pt-1">
                {{ $eventSummary !== '' ? $eventSummary : 'ไม่ระบุเหตุการณ์' }}
            </div>
            <div class="flex items-center justify-between pt-2">
                <span class="text-blue-700 hover:underline text-sm cursor-pointer open-log-drawer"
                    data-time="{{ $thaiDate }}"
                    data-summary="{{ $eventSummary }}"
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
    <div id="project-log-mobile-empty-search" class="hidden text-center py-10 text-gray-400">ไม่พบรายการที่ตรงกับคำค้นหาในหน้านี้</div>

    <div id="project-log-mobile-pagination-wrap" class="mt-4">
        {{ $logs->links('vendor.pagination.th') }}
    </div>
    </div>
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

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var activeTopUpdaterChart = null;
    var rootSelector = '#project-log-ajax-root-mobile';
    var resultsSelector = '#project-log-results-shell-mobile';
    var suggestDebounceTimer = null;
    var filterDebounceTimer = null;
    var ajaxController = null;
    var ajaxRequestSeq = 0;

    function renderTopUpdaterChart(root) {
        var topUpdaterCanvas = root.querySelector('#topUpdatersChartMobile');
        if (!topUpdaterCanvas || !window.Chart) return;
        var topUpdaterLabels = JSON.parse(root.dataset.chartLabels || '[]');
        var topUpdaterCounts = JSON.parse(root.dataset.chartCounts || '[]');
        if (activeTopUpdaterChart) {
            activeTopUpdaterChart.destroy();
            activeTopUpdaterChart = null;
        }
        activeTopUpdaterChart = new Chart(topUpdaterCanvas, {
            type: 'bar',
            data: {
                labels: topUpdaterLabels,
                datasets: [{
                    data: topUpdaterCounts,
                    borderRadius: 8,
                    backgroundColor: 'rgba(59, 130, 246, 0.78)',
                    borderColor: 'rgba(37, 99, 235, 1)',
                    borderWidth: 1.2,
                }],
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: { precision: 0 },
                        grid: { color: 'rgba(148, 163, 184, 0.2)' },
                    },
                    y: {
                        grid: { display: false },
                    },
                },
            },
        });
    }

    function fetchSuggestions(root, keyword) {
        var suggestUrl = root.dataset.suggestUrl;
        var datalist = root.querySelector('#project-log-suggestions-mobile');
        if (!suggestUrl || !datalist) return;
        if (!keyword || keyword.trim().length < 2) {
            datalist.innerHTML = '';
            return;
        }

        var url = new URL(suggestUrl, window.location.origin);
        url.searchParams.set('q', keyword.trim());
        fetch(url.toString(), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function(res) { return res.json(); })
            .then(function(payload) {
                var rows = (payload && payload.data) ? payload.data : [];
                datalist.innerHTML = '';
                rows.forEach(function(item) {
                    var option = document.createElement('option');
                    option.value = item.label || '';
                    datalist.appendChild(option);
                });
            })
            .catch(function() {
                datalist.innerHTML = '';
            });
    }

    function buildFormUrl(form) {
        var params = new URLSearchParams(new FormData(form));
        var forceView = new URLSearchParams(window.location.search).get('view');
        if (forceView) {
            params.set('view', forceView);
        }
        return form.getAttribute('action') + '?' + params.toString();
    }

    function buildExportUrl(root, form, format) {
        var exportUrl = root.dataset.exportUrl || '';
        if (!exportUrl) return '';
        var params = new URLSearchParams(new FormData(form));
        params.set('format', format);
        return exportUrl + '?' + params.toString();
    }

    function applyQuickPeriod(form, period) {
        var timeMode = form.querySelector('[name=\"time_mode\"]');
        var monthSel = form.querySelector('[name=\"filter_month_th\"]');
        var yearInput = form.querySelector('[name=\"filter_year_be\"]');
        var startInput = form.querySelector('[name=\"start_date\"]');
        var endInput = form.querySelector('[name=\"end_date\"]');
        var now = new Date();
        var be = now.getFullYear() + 543;
        var month = now.getMonth() + 1;

        if (period === 'month_current') {
            if (timeMode) timeMode.value = 'month';
            if (monthSel) monthSel.value = String(month);
            if (yearInput) yearInput.value = String(be);
            if (startInput) startInput.value = '';
            if (endInput) endInput.value = '';
            return;
        }
        if (period === 'year_current') {
            if (timeMode) timeMode.value = 'year';
            if (yearInput) yearInput.value = String(be);
            if (startInput) startInput.value = '';
            if (endInput) endInput.value = '';
            return;
        }
        if (period === 'range_custom') {
            if (timeMode) timeMode.value = 'range';
        }
    }

    function syncPeriodPills(root, form) {
        if (!root || !form) return;
        var mode = (form.querySelector('[name=\"time_mode\"]') || {}).value || '';
        var targetPeriod = '';
        if (mode === 'month') targetPeriod = 'month_current';
        if (mode === 'year') targetPeriod = 'year_current';
        if (mode === 'range') targetPeriod = 'range_custom';
        root.querySelectorAll('.js-quick-period-mobile').forEach(function(btn) {
            btn.classList.toggle('is-active', targetPeriod !== '' && btn.dataset.period === targetPeriod);
        });
    }

    function updateTimeModeUi(root, form) {
        if (!root || !form) return;
        var mode = (form.querySelector('[name=\"time_mode\"]') || {}).value || 'month';
        var map = {
            month: ['month', 'year'],
            year: ['year'],
            range: ['start', 'end'],
            all: []
        };
        var active = map[mode] || map.month;
        root.querySelectorAll('[data-time-field]').forEach(function(block) {
            var key = block.getAttribute('data-time-field') || '';
            var show = active.indexOf(key) !== -1;
            block.classList.toggle('hidden', !show);
            block.querySelectorAll('input, select, button').forEach(function(control) {
                control.disabled = !show;
            });
        });
    }

    function initMobileWheelDateInputs(root) {
        if (!root || typeof window.initNativeMobileDateInput !== 'function') return;
        root.querySelectorAll('input.js-date-be').forEach(function(input) {
            window.initNativeMobileDateInput(input);
        });
    }

    function replaceResultsShellFromHtml(html) {
        var parser = new DOMParser();
        var doc = parser.parseFromString(html, 'text/html');
        var nextRoot = doc.querySelector(rootSelector);
        var currentRoot = document.querySelector(rootSelector);
        var nextShell = doc.querySelector(resultsSelector);
        var currentShell = document.querySelector(resultsSelector);
        if (!nextRoot || !currentRoot || !nextShell || !currentShell) return false;
        currentShell.replaceWith(nextShell);
        currentRoot.dataset.chartLabels = nextRoot.dataset.chartLabels || '[]';
        currentRoot.dataset.chartCounts = nextRoot.dataset.chartCounts || '[]';
        return true;
    }

    function ajaxRefresh(url) {
        var requestSeq = ++ajaxRequestSeq;
        if (ajaxController) {
            ajaxController.abort();
        }
        ajaxController = new AbortController();

        fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            signal: ajaxController.signal
        })
            .then(function(res) { return res.text(); })
            .then(function(html) {
                if (requestSeq !== ajaxRequestSeq) return;
                if (!replaceResultsShellFromHtml(html)) return;
                history.replaceState({ ajax: true }, '', url);
                bindAjaxControls();
                bindDrawerControls();
                bindConfirmForms();
                var refreshedRoot = document.querySelector(rootSelector);
                renderTopUpdaterChart(refreshedRoot);
                applyLocalSearchFilter(refreshedRoot || document);
            })
            .catch(function() {
                if (ajaxController && ajaxController.signal.aborted) return;
                window.location.href = url;
            });
    }

    function bindAjaxControls() {
        var root = document.querySelector(rootSelector);
        if (!root) return;
        var form = root.querySelector('#project-log-filter-form-mobile');
        if (!form) return;
        var searchInput = root.querySelector('#project-log-search-input-mobile');

        if (form.dataset.ajaxBound !== 'true') {
            form.dataset.ajaxBound = 'true';

            form.addEventListener('submit', function(e) {
                e.preventDefault();
                ajaxRefresh(buildFormUrl(form));
            });

            root.querySelectorAll('select, input[type="number"], input.js-date-be').forEach(function(el) {
                el.addEventListener('change', function() {
                    updateTimeModeUi(root, form);
                    syncPeriodPills(root, form);
                    ajaxRefresh(buildFormUrl(form));
                });
            });

            root.querySelectorAll('.js-quick-period-mobile').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    root.querySelectorAll('.js-quick-period-mobile').forEach(function(b) { b.classList.remove('is-active'); });
                    btn.classList.add('is-active');
                    applyQuickPeriod(form, btn.dataset.period || '');
                    updateTimeModeUi(root, form);
                    syncPeriodPills(root, form);
                    ajaxRefresh(buildFormUrl(form));
                });
            });

            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    applyLocalSearchFilter(root);
                });
                searchInput.addEventListener('keydown', function(event) {
                    if (event.key !== 'Enter') return;
                    event.preventDefault();
                    applyLocalSearchFilter(root);
                });
            }

            root.querySelectorAll('.js-export-logs-mobile').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var format = btn.dataset.format || 'csv';
                    var url = buildExportUrl(root, form, format);
                    if (!url) return;
                    window.open(url, '_blank');
                });
            });
        }

        root.querySelectorAll('.pagination a').forEach(function(link) {
            if (link.dataset.ajaxBound === 'true') return;
            link.dataset.ajaxBound = 'true';
            link.addEventListener('click', function(e) {
                e.preventDefault();
                ajaxRefresh(link.href);
            });
        });

        updateTimeModeUi(root, form);
        syncPeriodPills(root, form);
    }

    var drawer = document.getElementById('log-drawer');
    var drawerPanel = document.getElementById('log-drawer-panel');
    var drawerBody = document.getElementById('log-drawer-body');
    var drawerTime = document.getElementById('log-drawer-time');
    var drawerAction = document.getElementById('log-drawer-action');
    var drawerProject = document.getElementById('log-drawer-project');
    var drawerUser = document.getElementById('log-drawer-user');

    function normalizeLocalSearch(value) {
        return String(value || '').toLowerCase().replace(/\s+/g, ' ').trim();
    }

    function applyLocalSearchFilter(root) {
        root = root || document;
        var input = root.querySelector('#project-log-search-input-mobile');
        if (!input) return;
        var term = normalizeLocalSearch(input.value);
        var cards = root.querySelectorAll('div[data-log-search-text]');
        var visibleCount = 0;

        cards.forEach(function(card) {
            var text = normalizeLocalSearch(card.getAttribute('data-log-search-text') || '');
            var matched = term === '' || text.indexOf(term) !== -1;
            card.classList.toggle('hidden', !matched);
            if (matched) visibleCount += 1;
        });

        var localSearchEmptyEl = root.querySelector('#project-log-mobile-empty-search');
        if (localSearchEmptyEl) {
            localSearchEmptyEl.classList.toggle('hidden', !(term !== '' && visibleCount === 0));
        }
        var localSearchPaginationEl = root.querySelector('#project-log-mobile-pagination-wrap');
        if (localSearchPaginationEl) {
            localSearchPaginationEl.classList.toggle('hidden', term !== '');
        }
    }

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

    function bindDrawerControls() {
        document.querySelectorAll('.open-log-drawer').forEach(function(button) {
            button.onclick = function(e) {
                e.preventDefault();
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
                action: button.getAttribute('data-summary') || 'เหตุการณ์ทั้งหมด',
                project: button.getAttribute('data-project'),
                user: button.getAttribute('data-user'),
                html: bodyHtml
            });
            };
        });
    }

    function bindConfirmForms() {
        document.querySelectorAll('.delete-log-form').forEach(function(form) {
            form.onsubmit = function(e) {
                e.preventDefault();
                if (confirm('ยืนยันการลบ log นี้?')) {
                    form.submit();
                }
            };
        });
    }

    document.querySelectorAll('.log-drawer-close').forEach(function(button) {
        button.addEventListener('click', function() {
            closeDrawer();
        });
    });

    bindConfirmForms();
    document.querySelectorAll('.purge-old-form').forEach(function(form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            if(confirm('ยืนยันการลบ log ที่เก่ากว่า 3 เดือนทั้งหมด?')) {
                form.submit();
            }
        });
    });

    bindAjaxControls();
    bindDrawerControls();
    bindConfirmForms();
    var root = document.querySelector(rootSelector);
    if (root) {
        initMobileWheelDateInputs(root);
        renderTopUpdaterChart(root);
        applyLocalSearchFilter(root);
    }
});
</script>
@endsection
